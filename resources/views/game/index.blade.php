<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{--
        Pusher credentials — read by app.js at runtime from .env.
        No rebuild needed when you change Pusher keys.
    --}}
    <meta name="pusher-key"     content="{{ config('broadcasting.connections.pusher.key') }}">
    <meta name="pusher-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster', 'ap1') }}">

    <title>🐧 Lucky Puffin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --bg-deep:#06101a;--bg-panel:#0c1c2a;
            --gold:#f0c040;--gold-bright:#ffd700;--gold-dim:#9a7c28;
            --gold-border:rgba(240,192,64,.22);--dim-border:rgba(255,255,255,.07);
            --blue:#3d9fff;--red:#ff4d5e;--green:#38c97a;
            --text:#e8dfc8;--text-dim:rgba(232,223,200,.45);--text-muted:rgba(232,223,200,.22);
            --shadow:0 8px 32px rgba(0,0,0,.7);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; }
        html, body { height:100%; background-color:var(--bg-deep); background-image:radial-gradient(ellipse 90% 50% at 50% -10%,rgba(10,50,32,.7) 0%,transparent 60%),url("data:image/svg+xml,%3Csvg width='80' height='80' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 40h80M40 0v80' stroke='%23ffffff' stroke-width='0.4' stroke-opacity='0.025'/%3E%3C/svg%3E"); color:var(--text); font-family:'DM Sans',sans-serif; font-size:14px; min-height:100vh; }
        .game-layout { display:grid; grid-template-columns:220px 1fr 220px; grid-template-rows:auto 1fr; gap:12px; max-width:1200px; margin:0 auto; padding:14px; min-height:100vh; }
        .game-header { grid-column:1/-1; display:flex; align-items:center; justify-content:space-between; padding:10px 20px; background:linear-gradient(135deg,#0a1824 0%,#0d2236 100%); border:1px solid var(--gold-border); border-radius:12px; box-shadow:var(--shadow),inset 0 1px 0 rgba(255,215,0,.08); }
        .game-logo { font-family:'Cinzel',serif; font-size:1.25rem; font-weight:700; color:var(--gold); letter-spacing:.1em; text-shadow:0 0 24px rgba(240,192,64,.4); }
        .header-center { display:flex; align-items:center; gap:16px; }
        .player-count { font-size:.72rem; color:var(--text-dim); }
        .player-count strong { color:var(--green); }
        .balance-pill { background:linear-gradient(135deg,#122a1e 0%,#0c1e16 100%); border:1px solid rgba(56,201,122,.35); border-radius:20px; padding:5px 16px; font-family:'Cinzel',serif; font-size:.9rem; font-weight:600; color:var(--gold-bright); box-shadow:0 0 12px rgba(56,201,122,.1); }
        .side-panel { display:flex; flex-direction:column; gap:12px; }
        .panel { background:var(--bg-panel); border:1px solid var(--dim-border); border-radius:10px; overflow:hidden; box-shadow:var(--shadow); }
        .panel-header { padding:7px 12px; background:rgba(255,215,0,.04); border-bottom:1px solid var(--dim-border); font-family:'Cinzel',serif; font-size:.62rem; font-weight:600; letter-spacing:.14em; text-transform:uppercase; color:var(--gold-dim); }
        .panel-body { padding:10px 12px; }
        .lb-row { display:flex; align-items:center; gap:7px; padding:6px 2px; border-bottom:1px solid rgba(255,255,255,.04); font-size:.75rem; }
        .lb-row:last-child { border-bottom:none; }
        .lb-rank { font-family:'Cinzel',serif; color:var(--gold-dim); width:16px; font-size:.67rem; flex-shrink:0; }
        .lb-name { flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:var(--text); }
        .lb-name.me { color:var(--gold); font-weight:600; }
        .lb-bal { font-family:'Cinzel',serif; font-size:.7rem; color:var(--green); white-space:nowrap; }
        .activity-item { padding:5px 2px; border-bottom:1px solid rgba(255,255,255,.04); font-size:.72rem; color:var(--text-dim); line-height:1.4; animation:slideIn .3s ease; }
        .activity-item:last-child { border-bottom:none; }
        .activity-item.win { color:var(--green); }
        .activity-item.big { color:var(--gold); }
        .activity-item.lose { color:rgba(255,77,94,.75); }
        @keyframes slideIn { from{opacity:0;transform:translateX(-6px);}to{opacity:1;transform:translateX(0);} }
        .table-col { display:flex; flex-direction:column; gap:10px; }
        .felt-table { background:radial-gradient(ellipse at 50% 30%,#0d4535 0%,#082e20 55%,#051e15 100%); border:2px solid rgba(255,215,0,.15); border-radius:14px; padding:20px 16px 16px; position:relative; box-shadow:inset 0 2px 60px rgba(0,0,0,.5),0 12px 40px rgba(0,0,0,.6); flex:1; }
        .felt-table::after { content:''; position:absolute; inset:7px; border-radius:8px; border:1px solid rgba(255,215,0,.08); pointer-events:none; }
        .timer-wrap { text-align:center; margin-bottom:14px; position:relative; z-index:1; }
        #bet-timer { display:inline-block; font-family:'Cinzel',serif; font-size:.75rem; letter-spacing:.12em; text-transform:uppercase; padding:6px 22px; border-radius:20px; border:1px solid rgba(255,255,255,.08); background:rgba(0,0,0,.35); color:var(--text-dim); transition:all .4s; }
        #bet-timer.t-waiting { border-color:rgba(255,255,255,.1); color:var(--text-dim); }
        #bet-timer.t-betting { border-color:rgba(56,201,122,.45); color:var(--green); background:rgba(56,201,122,.08); }
        #bet-timer.t-warning { border-color:rgba(240,192,64,.5); color:var(--gold); background:rgba(240,192,64,.1); animation:pulseGlow .7s ease-in-out infinite alternate; }
        #bet-timer.t-urgent  { border-color:rgba(255,77,94,.6);  color:var(--red);  background:rgba(255,77,94,.1);  animation:pulseGlow .35s ease-in-out infinite alternate; }
        @keyframes pulseGlow { from{box-shadow:none;}to{box-shadow:0 0 18px rgba(255,200,0,.35);} }
        .bet-main { display:grid; grid-template-columns:1fr 72px 1fr; gap:10px; margin-bottom:10px; position:relative; z-index:1; }
        .bet-sides { display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; margin-bottom:16px; position:relative; z-index:1; }
        .betting-box { border-radius:10px; border:1px solid rgba(255,255,255,.1); background:rgba(0,0,0,.28); padding:14px 8px; text-align:center; cursor:pointer; user-select:none; transition:transform .18s,box-shadow .18s,border-color .18s; position:relative; overflow:hidden; }
        .betting-box:hover:not(.disabled) { transform:translateY(-3px); }
        .betting-box:active:not(.disabled) { transform:translateY(-1px) scale(.98); }
        .betting-box.disabled { opacity:.3; cursor:not-allowed; filter:saturate(.3); }
        .betting-box[data-bet-type="player"] { border-color:rgba(61,159,255,.28); background:linear-gradient(160deg,rgba(61,159,255,.1) 0%,rgba(0,0,0,.28) 100%); }
        .betting-box[data-bet-type="player"]:hover:not(.disabled) { border-color:rgba(61,159,255,.65); box-shadow:0 4px 24px rgba(61,159,255,.2); }
        .betting-box[data-bet-type="banker"] { border-color:rgba(255,77,94,.28); background:linear-gradient(160deg,rgba(255,77,94,.1) 0%,rgba(0,0,0,.28) 100%); }
        .betting-box[data-bet-type="banker"]:hover:not(.disabled) { border-color:rgba(255,77,94,.65); box-shadow:0 4px 24px rgba(255,77,94,.2); }
        .betting-box[data-bet-type="tie"] { border-color:rgba(56,201,122,.28); background:linear-gradient(160deg,rgba(56,201,122,.1) 0%,rgba(0,0,0,.28) 100%); }
        .betting-box[data-bet-type="playerPair"],.betting-box[data-bet-type="bankerPair"],.betting-box[data-bet-type="randomPair"] { padding:10px 6px; border-color:rgba(255,215,0,.12); background:rgba(0,0,0,.38); }
        .betting-box[data-bet-type="playerPair"]:hover:not(.disabled),.betting-box[data-bet-type="bankerPair"]:hover:not(.disabled),.betting-box[data-bet-type="randomPair"]:hover:not(.disabled) { border-color:rgba(240,192,64,.45); box-shadow:0 3px 16px rgba(240,192,64,.12); }
        .box-label { font-family:'Cinzel',serif; font-size:.6rem; letter-spacing:.14em; text-transform:uppercase; color:var(--text-dim); margin-bottom:3px; }
        .box-odds { font-size:.72rem; color:var(--text-muted); margin-bottom:6px; }
        .box-bet { font-family:'Cinzel',serif; font-size:1.15rem; font-weight:700; color:var(--gold); min-height:1.4rem; line-height:1; }
        .box-bet-sm { font-family:'Cinzel',serif; font-size:.9rem; font-weight:600; color:var(--gold); min-height:1.2rem; }
        .gold-line { height:1px; background:linear-gradient(90deg,transparent,rgba(255,215,0,.25),transparent); margin:0 -16px 14px; position:relative; z-index:1; }
        .chip-rack { display:flex; justify-content:center; gap:10px; flex-wrap:wrap; margin-bottom:14px; position:relative; z-index:1; }
        .chip { width:52px; height:52px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-family:'Cinzel',serif; font-size:.62rem; font-weight:700; cursor:pointer; user-select:none; transition:transform .15s,box-shadow .15s; position:relative; color:rgba(255,255,255,.92); border:3px solid rgba(255,255,255,.22); box-shadow:inset 0 2px 5px rgba(255,255,255,.22),inset 0 -3px 5px rgba(0,0,0,.45),0 5px 12px rgba(0,0,0,.55); letter-spacing:.04em; }
        .chip::after { content:''; position:absolute; inset:5px; border-radius:50%; border:2px dashed rgba(255,255,255,.28); pointer-events:none; }
        .chip[data-value="10"]  { background:radial-gradient(circle at 38% 32%,#5a9af0,#2563c0); }
        .chip[data-value="50"]  { background:radial-gradient(circle at 38% 32%,#4dd88c,#1b8850); }
        .chip[data-value="100"] { background:radial-gradient(circle at 38% 32%,#f06060,#b01e2e); }
        .chip[data-value="500"] { background:radial-gradient(circle at 38% 32%,#c07ae8,#6a1faa); }
        .chip[data-value="1000"]{ background:radial-gradient(circle at 38% 32%,#f8b060,#c55e10); }
        .chip:hover { transform:translateY(-3px) scale(1.06); }
        .chip.active { transform:translateY(-5px) scale(1.12); border-color:var(--gold-bright); box-shadow:inset 0 2px 5px rgba(255,255,255,.3),0 0 0 2px var(--gold-bright),0 8px 24px rgba(255,215,0,.45); }
        .action-row { display:flex; justify-content:center; position:relative; z-index:1; }
        .btn-clear { background:transparent; border:1px solid rgba(255,77,94,.4); color:rgba(255,77,94,.8); border-radius:20px; padding:6px 22px; font-size:.72rem; font-family:'DM Sans',sans-serif; letter-spacing:.05em; cursor:pointer; transition:all .2s; }
        .btn-clear:hover { background:rgba(255,77,94,.1); border-color:var(--red); color:var(--red); }
        .stat-row { padding:7px 0; border-bottom:1px solid rgba(255,255,255,.05); display:flex; justify-content:space-between; align-items:center; font-size:.75rem; }
        .stat-row:last-child { border-bottom:none; }
        .stat-label { color:var(--text-muted); }
        .stat-val { font-family:'Cinzel',serif; font-size:.78rem; color:var(--text); }
        #card-dealing-overlay { display:none; position:fixed; inset:0; z-index:1050; background:radial-gradient(ellipse at center,rgba(5,30,20,.97) 0%,rgba(4,10,18,.98) 100%); backdrop-filter:blur(6px); align-items:center; justify-content:center; flex-direction:column; padding:24px; }
        #card-dealing-overlay.active { display:flex; }
        .dealing-label { font-family:'Cinzel',serif; font-size:.72rem; letter-spacing:.22em; text-transform:uppercase; color:var(--gold-dim); margin-bottom:32px; }
        .hands-wrap { display:flex; gap:48px; justify-content:center; flex-wrap:wrap; width:100%; max-width:680px; }
        .hand-section { text-align:center; flex:1; min-width:180px; max-width:280px; }
        .hand-label { font-family:'Cinzel',serif; font-size:.62rem; letter-spacing:.2em; text-transform:uppercase; color:var(--text-muted); margin-bottom:14px; }
        .cards-row { display:flex; justify-content:center; gap:8px; min-height:110px; margin-bottom:14px; align-items:center; }
        .card-placeholder { width:66px; height:98px; background:#fff; border-radius:9px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.3rem; box-shadow:0 8px 24px rgba(0,0,0,.7),inset 0 1px 0 rgba(255,255,255,.9); animation:cardDeal .45s cubic-bezier(0.34,1.56,0.64,1) both; }
        .card-placeholder.red { color:#c0392b; }
        .card-placeholder.black { color:#1a1a2e; }
        @keyframes cardDeal { 0%{transform:rotateY(-80deg) scale(.7);opacity:0;}65%{transform:rotateY(8deg) scale(1.04);}100%{transform:rotateY(0deg) scale(1);opacity:1;} }
        .hand-total { font-family:'Cinzel',serif; font-size:2rem; font-weight:700; color:var(--gold); min-height:2.4rem; text-shadow:0 0 24px rgba(240,192,64,.4); transition:all .4s; }
        .vs-divider { display:flex; align-items:center; padding-top:60px; color:var(--text-muted); font-family:'Cinzel',serif; font-size:.7rem; letter-spacing:.1em; }
        #result-overlay { display:none; position:fixed; inset:0; z-index:1060; background:radial-gradient(ellipse at center,rgba(8,18,30,.96) 0%,rgba(4,8,14,.98) 100%); backdrop-filter:blur(8px); align-items:center; justify-content:center; flex-direction:column; }
        #result-overlay.active { display:flex; }
        .result-inner { text-align:center; animation:resultPop .5s cubic-bezier(0.34,1.56,0.64,1); padding:20px; }
        @keyframes resultPop { from{transform:scale(.65);opacity:0;}to{transform:scale(1);opacity:1;} }
        .result-heading { font-family:'Cinzel',serif; font-size:clamp(2rem,8vw,4rem); font-weight:700; color:var(--gold); text-shadow:0 0 50px rgba(255,215,0,.5); margin-bottom:12px; letter-spacing:.04em; }
        .result-profit { font-size:1.5rem; font-weight:600; margin-bottom:28px; }
        .profit-win { color:var(--green); } .profit-loss { color:var(--red); } .profit-tie { color:var(--text-dim); }
        .result-cd { font-family:'Cinzel',serif; font-size:.68rem; letter-spacing:.16em; color:var(--text-muted); text-transform:uppercase; }
        .result-cd span { color:var(--gold-dim); }
        #toast-container { position:fixed; bottom:20px; right:20px; z-index:9999; display:flex; flex-direction:column; align-items:flex-end; gap:6px; }
        .toast-msg { background:rgba(12,28,42,.95); border:1px solid rgba(255,215,0,.15); border-radius:20px; padding:7px 18px; font-size:.76rem; color:var(--text); backdrop-filter:blur(10px); animation:toastIn .22s ease; white-space:nowrap; box-shadow:0 4px 20px rgba(0,0,0,.5); }
        @keyframes toastIn { from{transform:translateX(110%);opacity:0;}to{transform:translateX(0);opacity:1;} }
        @media(max-width:1024px) { .game-layout{grid-template-columns:1fr;} .side-panel{display:none;} }
        @media(max-width:576px) { .chip{width:44px;height:44px;font-size:.58rem;} .card-placeholder{width:54px;height:82px;font-size:1.1rem;} .hands-wrap{gap:24px;} }
        /* ── Auth modal ─────────────────────────────────────────────────────── */
        #auth-modal { display:none; position:fixed; inset:0; z-index:2000; background:rgba(4,10,18,.97); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:16px; }
        #auth-modal.show { display:flex; }
        .auth-box { background:var(--bg-panel); border:1px solid var(--gold-border); border-radius:16px; width:100%; max-width:380px; padding:32px 28px; box-shadow:var(--shadow); }
        .auth-logo { font-family:'Cinzel',serif; font-size:1.45rem; font-weight:700; color:var(--gold); text-align:center; margin-bottom:4px; text-shadow:0 0 24px rgba(240,192,64,.3); }
        .auth-sub { text-align:center; font-size:.76rem; color:var(--text-dim); margin-bottom:22px; }
        .auth-tabs { display:flex; border-bottom:1px solid var(--dim-border); margin-bottom:22px; gap:2px; }
        .auth-tab { flex:1; text-align:center; padding:8px 4px; font-size:.75rem; font-family:'Cinzel',serif; letter-spacing:.08em; cursor:pointer; color:var(--text-dim); border-bottom:2px solid transparent; transition:all .2s; }
        .auth-tab.active { color:var(--gold); border-bottom-color:var(--gold); }
        .auth-field { margin-bottom:13px; }
        .auth-field label { display:block; font-size:.68rem; color:var(--text-dim); margin-bottom:5px; letter-spacing:.05em; text-transform:uppercase; }
        .auth-field input { width:100%; background:rgba(0,0,0,.45); border:1px solid rgba(255,255,255,.1); border-radius:8px; padding:9px 12px; color:var(--text); font-size:.85rem; outline:none; transition:border-color .2s; font-family:'DM Sans',sans-serif; }
        .auth-field input:focus { border-color:rgba(240,192,64,.45); background:rgba(0,0,0,.6); }
        .auth-error { font-size:.72rem; color:var(--red); min-height:1.1rem; margin-bottom:12px; text-align:center; line-height:1.4; }
        .btn-auth { width:100%; background:linear-gradient(135deg,#1a4020 0%,#0d2e18 100%); border:1px solid rgba(56,201,122,.4); border-radius:8px; padding:10px; color:var(--green); font-family:'Cinzel',serif; font-size:.8rem; letter-spacing:.1em; cursor:pointer; transition:all .2s; }
        .btn-auth:hover { background:linear-gradient(135deg,#214e28 0%,#122e1d 100%); border-color:var(--green); box-shadow:0 0 16px rgba(56,201,122,.15); }
        .btn-auth:disabled { opacity:.45; cursor:not-allowed; }
        /* ── Header logout ───────────────────────────────────────────────────── */
        .btn-logout { background:transparent; border:1px solid rgba(255,77,94,.3); color:rgba(255,77,94,.7); border-radius:20px; padding:4px 14px; font-size:.68rem; letter-spacing:.05em; cursor:pointer; transition:all .2s; white-space:nowrap; }
        .btn-logout:hover { background:rgba(255,77,94,.1); border-color:var(--red); color:var(--red); }
        .header-player { font-size:.72rem; color:var(--text-dim); }
        .header-player strong { color:var(--text); }
    </style>
</head>
<body>

<div id="game-app"
     data-player-id="0"
     data-player-name=""
     data-balance="0"
     data-result-duration="{{ config('game.result_duration', 5) }}">

<div class="game-layout">
    <header class="game-header">
        <div class="game-logo">🐧 Lucky Puffin</div>
        <div class="header-center">
            <div class="player-count"><i class="bi bi-people-fill"></i> <strong id="player-count">0</strong> online</div>
            <div id="next-round-wrapper" style="display:none;font-size:.7rem;color:var(--text-muted);">Next round in <span id="next-round-seconds" style="color:var(--gold-dim);font-family:'Cinzel',serif;">5s</span></div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <div class="header-player"><strong id="header-player-name"></strong></div>
            <div class="balance-pill"><i class="bi bi-coin me-1"></i><span id="player-balance">0</span><span style="opacity:.6;font-size:.75em;margin-left:4px;">WPUFF</span></div>
            <button class="btn-logout" onclick="doLogout()"><i class="bi bi-box-arrow-right me-1"></i>Logout</button>
        </div>
    </header>

    <aside class="side-panel">
        <div class="panel">
            <div class="panel-header">🏆 Leaderboard</div>
            <div class="panel-body" id="leaderboard"><div style="color:var(--text-muted);font-size:.75rem;text-align:center;padding:8px 0;">Loading...</div></div>
        </div>
        <div class="panel">
            <div class="panel-header">⚡ Live Activity</div>
            <div class="panel-body" id="activity-feed"><div style="color:var(--text-muted);font-size:.73rem;text-align:center;padding:8px 0;">No activity yet</div></div>
        </div>
    </aside>

    <main class="table-col">
        <div class="timer-wrap">
            <span id="bet-timer" class="t-waiting"><i class="bi bi-hourglass-split"></i>&nbsp; Waiting for players...</span>
        </div>
        <div class="felt-table">
            <div class="bet-main">
                <div class="betting-box" data-bet-type="player" onclick="handleBetClick(this)">
                    <div class="box-label" style="color:rgba(61,159,255,.7);">Player</div>
                    <div class="box-odds">Pays 1:1</div>
                    <div class="box-bet" id="player-total">0</div>
                </div>
                <div class="betting-box" data-bet-type="tie" onclick="handleBetClick(this)">
                    <div class="box-label" style="color:rgba(56,201,122,.7);font-size:.55rem;">Tie</div>
                    <div class="box-odds" style="font-size:.65rem;">7:1</div>
                    <div class="box-bet" style="font-size:.9rem;" id="tie-total">0</div>
                </div>
                <div class="betting-box" data-bet-type="banker" onclick="handleBetClick(this)">
                    <div class="box-label" style="color:rgba(255,77,94,.7);">Banker</div>
                    <div class="box-odds">Pays 1:1</div>
                    <div class="box-bet" id="banker-total">0</div>
                </div>
            </div>
            <div class="gold-line"></div>
            <div class="bet-sides">
                <div class="betting-box" data-bet-type="playerPair" onclick="handleBetClick(this)">
                    <div class="box-label" style="font-size:.55rem;">P. Pair</div>
                    <div class="box-odds" style="font-size:.65rem;color:rgba(240,192,64,.5);">10:1</div>
                    <div class="box-bet-sm" id="player-pair-total">0</div>
                </div>
                <div class="betting-box" data-bet-type="randomPair" onclick="handleBetClick(this)">
                    <div class="box-label" style="font-size:.55rem;color:rgba(240,192,64,.7);">Rnd Pair</div>
                    <div class="box-odds" style="font-size:.65rem;color:rgba(240,192,64,.5);">4:1</div>
                    <div class="box-bet-sm" id="random-pair-total">0</div>
                </div>
                <div class="betting-box" data-bet-type="bankerPair" onclick="handleBetClick(this)">
                    <div class="box-label" style="font-size:.55rem;">B. Pair</div>
                    <div class="box-odds" style="font-size:.65rem;color:rgba(240,192,64,.5);">10:1</div>
                    <div class="box-bet-sm" id="banker-pair-total">0</div>
                </div>
            </div>
            <div class="chip-rack">
                @foreach([10 => '10', 50 => '50', 100 => '100', 500 => '500', 1000 => '1K'] as $value => $label)
                    <div class="chip" data-value="{{ $value }}" onclick="selectChip({{ $value }})">{{ $label }}</div>
                @endforeach
            </div>
            <div class="action-row">
                <button class="btn-clear" onclick="clearAllBets()"><i class="bi bi-x-circle-fill me-1"></i> Clear Bets</button>
            </div>
        </div>
    </main>

    <aside class="side-panel">
        <div class="panel">
            <div class="panel-header">👤 Your Stats</div>
            <div class="panel-body">
                <div class="stat-row"><span class="stat-label">Balance</span><span class="stat-val" style="color:var(--green);"><span id="player-balance-side">0</span><span style="font-size:.65em;opacity:.6;"> WPUFF</span></span></div>
                <div class="stat-row"><span class="stat-label">Name</span><span class="stat-val" id="stat-player-name">—</span></div>
                <div class="stat-row"><span class="stat-label">Games</span><span class="stat-val" id="stat-games-played">—</span></div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-header" style="color:rgba(56,201,122,.6);">📋 How to Play</div>
            <div class="panel-body" style="font-size:.72rem;color:var(--text-dim);line-height:1.8;">
                <div>① Pick a chip value</div><div>② Click Player or Banker</div>
                <div>③ Wait for timer to end</div><div>④ Cards deal automatically</div>
                <div style="margin-top:8px;color:var(--gold-dim);">Closest to 9 wins!</div>
            </div>
        </div>
    </aside>
</div>
</div>

<div id="card-dealing-overlay">
    <div class="dealing-label"><i class="bi bi-suit-spade-fill me-2"></i>Dealing Cards<i class="bi bi-suit-heart-fill ms-2" style="color:var(--red);"></i></div>
    <div class="hands-wrap">
        <div class="hand-section">
            <div class="hand-label" style="color:rgba(61,159,255,.6);">— Player —</div>
            <div class="cards-row" id="player-hand"></div>
            <div class="hand-total" id="player-total-score">?</div>
        </div>
        <div class="vs-divider">VS</div>
        <div class="hand-section">
            <div class="hand-label" style="color:rgba(255,77,94,.6);">— Banker —</div>
            <div class="cards-row" id="banker-hand"></div>
            <div class="hand-total" id="banker-total-score">?</div>
        </div>
    </div>
</div>

<div id="result-overlay">
    <div class="result-inner">
        <div class="result-heading" id="result-title">—</div>
        <div class="result-profit" id="result-details"></div>
        <div class="result-cd">Next round in <span id="result-countdown">5s</span></div>
    </div>
</div>

<div id="toast-container"></div>

<!-- ── Auth modal ──────────────────────────────────────────────────────────── -->
<div id="auth-modal">
    <div class="auth-box">
        <div class="auth-logo">🐧 Lucky Puffin</div>
        <div class="auth-sub">Sign in to place bets and track your balance</div>

        <div class="auth-tabs">
            <div class="auth-tab active" id="tab-login"    onclick="showAuthTab('login')">Login</div>
            <div class="auth-tab"        id="tab-register" onclick="showAuthTab('register')">Register</div>
        </div>

        <!-- Login form -->
        <div id="form-login">
            <div class="auth-field">
                <label>Email</label>
                <input type="email" id="login-email" placeholder="your@email.com" onkeydown="if(event.key==='Enter')doLogin()">
            </div>
            <div class="auth-field">
                <label>Password</label>
                <input type="password" id="login-password" placeholder="••••••••" onkeydown="if(event.key==='Enter')doLogin()">
            </div>
            <div class="auth-error" id="login-error"></div>
            <button class="btn-auth" id="btn-login" onclick="doLogin()">Enter the Table</button>
        </div>

        <!-- Register form -->
        <div id="form-register" style="display:none;">
            <div class="auth-field">
                <label>Display Name</label>
                <input type="text" id="reg-name" placeholder="e.g. LuckyPuffin88" onkeydown="if(event.key==='Enter')doRegister()">
            </div>
            <div class="auth-field">
                <label>Email</label>
                <input type="email" id="reg-email" placeholder="your@email.com" onkeydown="if(event.key==='Enter')doRegister()">
            </div>
            <div class="auth-field">
                <label>Password</label>
                <input type="password" id="reg-password" placeholder="Min 8 characters" onkeydown="if(event.key==='Enter')doRegister()">
            </div>
            <div class="auth-field">
                <label>Confirm Password</label>
                <input type="password" id="reg-confirm" placeholder="Repeat password" onkeydown="if(event.key==='Enter')doRegister()">
            </div>
            <div class="auth-error" id="reg-error"></div>
            <button class="btn-auth" id="btn-register" onclick="doRegister()">Create Account</button>
        </div>
    </div>
</div>

    <script>
// ── Auth: show modal if not logged in, otherwise apply stored identity to DOM ──
(function () {
    var token = localStorage.getItem('auth_token');

    if (!token) {
        // Not logged in — show auth modal, game scripts still load but API calls
        // will 401 without a token (betting is gated server-side anyway).
        document.getElementById('auth-modal').classList.add('show');
        return;
    }

    // Apply stored player identity to data-attributes so game.js reads correct values.
    var app = document.getElementById('game-app');
    if (app) {
        app.dataset.playerId   = localStorage.getItem('player_id')      || '0';
        app.dataset.playerName = localStorage.getItem('player_name')    || '';
        app.dataset.balance    = localStorage.getItem('player_balance') || '0';
    }

    // Populate header + sidebar from localStorage immediately (game.js will sync from server).
    var name = localStorage.getItem('player_name') || '';
    var bal  = localStorage.getItem('player_balance') || '0';
    var hn = document.getElementById('header-player-name');   if (hn) hn.textContent = name;
    var sn = document.getElementById('stat-player-name');     if (sn) sn.textContent = name;
    var pb = document.getElementById('player-balance');        if (pb) pb.textContent = bal;
    var ps = document.getElementById('player-balance-side');   if (ps) ps.textContent = bal;
})();

// ── Auth tab switcher ──────────────────────────────────────────────────────────
function showAuthTab(tab) {
    document.getElementById('form-login').style.display    = tab === 'login'    ? '' : 'none';
    document.getElementById('form-register').style.display = tab === 'register' ? '' : 'none';
    document.getElementById('tab-login').classList.toggle('active',    tab === 'login');
    document.getElementById('tab-register').classList.toggle('active', tab === 'register');
}

// ── Login ──────────────────────────────────────────────────────────────────────
async function doLogin() {
    var btn   = document.getElementById('btn-login');
    var errEl = document.getElementById('login-error');
    errEl.textContent = '';
    btn.disabled = true; btn.textContent = 'Logging in…';

    try {
        var res  = await fetch('/api/v1/login', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                       'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || '' },
            body: JSON.stringify({
                email:    document.getElementById('login-email').value.trim(),
                password: document.getElementById('login-password').value,
            }),
        });
        var data = await res.json();

        if (data.success && data.token) {
            localStorage.setItem('auth_token',     data.token);
            localStorage.setItem('player_id',      data.player.id);
            localStorage.setItem('player_name',    data.player.player_name);
            localStorage.setItem('player_balance', data.player.balance);
            window.location.reload();
        } else {
            errEl.textContent = data.message || 'Invalid email or password.';
        }
    } catch (e) {
        errEl.textContent = 'Network error — please try again.';
    }

    btn.disabled = false; btn.textContent = 'Enter the Table';
}

// ── Register ───────────────────────────────────────────────────────────────────
async function doRegister() {
    var btn   = document.getElementById('btn-register');
    var errEl = document.getElementById('reg-error');
    errEl.textContent = '';

    var name    = document.getElementById('reg-name').value.trim();
    var email   = document.getElementById('reg-email').value.trim();
    var pass    = document.getElementById('reg-password').value;
    var confirm = document.getElementById('reg-confirm').value;

    if (!name || !email || !pass) { errEl.textContent = 'All fields are required.'; return; }
    if (pass !== confirm)         { errEl.textContent = 'Passwords do not match.'; return; }
    if (pass.length < 8)          { errEl.textContent = 'Password must be at least 8 characters.'; return; }

    btn.disabled = true; btn.textContent = 'Creating account…';

    try {
        var res  = await fetch('/api/v1/register', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                       'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || '' },
            body: JSON.stringify({
                player_name:           name,
                email:                 email,
                password:              pass,
                password_confirmation: confirm,
            }),
        });
        var data = await res.json();

        if (data.success && data.token) {
            localStorage.setItem('auth_token',     data.token);
            localStorage.setItem('player_id',      data.player.id);
            localStorage.setItem('player_name',    data.player.player_name);
            localStorage.setItem('player_balance', data.player.balance);
            window.location.reload();
        } else {
            if (data.errors) {
                errEl.textContent = Object.values(data.errors).flat().join(' ');
            } else {
                errEl.textContent = data.message || 'Registration failed.';
            }
        }
    } catch (e) {
        errEl.textContent = 'Network error — please try again.';
    }

    btn.disabled = false; btn.textContent = 'Create Account';
}

// ── Logout ─────────────────────────────────────────────────────────────────────
async function doLogout() {
    var token = localStorage.getItem('auth_token');
    if (token) {
        try {
            await fetch('/api/v1/logout', {
                method:  'POST',
                headers: { 'Accept': 'application/json',
                           'Authorization': 'Bearer ' + token,
                           'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || '' },
            });
        } catch (e) { /* server-side revoke failed — local clear is enough */ }
    }
    ['auth_token','player_id','player_name','player_balance','guest_id'].forEach(function (k) {
        localStorage.removeItem(k);
    });
    window.location.reload();
}
</script>

<script src="{{ mix('js/app.js') }}"></script>
<script src="{{ mix('js/game.js') }}"></script>
</body>
</html>
