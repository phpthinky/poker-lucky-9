<?php
/**
 * Lucky Puffin - API (Shared Round System)
 * All players bet on the same cards!
 */
if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'democodes.online') === false) {
    http_response_code(403);
    exit('Forbidden');
}

require_once 'config.php';
require_once 'Player.php';

$requestMethod = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    $player = new Player();
    
    $action = $_GET['action'] ?? ($input['action'] ?? '');
    
    switch ($action) {
        
        case 'getPlayer':
            $guestId = $input['guestId'] ?? '';
            $playerName = $input['playerName'] ?? null;
            
            if (empty($guestId)) {
                throw new Exception('Guest ID is required');
            }
            
            $playerData = $player->getOrCreatePlayer($guestId, $playerName);
            
            $response['success'] = true;
            $response['data'] = $playerData;
            
            if (isset($playerData['is_new']) && $playerData['is_new']) {
                $response['message'] = "Welcome {$playerData['player_name']}! You start with 1000 WPUFF.";
            } elseif (isset($playerData['bonus_earned']) && $playerData['bonus_earned'] > 0) {
                $hours = floor($playerData['bonus_earned'] / 10);
                $response['message'] = "Welcome back! You earned {$playerData['bonus_earned']} bonus WPUFF for being away {$hours} hour(s)!";
            } else {
                $response['message'] = "Welcome back {$playerData['player_name']}!";
            }
            break;
            
        case 'getCurrentRound':
            // Get active round
            $currentRound = $player->getCurrentRound();
            
            $response['success'] = true;
            $response['data'] = $currentRound;
            break;
            
case 'placeBet':
    // Place bet on current round
    $playerId = $input['playerId'] ?? 0;
    $playerName = $input['playerName'] ?? 'Guest';
    $bets = $input['bets'] ?? [];
    $roundId = $input['roundId'] ?? 0;
    
    if (!$playerId) {
        throw new Exception('Invalid player ID');
    }
    
    // Get current round
    if (!$roundId) {
        $currentRound = $player->getCurrentRound();
        $roundId = $currentRound['id'];
    }
    
    // Check if round exists
    $round = $player->db->fetch("SELECT * FROM game_rounds WHERE id = ?", [$roundId]);
    if (!$round) {
        throw new Exception('Round not found');
    }
    
    // Allow betting on waiting rounds - they will become betting
    if ($round['round_status'] === 'waiting') {
        // Start the round immediately
        $player->db->query(
            "UPDATE game_rounds 
             SET round_status = 'betting', 
                 timer_remaining = 20, 
                 started_at = NOW() 
             WHERE id = ?",
            [$roundId]
        );
        error_log("Auto-started round $roundId from waiting to betting");
    } else if ($round['round_status'] !== 'betting') {
        throw new Exception('Betting is closed for this round');
    }
    
    // Place the bet
    $player->placeBet($roundId, $playerId, $playerName, $bets);
    
    $response['success'] = true;
    $response['message'] = 'Bet placed successfully';
    break;
    
            
        case 'updateTimer':
            // Update timer (can be called by any player)
            $roundId = $input['roundId'] ?? 0;
            $timeRemaining = $input['timeRemaining'] ?? 20;
            
            if (!$roundId) {
                // Get current round
                $currentRound = $player->getCurrentRound();
                $roundId = $currentRound['id'];
            }
            
            $player->updateRoundTimer($roundId, $timeRemaining);
            
            $response['success'] = true;
            break;
            
        case 'dealCards':
            // Manually trigger card dealing (auto-triggered by timer)
            $roundId = $input['roundId'] ?? 0;
            
            if (!$roundId) {
                $currentRound = $player->getCurrentRound();
                $roundId = $currentRound['id'];
            }
            
            $player->dealCards($roundId);
            
            $response['success'] = true;
            $response['message'] = 'Cards dealt';
            break;
            
        case 'getGameState':
            // Get complete game state (for polling)
            $playerId = $input['playerId'] ?? null;
            
            $gameState = $player->getGameState($playerId);
            
            $response['success'] = true;
            $response['data'] = $gameState;
            break;
            
        case 'getLeaderboard':
            // Get leaderboard
            $limit = $input['limit'] ?? 10;
            $leaderboard = $player->getLeaderboard($limit);
            
            $response['success'] = true;
            $response['data'] = $leaderboard;
            break;
            
        case 'updateBalance':
            // Update player balance
            $playerId = $input['playerId'] ?? 0;
            $newBalance = $input['balance'] ?? 0;
            
            if (!$playerId || $newBalance < 0) {
                throw new Exception('Invalid player ID or balance');
            }
            
            $player->updateBalance($playerId, $newBalance);
            
            $response['success'] = true;
            $response['message'] = 'Balance updated successfully';
            break;
            
        case 'resetBalance':
            // Reset balance
            $playerId = $input['playerId'] ?? 0;
            
            if (!$playerId) {
                throw new Exception('Invalid player ID');
            }
            
            $player->resetBalance($playerId);
            
            $response['success'] = true;
            $response['message'] = 'Balance reset to 1000 WPUFF';
            $response['data'] = ['balance' => 1000];
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    http_response_code(400);
}

echo json_encode($response);