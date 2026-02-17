<?php
/**
 * Lucky Puffin - Player Model (FINAL FIXED VERSION)
 * ✅ No double deduction
 * ✅ Waiting → Betting transition
 * ✅ Stuck round cleanup
 */

require_once 'Database.php';

class Player {
    public $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getOrCreatePlayer($guestId, $playerName = null) {
        $player = $this->db->fetch(
            "SELECT * FROM players WHERE guest_id = ?",
            [$guestId]
        );
        
        if ($player) {
            $bonus = $this->checkHourlyBonus($player['id'], $player['last_visit']);
            
            if ($bonus > 0) {
                $this->addBonus($player['id'], $bonus);
                $player['balance'] += $bonus;
                $player['bonus_earned'] = $bonus;
            }
            
            $this->db->query(
                "UPDATE players SET last_visit = NOW() WHERE id = ?",
                [$player['id']]
            );
            
            return $player;
        }
        
        if (!$playerName) {
            $playerName = 'Guest' . rand(1000, 9999);
        }
        
        $this->db->query(
            "INSERT INTO players (guest_id, player_name, balance, last_visit) VALUES (?, ?, 1000, NOW())",
            [$guestId, $playerName]
        );
        
        $playerId = $this->db->lastInsertId();
        
        return [
            'id' => $playerId,
            'guest_id' => $guestId,
            'player_name' => $playerName,
            'balance' => 1000,
            'last_visit' => date('Y-m-d H:i:s'),
            'is_new' => true
        ];
    }
    
    private function checkHourlyBonus($playerId, $lastVisit) {
        if (!$lastVisit) return 0;
        
        $lastVisitTime = strtotime($lastVisit);
        $now = time();
        $hoursPassed = floor(($now - $lastVisitTime) / 3600);
        
        if ($hoursPassed >= 1) {
            $hoursToReward = min($hoursPassed, 5);
            return $hoursToReward * 10;
        }
        
        return 0;
    }
    
    private function addBonus($playerId, $bonusAmount) {
        $this->db->query(
            "UPDATE players SET balance = balance + ? WHERE id = ?",
            [$bonusAmount, $playerId]
        );
        
        $this->db->query(
            "INSERT INTO bonus_claims (player_id, bonus_amount) VALUES (?, ?)",
            [$playerId, $bonusAmount]
        );
    }
    
    public function updateBalance($playerId, $newBalance) {
        $this->db->query(
            "UPDATE players SET balance = ? WHERE id = ?",
            [$newBalance, $playerId]
        );
        
        return true;
    }
    
    public function getPlayerById($playerId) {
        return $this->db->fetch(
            "SELECT * FROM players WHERE id = ?",
            [$playerId]
        );
    }
    
    public function getLeaderboard($limit = 10) {
        return $this->db->fetchAll(
            "SELECT id, player_name, balance, total_games_played, total_winnings 
             FROM players 
             ORDER BY balance DESC 
             LIMIT ?",
            [$limit]
        );
    }
    
    public function resetBalance($playerId) {
        $this->db->query(
            "UPDATE players SET balance = 1000 WHERE id = ?",
            [$playerId]
        );
        
        return true;
    }
    
    // ============================================
    // SHARED ROUND SYSTEM
    // ============================================
    
