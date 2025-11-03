<!DOCTYPE html>
<html lang="en" data-theme="dark" data-bs-theme="dark">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Production — Choose Area</title>

    <!-- Fonts: Industrial (Russo One) + Body (Barlow) -->
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;600;800&family=Russo+One&display=swap"
        rel="stylesheet" />
    <!-- (Optional) Bootstrap only for reset/grid -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <style>
        :root {
            --bg: #06090f;
            --fg: #e8eef7;
            --muted: #9fb2c6;
            --glass: #0c1320cc;
            --border: #1e2a40;
            --ring: #2e3b55;
            --as: #60a5fa;
            --ma: #22c55e;
            --radius: 22px;
            --blur: 14px;
            --shadow: 0 20px 60px rgba(0, 0, 0, .45);
            --tile: rgba(255, 255, 255, .06);

            --font-heading: 'Russo One', ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial;
            --font-body: 'Barlow', ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial;
            --caps-space: .08em;
            /* letterspacing headings */
        }

        [data-theme="light"] {
            --bg: #f6f9ff;
            --fg: #0b1220;
            --muted: #5d6b82;
            --glass: #ffffffcc;
            --border: #e6eef9;
            --ring: #c8d7f2;
            --as: #0ea5e9;
            --ma: #16a34a;
            --shadow: 0 16px 40px rgba(2, 6, 23, .12);
            --tile: rgba(2, 6, 23, .06);
        }

        html,
        body {
            height: 100%
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--fg);
            font-family: var(--font-body);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .backdrop {
            position: fixed;
            inset: -15vmax;
            z-index: -1;
            filter: blur(110px) saturate(110%);
            background:
                radial-gradient(40vmax 40vmax at 12% 18%, #3b82f6 18%, transparent 60%),
                radial-gradient(38vmax 38vmax at 88% 12%, #22c55e 14%, transparent 60%),
                radial-gradient(42vmax 42vmax at 50% 92%, #8b5cf6 10%, transparent 60%);
            opacity: .18;
            pointer-events: none;
        }

        .wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 28px clamp(16px, 3vw, 28px);
            display: grid;
            gap: 26px
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px
        }

        .brand h1 {
            margin: 0;
            font-family: var(--font-heading);
            text-transform: uppercase;
            letter-spacing: var(--caps-space);
            font-size: clamp(2.1rem, 4vw, 3rem);
            line-height: 1;
        }

        .brand .sub {
            margin-top: 4px;
            color: var(--muted);
            font-family: var(--font-heading);
            text-transform: uppercase;
            letter-spacing: .18em;
            font-size: .85rem;
        }

        .tools {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap
        }

        .pill {
            display: flex;
            align-items: center;
            gap: .8rem;
            padding: .85rem 1rem;
            background: var(--glass);
            border: 1px solid var(--border);
            border-radius: 14px;
            backdrop-filter: blur(var(--blur));
            -webkit-backdrop-filter: blur(var(--blur));
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .06);
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .pill input[type="date"] {
            border: 0;
            background: transparent;
            color: var(--fg);
            font-weight: 800;
            letter-spacing: .02em;
            font-size: clamp(1rem, 1.4vw, 1.1rem);
            outline: none;
            font-variant-numeric: tabular-nums;
        }

        /* Theme icon button */
        .btn-theme {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: var(--glass);
            border: 1px solid var(--border);
            backdrop-filter: blur(var(--blur));
            -webkit-backdrop-filter: blur(var(--blur));
            color: var(--fg);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .06);
            cursor: pointer;
        }

        .btn-theme:hover {
            border-color: var(--ring)
        }

        .btn-theme svg {
            width: 22px;
            height: 22px;
            display: none
        }

        html[data-theme="light"] .icon-sun {
            display: block
        }

        html[data-theme="dark"] .icon-moon {
            display: block
        }

        .grid {
            display: grid;
            gap: 22px;
            grid-template-columns: 1fr;
            align-items: stretch
        }

        @media(min-width:980px) {
            .grid {
                grid-template-columns: 1fr 1fr
            }
        }

        .glass {
            position: relative;
            isolation: isolate;
            min-height: 320px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: var(--glass);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(var(--blur));
            -webkit-backdrop-filter: blur(var(--blur));
            overflow: hidden;
            transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
            cursor: pointer;
        }

        .glass:hover {
            transform: translateY(-4px);
            border-color: var(--ring);
            box-shadow: 0 24px 70px rgba(0, 0, 0, .5)
        }

        .glow {
            content: "";
            position: absolute;
            inset: -1px;
            z-index: -1;
            background: radial-gradient(1200px 400px at 10% -20%, color-mix(in oklab, var(--as) 45%, transparent), transparent 70%);
            opacity: .10;
            pointer-events: none;
        }

        .glass.ma .glow {
            background: radial-gradient(1200px 400px at 10% -20%, color-mix(in oklab, var(--ma) 45%, transparent), transparent 70%);
        }

        .badge-icon {
            width: 108px;
            height: 108px;
            border-radius: 22px;
            display: grid;
            place-items: center;
            font-family: var(--font-heading);
            font-size: 2.2rem;
            letter-spacing: var(--caps-space);
            color: #0b0f14;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .28), 0 8px 20px rgba(0, 0, 0, .25);
            margin-bottom: 16px;
            user-select: none;
            text-transform: uppercase;
        }

        .glass.as .badge-icon {
            background: linear-gradient(180deg, color-mix(in oklab, var(--as) 94%, white 6%), color-mix(in oklab, var(--as) 72%, black 8%))
        }

        .glass.ma .badge-icon {
            background: linear-gradient(180deg, color-mix(in oklab, var(--ma) 94%, white 6%), color-mix(in oklab, var(--ma) 72%, black 8%))
        }

        .title {
            font-family: var(--font-heading);
            text-transform: uppercase;
            letter-spacing: var(--caps-space);
            font-size: clamp(1.8rem, 3vw, 2.4rem);
            margin: 0 0 2px;
        }

        .desc {
            color: var(--muted);
            font-family: var(--font-heading);
            text-transform: uppercase;
            letter-spacing: .18em;
            font-size: .95rem;
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 10px
        }

        .btn-ghost {
            text-decoration: none;
            color: var(--fg);
            font-weight: 800;
            letter-spacing: .02em;
            padding: 1rem 1.15rem;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: linear-gradient(180deg, rgba(255, 255, 255, .06), rgba(255, 255, 255, .02));
        }

        .btn-ghost:hover {
            border-color: var(--ring);
            background: var(--tile)
        }

        .as .btn-ghost {
            border-color: color-mix(in oklab, var(--as) 40%, var(--border))
        }

        .ma .btn-ghost {
            border-color: color-mix(in oklab, var(--ma) 40%, var(--border))
        }

        .hint {
            color: var(--muted);
            font-size: .92rem;
            margin-top: 4px
        }

        .glass:focus-visible {
            outline: 2px solid var(--ring);
            outline-offset: 3px
        }

        .btn-glass-back {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            height: 46px;
            padding: .6rem .95rem;
            border-radius: 14px;
            background: var(--glass);
            border: 1px solid var(--border);
            backdrop-filter: blur(var(--blur));
            -webkit-backdrop-filter: blur(var(--blur));
            color: inherit;
            text-decoration: none;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .06);
            font-weight: 800;
            letter-spacing: .02em;
        }

        .btn-glass-back:hover {
            border-color: var(--ring);
            background: var(--tile)
        }

        .btn-glass-back .ico {
            width: 18px;
            height: 18px;
            flex: 0 0 18px
        }

        @media(max-width:576px) {
            .btn-back-label {
                display: none
            }
        }
    </style>
