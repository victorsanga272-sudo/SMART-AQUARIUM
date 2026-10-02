<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SACP Admin | System overview</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        :root {
            color-scheme: light;
            --ink: #19231f;
            --muted: #76817b;
            --line: #e3e9e5;
            --paper: #f5f7f4;
            --surface: #fff;
            --forest: #176b50;
            --forest-light: #e5f3ec;
            --amber: #aa6221;
            --amber-light: #fff2df;
            --mono: "IBM Plex Mono", monospace;
            --sans: "DM Sans", sans-serif;
        }

        * { box-sizing: border-box; }
        body { margin: 0; background: var(--paper); color: var(--ink); font-family: var(--sans); font-size: 14px; }
        a { color: inherit; text-decoration: none; }
        button, input { font: inherit; }
        .topbar { height: 68px; display: flex; align-items: center; justify-content: space-between; padding: 0 5vw; background: var(--surface); border-bottom: 1px solid var(--line); }
        .brand { display: flex; align-items: center; gap: 12px; font-weight: 700; letter-spacing: .02em; }
        .brand-mark { width: 34px; height: 34px; display: grid; place-items: center; background: var(--forest); color: #fff; font-size: 11px; border-radius: 6px; }
        .brand-divider { height: 22px; border-left: 1px solid var(--line); margin: 0 2px; }
        .brand-section { color: var(--muted); font-weight: 500; }
        .top-actions { display: flex; align-items: center; gap: 18px; }
        .admin-label { color: var(--muted); font-size: 12px; }
        .admin-email { color: var(--ink); font-weight: 600; }
        .link-button { display: inline-flex; align-items: center; gap: 8px; border: 1px solid var(--line); border-radius: 5px; background: var(--surface); padding: 9px 12px; font-size: 12px; font-weight: 600; transition: border-color .15s, background .15s; }
        .link-button:hover { border-color: #b8c8be; background: #f8fbf9; }
        main { width: min(1440px, 90vw); margin: 0 auto; padding: 36px 0 56px; }
        .heading-row { display: flex; align-items: end; justify-content: space-between; gap: 20px; margin-bottom: 25px; }
        .eyebrow { margin: 0 0 7px; color: var(--forest); font: 500 11px var(--mono); letter-spacing: .08em; text-transform: uppercase; }
        h1 { margin: 0; font-size: clamp(24px, 3vw, 32px); line-height: 1.15; letter-spacing: 0; }
        .subtitle { margin: 8px 0 0; color: var(--muted); }
        .updated { color: var(--muted); font: 11px var(--mono); white-space: nowrap; }
        .stats { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; margin-bottom: 26px; }
        .stat { min-width: 0; padding: 17px 18px 16px; background: var(--surface); border: 1px solid var(--line); border-radius: 6px; }
        .stat-label { display: flex; align-items: center; gap: 8px; color: var(--muted); font-size: 12px; }
        .stat-label i { color: var(--forest); width: 14px; text-align: center; }
        .stat-value { margin-top: 12px; font: 600 27px var(--mono); letter-spacing: 0; }
        .stat-note { margin-top: 4px; color: var(--muted); font-size: 11px; }
        .content-grid { display: grid; grid-template-columns: minmax(0, 1.8fr) minmax(280px, .8fr); gap: 18px; align-items: start; }
        .panel { background: var(--surface); border: 1px solid var(--line); border-radius: 6px; min-width: 0; }
        .panel-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 17px 19px; border-bottom: 1px solid var(--line); }
        .panel-title { margin: 0; font-size: 14px; font-weight: 700; }
        .count { color: var(--muted); font: 11px var(--mono); }
        .search { position: relative; width: min(250px, 48%); }
        .search i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #89948d; font-size: 12px; }
        .search input { width: 100%; height: 34px; padding: 0 10px 0 31px; border: 1px solid var(--line); border-radius: 4px; color: var(--ink); background: #fbfcfb; outline: none; font-size: 12px; }
        .search input:focus { border-color: #75a88f; box-shadow: 0 0 0 2px #e5f3ec; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 11px 15px; color: #758079; background: #fafbfa; font: 500 10px var(--mono); text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
        td { padding: 13px 15px; border-top: 1px solid #edf0ed; vertical-align: middle; }
        tbody tr:hover { background: #fbfcfb; }
        .user-name { font-weight: 600; }
        .user-email { margin-top: 3px; color: var(--muted); font-size: 11px; }
        .mono { font: 11px var(--mono); white-space: nowrap; }
        .reading { line-height: 1.7; white-space: nowrap; font: 11px var(--mono); }
        .reading span { color: var(--muted); }
        .state { display: inline-flex; align-items: center; gap: 6px; padding: 5px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; white-space: nowrap; }
        .state::before { width: 6px; height: 6px; border-radius: 50%; background: currentColor; content: ""; }
        .state-online { color: var(--forest); background: var(--forest-light); }
        .state-offline { color: #7b8580; background: #eef1ef; }
        .empty { padding: 28px 16px; color: var(--muted); text-align: center; }
        .pagination { display: flex; justify-content: space-between; align-items: center; padding: 13px 18px; border-top: 1px solid var(--line); color: var(--muted); font-size: 11px; }
        .pagination-links { display: flex; gap: 6px; }
        .pagination-links a, .pagination-links span { min-width: 29px; height: 29px; display: grid; place-items: center; border: 1px solid var(--line); border-radius: 4px; }
        .pagination-links a:hover { border-color: var(--forest); color: var(--forest); }
        .pagination-links .active { border-color: var(--forest); background: var(--forest); color: #fff; }
        .activity-list { list-style: none; margin: 0; padding: 0; }
        .activity-item { padding: 14px 17px; border-bottom: 1px solid #edf0ed; }
        .activity-item:last-child { border-bottom: 0; }
        .activity-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .command { font: 500 11px var(--mono); }
        .command-status { color: var(--muted); font-size: 10px; text-transform: capitalize; }
        .activity-owner { margin-top: 6px; color: var(--muted); font-size: 11px; }
        .activity-time { margin-top: 5px; color: #96a099; font: 10px var(--mono); }
        .no-activity { padding: 26px 18px; color: var(--muted); font-size: 12px; }
        .footer-note { display: flex; align-items: center; gap: 7px; margin: 16px 0 0; color: var(--muted); font-size: 11px; }
        .footer-note i { color: var(--forest); }
        @media (max-width: 1080px) { .stats { grid-template-columns: repeat(3, minmax(0, 1fr)); } .content-grid { grid-template-columns: 1fr; } }
        @media (max-width: 640px) { .topbar { height: auto; min-height: 62px; padding: 12px 5vw; } .brand-section, .brand-divider, .admin-label { display: none; } .admin-email { max-width: 38vw; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 11px; } .top-actions { gap: 8px; } .link-button { padding: 8px 9px; } main { width: 92vw; padding-top: 25px; } .heading-row { align-items: start; flex-direction: column; } .updated { white-space: normal; } .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; } .stat { padding: 13px; } .stat-value { font-size: 23px; } .panel-head { padding: 14px; flex-wrap: wrap; } .search { width: 100%; } th, td { padding: 11px 12px; } .pagination { align-items: start; flex-direction: column; gap: 10px; } }
    </style>
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('admin.dashboard') }}" aria-label="SACP admin home">
            <span class="brand-mark">SACP</span>
            <span>Operations</span>
            <span class="brand-divider"></span>
            <span class="brand-section">Administration</span>
        </a>
        <div class="top-actions">
            <span class="admin-label">Signed in as <span class="admin-email">{{ auth()->user()->email }}</span></span>
            <a class="link-button" href="{{ route('dashboard') }}"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> Aquarium view</a>
        </div>
    </header>

    <main>
        <section class="heading-row" aria-labelledby="page-title">
            <div>
                <p class="eyebrow">SACP / Operations</p>
                <h1 id="page-title">System overview</h1>
                <p class="subtitle">Accounts, connected devices, and platform activity.</p>
            </div>
            <p class="updated">SNAPSHOT &middot; {{ now()->format('M j, Y / H:i') }} UTC</p>
        </section>

        <section class="stats" aria-label="System metrics">
            <article class="stat"><div class="stat-label"><i class="fa-solid fa-users" aria-hidden="true"></i>Registered users</div><div class="stat-value">{{ number_format($stats['users']) }}</div><div class="stat-note">All platform accounts</div></article>
            <article class="stat"><div class="stat-label"><i class="fa-solid fa-fish-fins" aria-hidden="true"></i>Aquarium accounts</div><div class="stat-value">{{ number_format($stats['aquariums']) }}</div><div class="stat-note">Provisioned per user</div></article>
            <article class="stat"><div class="stat-label"><i class="fa-solid fa-link" aria-hidden="true"></i>Paired devices</div><div class="stat-value">{{ number_format($stats['paired']) }}</div><div class="stat-note">Authenticated device tokens</div></article>
            <article class="stat"><div class="stat-label"><i class="fa-solid fa-wave-square" aria-hidden="true"></i>Telemetry records</div><div class="stat-value">{{ number_format($stats['telemetry']) }}</div><div class="stat-note">Readings stored</div></article>
            <article class="stat"><div class="stat-label"><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>Pending commands</div><div class="stat-value">{{ number_format($stats['pendingCommands']) }}</div><div class="stat-note">Awaiting device response</div></article>
        </section>

        <div class="content-grid">
            <section class="panel" aria-labelledby="accounts-title">
                <div class="panel-head">
                    <div><h2 class="panel-title" id="accounts-title">Aquarium accounts</h2><span class="count">{{ number_format($users->total()) }} total</span></div>
                    <form class="search" method="GET" action="{{ route('admin.dashboard') }}" role="search">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <input type="search" name="q" value="{{ $search }}" placeholder="Search name, email, or SAIN" aria-label="Search aquarium accounts">
                    </form>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Account</th><th>SAIN</th><th>Device</th><th>Latest reading</th><th>Joined</th></tr></thead>
                        <tbody>
                            @forelse ($users as $user)
                                @php($aquarium = $user->aquarium)
                                @php($reading = $aquarium ? $latestTelemetry->get($aquarium->id) : null)
                                <tr>
                                    <td><div class="user-name">{{ $user->username }}</div><div class="user-email">{{ $user->email }}</div></td>
                                    <td class="mono">{{ $aquarium?->sain ?? '—' }}</td>
                                    <td><span class="state {{ $aquarium?->device_token_hash ? 'state-online' : 'state-offline' }}">{{ $aquarium?->device_token_hash ? 'Paired' : 'Unpaired' }}</span></td>
                                    <td class="reading">@if ($reading)<span>{{ number_format($reading->temperature, 1) }}°C</span> &middot; {{ number_format($reading->ph, 2) }} pH<br><span>{{ number_format($reading->turbidity, 1) }} NTU</span>@else<span>No readings</span>@endif</td>
                                    <td class="mono">{{ $user->created_at?->format('M j, Y') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="empty">{{ $search ? 'No accounts match that search.' : 'No accounts have been created yet.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($users->hasPages())
                    <div class="pagination">
                        <span>Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}</span>
                        <div class="pagination-links">
                            @if ($users->onFirstPage())<span aria-disabled="true"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></span>@else<a href="{{ $users->previousPageUrl() }}" aria-label="Previous page"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></a>@endif
                            <span class="active" aria-current="page">{{ $users->currentPage() }}</span>
                            @if ($users->hasMorePages())<a href="{{ $users->nextPageUrl() }}" aria-label="Next page"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></a>@else<span aria-disabled="true"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></span>@endif
                        </div>
                    </div>
                @endif
            </section>

            <section class="panel" aria-labelledby="activity-title">
                <div class="panel-head"><h2 class="panel-title" id="activity-title">Recent actuator commands</h2><span class="count">LAST 8</span></div>
                @forelse ($recentCommands as $command)
                    <article class="activity-item">
                        <div class="activity-top"><span class="command">{{ str($command->command)->replace('-', ' ')->title() }}</span><span class="command-status">{{ $command->status }}</span></div>
                        <div class="activity-owner">{{ $command->aquarium?->owner?->username ?? 'Unknown account' }} &middot; {{ $command->aquarium?->sain ?? 'No SAIN' }}</div>
                        <div class="activity-time">{{ $command->created_at?->diffForHumans() ?? 'Time unavailable' }}</div>
                    </article>
                @empty
                    <div class="no-activity">No actuator commands have been recorded.</div>
                @endforelse
            </section>
        </div>
        <p class="footer-note"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i>Admin access is restricted to configured allowlisted accounts.</p>
    </main>
</body>
</html>