    public function getCurrentRound() {
        $round = $this->db->fetch(
            "SELECT * FROM game_rounds 
             ORDER BY id DESC 
             LIMIT 1"
        );
        
        $now = time();
        
        // Create first round if none exists
        if (!$round) {
            error_log("No round exists - creating waiting round");
            $this->db->query(
                "INSERT INTO game_rounds (round_status, timer_remaining, started_at) 
                 VALUES ('waiting', 0, NULL)"
            );
            $roundId = $this->db->lastInsertId();
            $round = $this->db->fetch("SELECT * FROM game_rounds WHERE id = ?", [$roundId]);
        }
        
        // CLEANUP: Fix stuck 'dealing' rounds with no cards
        if ($round['round_status'] === 'dealing') {
            $hasCards = !empty($round['player_cards']) && 
                       $round['player_cards'] !== 'null' && 
                       $round['player_cards'] !== '[]' &&
                       $round['player_cards'] !== 'NULL';
            
            if (!$hasCards) {
                error_log("STUCK ROUND DETECTED: Round {$round['id']} in 'dealing' with no cards!");
                
                $dealSuccess = $this->dealCards($round['id']);
                
                if (!$dealSuccess) {
                    error_log("Deal failed - resetting to waiting");
                    $this->db->query(
                        "UPDATE game_rounds 
                         SET round_status = 'waiting', 
                             timer_remaining = 0, 
                             started_at = NULL,
                             dealing_ends_at = NULL
                         WHERE id = ?",
                        [$round['id']]
                    );
                }
                
                $round = $this->db->fetch("SELECT * FROM game_rounds WHERE id = ?", [$round['id']]);
            }
        }
        
        // Get bet count
        $betCount = 0;
        if ($round['id']) {
            $result = $this->db->fetch(
                "SELECT COUNT(*) as count FROM round_bets WHERE round_id = ?",
                [$round['id']]
            );
            $betCount = $result ? $result['count'] : 0;
        }
        
        // Handle round states
        if ($round['round_status'] === 'waiting') {
            $round['timer_remaining'] = 0;
            
            // NOTE: Don't auto-start here - placeBet() handles the transition
            
        } elseif ($round['round_status'] === 'betting') {
            if (!empty($round['started_at'])) {
                $startedAt = strtotime($round['started_at']);
                $elapsed = $now - $startedAt;
                $timeRemaining = max(0, 20 - $elapsed);
                $round['timer_remaining'] = $timeRemaining;
                
                // Auto-deal when timer expires
                if ($timeRemaining <= 0) {
                    if ($betCount > 0) {
                        error_log("Timer expired - dealing cards");
                        $dealSuccess = $this->dealCards($round['id']);
                        
                        if (!$dealSuccess) {
                            error_log("Deal failed - returning to waiting");
                            $this->db->query(
                                "UPDATE game_rounds 
                                 SET round_status = 'waiting', 
                                     started_at = NULL 
                                 WHERE id = ?",
                                [$round['id']]
                            );
                        }
                    } else {
                        error_log("Timer expired but no bets - returning to waiting");
                        $this->db->query(
                            "UPDATE game_rounds 
                             SET round_status = 'waiting', 
                                 started_at = NULL 
                             WHERE id = ?",
                            [$round['id']]
                        );
                    }
                    $round = $this->db->fetch("SELECT * FROM game_rounds WHERE id = ?", [$round['id']]);
                }
            }
            
        } elseif ($round['round_status'] === 'dealing') {
            if (empty($round['dealing_ends_at'])) {
                $this->db->query(
                    "UPDATE game_rounds SET dealing_ends_at = DATE_ADD(NOW(), INTERVAL 5 SECOND) WHERE id = ?",
                    [$round['id']]
                );
                $round = $this->db->fetch("SELECT * FROM game_rounds WHERE id = ?", [$round['id']]);
            }
            
            if (!empty($round['dealing_ends_at'])) {
                $dealingEndsAt = strtotime($round['dealing_ends_at']);
                $timeRemaining = max(0, $dealingEndsAt - $now);
                $round['timer_remaining'] = $timeRemaining;
                
                if ($now >= $dealingEndsAt) {
                    error_log("Dealing complete - moving to finished");
                    $this->db->query(
                        "UPDATE game_rounds SET round_status = 'finished', finished_at = NOW() WHERE id = ?",
                        [$round['id']]
                    );
                    $round = $this->db->fetch("SELECT * FROM game_rounds WHERE id = ?", [$round['id']]);
                }
            }
            
        } elseif ($round['round_status'] === 'finished') {
            if (!empty($round['finished_at'])) {
                $finishedAt = strtotime($round['finished_at']);
                $timeSinceFinished = $now - $finishedAt;
                $timeRemaining = max(0, 5 - $timeSinceFinished);
                $round['timer_remaining'] = $timeRemaining;
                
                if ($timeSinceFinished >= 5) {
                    error_log("Creating new waiting round");
                    $this->db->query(
                        "INSERT INTO game_rounds (round_status, timer_remaining, started_at) 
                         VALUES ('waiting', 0, NULL)"
                    );
                    $roundId = $this->db->lastInsertId();
                    $round = $this->db->fetch("SELECT * FROM game_rounds WHERE id = ?", [$roundId]);
                }
            }
        }
        
        $round['bet_count'] = $betCount;
        return $round;
    }
    