</head>

@php
    $todayISO = \Carbon\Carbon::parse($selectedDate ?? now())->format('Y-m-d');
    $routeBoard = route('dashboard.board');
    $routePlan = route('dashboard.prodPlan');
@endphp

<body>
    <div class="backdrop" aria-hidden="true"></div>

    <div class="wrap">
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('dashboard.index') }}" class="btn-glass-back" id="btnBack" title="Back (Alt+←)">
                    <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    <span class="btn-back-label">Back</span>
                </a>
                <div class="brand">
                    <h1>Production</h1>
                    <div class="sub">Choose Area</div>
                </div>
            </div>

            <div class="tools">
                <div class="pill">
                    <span>Date</span>
                    <input id="selDate" type="date" value="{{ $todayISO }}">
                </div>

                <!-- Theme toggle icon -->
                <button id="themeToggle" class="btn-theme" aria-label="Toggle theme" title="Toggle theme">
                    <!-- Sun -->
                    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="4" />
                        <path
                            d="M12 1.5v3M12 19.5v3M22.5 12h-3M4.5 12h-3M18.4 18.4l-2.1-2.1M7.7 7.7 5.6 5.6M18.4 5.6 16.3 7.7M7.7 16.3 5.6 18.4" />
                    </svg>
                    <!-- Moon -->
                    <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 13a8.5 8.5 0 1 1-10-10 7 7 0 0 0 10 10z" />
                    </svg>
                </button>
            </div>
        </header>


        <main class="grid">
            <!-- AS -->
            <section class="glass as" data-group="AS" tabindex="0" aria-label="Open Assembly Lines board">
                <i class="glow"></i>
                <div>
                    <div class="badge-icon">AS</div>
                    <h2 class="title">Assembly Lines</h2>
                    <div class="desc">AS003 — AS004</div>
                </div>
                <div class="mt-5">
                    <div class="actions">
                        <a class="btn-ghost btn-board" href="#">Open Board</a>
                    </div>
                </div>
            </section>

            <!-- MA -->
            <section class="glass ma" data-group="MA" tabindex="0" aria-label="Open Material Lines board">
                <i class="glow"></i>
                <div>
                    <div class="badge-icon">MA</div>
                    <h2 class="title">Machining Lines</h2>
                    <div class="desc">MA001 — MA008</div>
                </div>
                <div class="mt-5">
                    <div class="actions">
                        <a class="btn-ghost btn-board" href="#">Open Board</a>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        // theme toggle
        (() => {
            const html = document.documentElement;
            const apply = t => {
                html.setAttribute('data-theme', t);
                html.setAttribute('data-bs-theme', t);
                localStorage.setItem('board-theme', t);
            };
            const saved = localStorage.getItem('board-theme');
            const prefers = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            apply(saved || prefers);

            document.getElementById('themeToggle')?.addEventListener('click', () => {
                apply(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
            });
        })();

        const BASE_BOARD = @json($routeBoard);
        const BASE_PLAN = @json($routePlan);

        function makeUrl(base, group) {
            const d = document.getElementById('selDate').value || @json($todayISO);
            const u = new URL(base, window.location.origin);
            u.searchParams.set('date', d);
            u.searchParams.set('group', group);
            return u.pathname + u.search;
        }

        function wireCard(card) {
            const group = card.dataset.group;
            const boardUrl = makeUrl(BASE_BOARD, group);
            const planUrl = makeUrl(BASE_PLAN, group);

            // set tombol Board (wajib ada)
            const boardBtn = card.querySelector('.btn-board');
            if (boardBtn) boardBtn.setAttribute('href', boardUrl);

            // set tombol Plan (opsional — aman kalau tidak ada)
            const planBtn = card.querySelector('.btn-plan');
            if (planBtn) planBtn.setAttribute('href', planUrl);

            // klik seluruh kartu -> ke Board
            card.addEventListener('click', (e) => {
                if (e.target.closest('a')) return; // kalau klik <a>, biarkan default
                window.location.assign(boardUrl);
            });

            // aksesibilitas: Enter/Space
            card.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    window.location.assign(boardUrl);
                }
            });
        }

        function wireAll() {
            document.querySelectorAll('.glass').forEach(wireCard);
        }
        document.addEventListener('DOMContentLoaded', wireAll);
        document.getElementById('selDate').addEventListener('change', wireAll);
    </script>
</body>

</html>
