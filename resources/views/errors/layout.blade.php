<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') — @yield('heading')</title>
    <link rel="icon" href="{{ asset('assets/img/bella.png') }}">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, Segoe UI, sans-serif;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            color: #1A2340;
            padding: 24px;
        }

        .e-card {
            width: 100%;
            max-width: 460px;
            background: #fff;
            border: 1px solid #DDE3EF;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(41, 71, 149, .12);
            padding: 40px 32px;
            text-align: center;
        }

        .e-icon {
            width: 76px;
            height: 76px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: var(--tint, #fef2f2);
            color: var(--accent, #dc2626);
            font-size: 34px;
        }

        .e-code {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .12em;
            color: var(--accent, #dc2626);
            text-transform: uppercase;
        }

        .e-title {
            font-size: 22px;
            font-weight: 850;
            margin: 6px 0 10px;
            letter-spacing: -.02em;
        }

        .e-desc {
            font-size: 13.5px;
            color: #6B7A99;
            line-height: 1.6;
            margin: 0 0 26px;
            font-weight: 500;
        }

        .e-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .e-btn {
            height: 42px;
            padding: 0 20px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .e-btn--primary { background: #0070B7; color: #fff; }
        .e-btn--primary:hover { filter: brightness(.95); color: #fff; }
        .e-btn--ghost { background: #fff; color: #6B7A99; border-color: #DDE3EF; }
        .e-btn--ghost:hover { background: #f8fafc; color: #1A2340; }
    </style>
</head>

<body style="--accent: @yield('accent', '#dc2626'); --tint: @yield('tint', '#fef2f2');">
    <div class="e-card">
        <div class="e-icon">@yield('emoji', '⚠️')</div>
        <div class="e-code">@yield('code')</div>
        <h1 class="e-title">@yield('heading')</h1>
        <p class="e-desc">@yield('message')</p>
        <div class="e-actions">
            <a href="{{ url('/dashboard/overview') }}" class="e-btn e-btn--primary">⟵ Ke Dashboard</a>
            <a href="javascript:history.back()" class="e-btn e-btn--ghost">Kembali</a>
        </div>
    </div>
</body>

</html>