    // ✅ FIXED: Immediate transition + NO double deduction
    public function placeBet($roundId, $playerId, $playerName, $bets) {
        $totalBet = array_sum($bets);
        
        // Get round status
        $round = $this->db->fetch("SELECT * FROM game_rounds WHERE id = ?", [$roundId]);
        
        if (!$round) {
            throw new Exception('Round not found');
        }
        
        // CRITICAL: Immediately transition 'waiting' → 'betting'
        if ($round['round_status'] === 'waiting') {
            error_log("FIRST BET - Transitioning round $roundId to 'betting'");
            $this->db->query(
                "UPDATE game_rounds 
                 SET round_status = 'betting', 
                     timer_remaining = 20, 
                     started_at = NOW() 
                 WHERE id = ?",
                [$roundId]
            );
        }
        
        // Check if bet exists
        $existing = $this->db->fetch(
            "SELECT id, total_bet FROM round_bets WHERE round_id = ? AND player_id = ?",
            [$roundId, $playerId]
        );
        
        if ($existing) {
            // Update existing bet
            $this->db->query(
                "UPDATE round_bets 
                 SET bet_player = ?, bet_banker = ?, bet_tie = ?, 
                     bet_player_pair = ?, bet_banker_pair = ?, total_bet = ?
                 WHERE id = ?",
                [
                    $bets['player'], $bets['banker'], $bets['tie'],
                    $bets['playerPair'], $bets['bankerPair'], $totalBet,
                    $existing['id']
                ]
            );
            
            // FIX: Log activity on UPDATE too (show cumulative bet)
            $this->logActivity($roundId, $playerId, $playerName, 'placed_bet', $totalBet, 
                "$playerName bet $totalBet WPUFF");
        } else {
            // Insert new bet
            $this->db->query(
                "INSERT INTO round_bets 
                 (round_id, player_id, player_name, bet_player, bet_banker, bet_tie, 
                  bet_player_pair, bet_banker_pair, total_bet) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $roundId, $playerId, $playerName,
                    $bets['player'], $bets['banker'], $bets['tie'],
                    $bets['playerPair'], $bets['bankerPair'], $totalBet
                ]
            );
            
            // Log first bet
            $this->logActivity($roundId, $playerId, $playerName, 'placed_bet', $totalBet, 
                "$playerName bet $totalBet WPUFF");
        }
        
        return true;
    }
    
    public function dealCards($roundId) {
        error_log("=== dealCards for round $roundId ===");
        
        $round = $this->db->fetch("SELECT * FROM game_rounds WHERE id = ?", [$roundId]);
        
        if (!$round) {
            error_log("ERROR: Round not found");
            return false;
        }
        
        if (!empty($round['player_cards']) && 
            $round['player_cards'] !== '[]' && 
            $round['player_cards'] !== 'null') {
            error_log("Cards already exist");
            return false;
        }
        
        $deck = $this->createDeck();
        shuffle($deck);
        
        $playerCards = [];
        $bankerCards = [];
        
        for ($i = 0; $i < 2; $i++) {
            $playerCards[] = array_pop($deck);
            $bankerCards[] = array_pop($deck);
        }
        
        $playerTotal = $this->calculateTotal($playerCards);
        $bankerTotal = $this->calculateTotal($bankerCards);
        
        if ($playerTotal <= 5) {
            $playerCards[] = array_pop($deck);
            $playerTotal = $this->calculateTotal($playerCards);
        }
        
        if ($bankerTotal <= 5) {
            $bankerCards[] = array_pop($deck);
            $bankerTotal = $this->calculateTotal($bankerCards);
        }
        
        if ($playerTotal > $bankerTotal) {
            $result = 'PLAYER_WINS';
        } elseif ($bankerTotal > $playerTotal) {
            $result = 'BANKER_WINS';
        } else {
            $result = 'TIE';
        }
        
        $isPlayerPair = (count($playerCards) >= 2 && $playerCards[0]['value'] === $playerCards[1]['value']);
        $isBankerPair = (count($bankerCards) >= 2 && $bankerCards[0]['value'] === $bankerCards[1]['value']);
        
        $updateResult = $this->db->query(
            "UPDATE game_rounds 
             SET round_status = 'dealing',
                 player_cards = ?,
                 banker_cards = ?,
                 player_total = ?,
                 banker_total = ?,
                 result = ?,
                 is_player_pair = ?,
                 is_banker_pair = ?,
                 dealing_ends_at = DATE_ADD(NOW(), INTERVAL 5 SECOND)
             WHERE id = ?",
            [
                json_encode($playerCards),
                json_encode($bankerCards),
                $playerTotal,
                $bankerTotal,
                $result,
                $isPlayerPair ? 1 : 0,
                $isBankerPair ? 1 : 0,
                $roundId
            ]
        );
        
        if ($updateResult === false) {
            error_log("ERROR: Failed to save cards");
            return false;
        }
        
        error_log("=== Cards dealt successfully ===");
        
        $this->calculatePayouts($roundId);
        
        return true;
    }
    
    // ✅ FIXED: Proper net change calculation
    private function calculatePayouts($roundId) {
        $round = $this->db->fetch("SELECT * FROM game_rounds WHERE id = ?", [$roundId]);
        $bets = $this->db->fetchAll("SELECT * FROM round_bets WHERE round_id = ?", [$roundId]);
        
        foreach ($bets as $bet) {
            $winnings = 0;
            
            if ($round['is_player_pair'] && $bet['bet_player_pair'] > 0) {
                $winnings += $bet['bet_player_pair'] * 11;
            }
            if ($round['is_banker_pair'] && $bet['bet_banker_pair'] > 0) {
                $winnings += $bet['bet_banker_pair'] * 11;
            }
            
            if ($round['result'] === 'PLAYER_WINS') {
                $winnings += $bet['bet_player'] * 2;
            } elseif ($round['result'] === 'BANKER_WINS') {
                $winnings += $bet['bet_banker'] * 2;
            } elseif ($round['result'] === 'TIE') {
                $winnings += $bet['bet_tie'] * 8;
                $winnings += $bet['bet_player'] + $bet['bet_banker'];
            }
            
            $this->db->query(
                "UPDATE round_bets SET total_won = ? WHERE id = ?",
                [$winnings, $bet['id']]
            );
            
            // Net change (frontend already deducted)
            $netChange = $winnings - $bet['total_bet'];
            
            $this->db->query(
                "UPDATE players SET balance = balance + ? WHERE id = ?",
                [$netChange, $bet['player_id']]
            );
            
            $this->db->query(
                "UPDATE players 
                 SET total_games_played = total_games_played + 1,
                     total_winnings = total_winnings + ? 
                 WHERE id = ?",
                [$netChange, $bet['player_id']]
            );
            
            if ($winnings > $bet['total_bet']) {
                $profit = $winnings - $bet['total_bet'];
                $activityType = ($profit > $bet['total_bet'] * 2) ? 'big_win' : 'won';
                $this->logActivity($roundId, $bet['player_id'], $bet['player_name'], 
                    $activityType, $profit, "{$bet['player_name']} won $profit WPUFF!");
            } elseif ($winnings == $bet['total_bet']) {
                $this->logActivity($roundId, $bet['player_id'], $bet['player_name'], 
                    'returned', 0, "{$bet['player_name']} tied");
            } else {
                $lost = $bet['total_bet'] - $winnings;
                $this->logActivity($roundId, $bet['player_id'], $bet['player_name'], 
                    'lost', $lost, "{$bet['player_name']} lost $lost WPUFF");
            }
        }
    }
    
    public function getGameState($playerId = null) {
        $currentRound = $this->getCurrentRound();
        $leaderboard = $this->getLeaderboard(5);
        $recentActivity = $this->getRecentActivity(5);
        
        $playerBet = null;
        if ($playerId && $currentRound && isset($currentRound['id'])) {
            $playerBet = $this->db->fetch(
                "SELECT total_bet, total_won FROM round_bets WHERE round_id = ? AND player_id = ?",
                [$currentRound['id'], $playerId]
            );
        }
        
        $activePlayers = [];
        if ($currentRound && isset($currentRound['id'])) {
            $activePlayers = $this->db->fetchAll(
                "SELECT DISTINCT player_id, player_name FROM round_bets WHERE round_id = ?",
                [$currentRound['id']]
            );
        }
        
        if ($currentRound && isset($currentRound['player_cards']) && $currentRound['player_cards']) {
            $currentRound['player_cards'] = json_decode($currentRound['player_cards'], true);
            $currentRound['banker_cards'] = json_decode($currentRound['banker_cards'], true);
        } else {
            $currentRound['player_cards'] = [];
            $currentRound['banker_cards'] = [];
        }
        
        if (!isset($currentRound['timer_remaining'])) {
            $currentRound['timer_remaining'] = 20;
        }
        
        return [
            'current_round' => $currentRound,
            'player_bet' => $playerBet,
            'active_players' => $activePlayers,
            'leaderboard' => $leaderboard,
            'recent_activity' => $recentActivity,
            'timestamp' => time()
        ];
    }
    
    private function createDeck() {
        $deck = [];
        $suits = ['♥', '♦', '♣', '♠'];
        $values = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
        
        foreach ($suits as $suit) {
            foreach ($values as $value) {
                $deck[] = [
                    'suit' => $suit,
                    'value' => $value,
                    'display' => $value . $suit
                ];
            }
        }
        
        return $deck;
    }
    
    private function calculateTotal($cards) {
        $total = 0;
        foreach ($cards as $card) {
            $value = $card['value'];
            if ($value === 'A') {
                $total += 1;
            } elseif (in_array($value, ['J', 'Q', 'K', '10'])) {
                $total += 0;
            } else {
                $total += intval($value);
            }
        }
        return $total % 10;
    }
    
    private function logActivity($roundId, $playerId, $playerName, $activityType, $amount, $message) {
        $this->db->query(
            "INSERT INTO activity_feed (round_id, player_id, player_name, activity_type, amount, message) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [$roundId, $playerId, $playerName, $activityType, $amount, $message]
        );
    }
    
    public function getRecentActivity($limit = 10) {
        return $this->db->fetchAll(
            "SELECT * FROM activity_feed 
             ORDER BY created_at DESC 
             LIMIT ?",
            [$limit]
        );
    }
}