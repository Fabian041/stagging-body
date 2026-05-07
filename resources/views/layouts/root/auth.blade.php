<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bella - Login')</title>

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/modules/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/izitoast/css/iziToast.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/bootstrap-social/bootstrap-social.css') }}">

    <style>
        :root {
            --navy: #294795;
            --blue: #0070B7;
            --sky: #0097D8;
            --white: #FFFFFF;
            --bg: #F0F4F9;
            --card: #FFFFFF;
            --text: #1A2340;
            --text-muted: #6B7A99;
            --border: #DDE3EF;
            --primary: #0070B7;
            --primary-light: #E8F4FD;
            --success: #16A34A;
            --success-light: #DCFCE7;
            --warning: #D97706;
            --warning-light: #FEF3C7;
            --danger: #DC2626;
            --danger-light: #FEE2E2;
            --shadow: 0 1px 8px rgba(41, 71, 149, .08);
            --shadow-md: 0 4px 20px rgba(41, 71, 149, .12);
            --radius: 8px;
            --radius-lg: 14px;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 13.5px;
            line-height: 1.55;
        }

        body.auth-body {
            min-height: 100vh;
            overflow-x: hidden;
        }

        #app {
            min-height: 100vh;
        }

        .btn {
            border: 0;
            border-radius: var(--radius);
            padding: 10px 14px;
            font-family: inherit;
            font-weight: 700;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: .16s ease;
        }

        .btn-primary,
        .btn-primary:focus {
            background: linear-gradient(135deg, var(--navy), var(--blue));
            color: #fff;
            box-shadow: 0 8px 20px rgba(0, 112, 183, .22);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1d3a80, #0063a3);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(0, 112, 183, .28);
        }

        .login-screen {
            min-height: 100vh;
            display: flex;
            overflow: hidden;
            background: var(--white);
        }

        .login-left {
            flex: 1.15;
            min-height: 100vh;
            background: linear-gradient(145deg, #0e2460 0%, #1a3a8f 40%, #0070B7 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 64px 72px;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 15% 22%, rgba(255, 255, 255, .13), transparent 28%),
                radial-gradient(circle at 85% 78%, rgba(125, 211, 252, .20), transparent 34%),
                linear-gradient(115deg, rgba(255, 255, 255, .07), transparent 40%);
            pointer-events: none;
        }

        .login-left-canvas {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            opacity: .62;
        }

        .login-left-content {
            position: relative;
            z-index: 2;
            max-width: 560px;
        }

        .login-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 52px;
        }

        .login-brand-icon {
            width: 46px;
            height: 46px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .15);
            border: 1px solid rgba(255, 255, 255, .22);
            overflow: hidden;
        }

        .login-brand-icon img {
            width: 34px;
            max-height: 34px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .login-brand-mark {
            width: 24px;
            height: 24px;
            color: #fff;
        }

        .login-brand-name {
            font-size: 20px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.02em;
        }

        .login-brand-sub {
            font-size: 11px;
            color: rgba(255, 255, 255, .58);
            margin-top: 1px;
        }

        .login-headline {
            font-size: 46px;
            font-weight: 800;
            color: #fff;
            line-height: 1.12;
            letter-spacing: -.045em;
            margin-bottom: 18px;
            max-width: 560px;
        }

        .login-headline span {
            color: #7dd3fc;
        }

        .login-tagline {
            font-size: 16px;
            color: rgba(255, 255, 255, .74);
            margin-bottom: 46px;
            max-width: 510px;
            line-height: 1.72;
        }

        .login-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            width: 100%;
            max-width: 470px;
        }

        .login-stat-card {
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .13);
            border-radius: 12px;
            padding: 16px 18px;
            backdrop-filter: blur(10px);
        }

        .login-stat-val {
            font-size: 25px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.035em;
            line-height: 1;
        }

        .login-stat-lbl {
            font-size: 10.5px;
            color: rgba(255, 255, 255, .58);
            margin-top: 5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .login-stat-trend {
            font-size: 10px;
            color: #86efac;
            font-weight: 700;
            margin-top: 4px;
        }

        .login-bottom-label {
            position: absolute;
            bottom: 30px;
            left: 72px;
            font-size: 10.5px;
            color: rgba(255, 255, 255, .34);
            z-index: 2;
        }

        .login-right {
            width: 560px;
            min-height: 100vh;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 64px 68px;
            position: relative;
        }

        .login-right::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(0, 151, 216, .13), transparent 38%),
                linear-gradient(180deg, rgba(248, 250, 255, .95) 0%, #FFFFFF 46%);
            pointer-events: none;
        }

        .login-right::after {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 1px;
            background: linear-gradient(180deg, transparent, #DDE3EF, transparent);
        }

        .login-box {
            width: 100%;
            max-width: 430px;
            position: relative;
            z-index: 1;
        }

        .login-mini-brand {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 11px;
            border: 1px solid #DDE3EF;
            border-radius: 999px;
            background: rgba(255, 255, 255, .78);
            color: var(--navy);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .02em;
            margin-bottom: 24px;
            box-shadow: 0 4px 16px rgba(41, 71, 149, .06);
        }

        .login-mini-brand svg {
            width: 14px;
            height: 14px;
            color: var(--navy);
        }

        .login-welcome {
            margin-bottom: 24px;
        }

        .login-logo-text {
            font-size: 30px;
            font-weight: 800;
            letter-spacing: -.05em;
            color: var(--text);
            line-height: 1.15;
        }

        .login-logo-sub {
            font-size: 13.5px;
            color: var(--text-muted);
            margin-top: 8px;
            line-height: 1.7;
            max-width: 410px;
        }

        .login-form-panel {
            display: flex;
            flex-direction: column;
        }

        .login-form-panel .form-group {
            margin-bottom: 14px;
        }

        .login-form-panel label {
            font-size: 11.5px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 6px;
        }

        .login-form-panel .form-control {
            width: 100%;
            min-height: 42px;
            padding: 10px 12px;
            border: 1px solid #DDE3EF;
            border-radius: 8px;
            font-family: inherit;
            font-size: 12.8px;
            color: var(--text);
            background: #fff;
            outline: none;
            transition: .15s ease;
            box-shadow: none;
        }

        .login-form-panel .form-control:focus {
            border-color: var(--sky);
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10);
        }

        .login-form-panel .form-control.is-invalid {
            border-color: var(--danger);
            background-image: none;
        }

        .invalid-feedback {
            font-size: 11px;
            font-weight: 600;
        }

        .login-password-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .login-remember {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 2px 0 18px;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 600;
        }

        .custom-control-input:checked~.custom-control-label::before {
            border-color: var(--primary);
            background-color: var(--primary);
        }

        .login-submit {
            width: 100%;
            min-height: 44px;
        }

        .login-footnote {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            font-size: 10.5px;
            color: #8A97AD;
            text-align: center;
            margin-top: 14px;
        }

        .login-footnote span {
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: #CBD5E1;
        }

        .login-register-note {
            margin-top: 18px;
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
        }

        .login-register-note a {
            color: var(--primary);
            font-weight: 800;
            text-decoration: none;
        }

        .login-register-note a:hover {
            text-decoration: underline;
        }

        .simple-footer {
            margin-top: 18px;
            text-align: center;
            font-size: 10.5px;
            color: #9aa6ba;
        }

        .auth-alert {
            border: 1px solid var(--danger-light);
            background: #fff7f7;
            color: #991b1b;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 14px;
            font-size: 12px;
            font-weight: 700;
        }

        .auth-alert-success {
            border-color: var(--success-light);
            background: #f0fdf4;
            color: #166534;
        }

        @media (max-width: 1080px) {
            .login-headline {
                font-size: 38px;
                max-width: 460px;
            }

            .login-tagline {
                font-size: 14px;
                max-width: 430px;
            }

            .login-left {
                padding: 56px 48px;
            }

            .login-bottom-label {
                left: 48px;
            }

            .login-right {
                width: 500px;
                padding: 54px 46px;
            }
        }

        @media (max-width: 760px) {
            .login-screen {
                display: block;
                min-height: 100vh;
                background: linear-gradient(180deg, #0e2460 0%, #0070B7 44%, #fff 44%, #fff 100%);
            }

            .login-left {
                display: none;
            }

            .login-right {
                width: 100%;
                min-height: 100vh;
                padding: 42px 26px;
                align-items: center;
            }

            .login-right::after {
                display: none;
            }

            .login-box {
                max-width: 390px;
            }
        }



        /* ===== CENTERED LOGIN VARIANT ===== */
        .login-screen.login-centered {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 34px 18px;
            overflow: hidden;
            background:
                radial-gradient(circle at 18% 18%, rgba(0, 151, 216, .34), transparent 30%),
                radial-gradient(circle at 82% 76%, rgba(125, 211, 252, .24), transparent 32%),
                linear-gradient(145deg, #0e2460 0%, #1a3a8f 42%, #0070B7 100%);
        }

        .login-centered::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(115deg, rgba(255, 255, 255, .09), transparent 42%),
                radial-gradient(circle at center, transparent 0%, rgba(5, 18, 52, .16) 70%);
            pointer-events: none;
        }

        .login-centered .login-left-canvas {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: .70;
            z-index: 0;
        }

        .login-card-shell {
            width: 100%;
            max-width: 432px;
            position: relative;
            z-index: 2;
        }

        .login-card-shell::before,
        .login-card-shell::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
            filter: blur(2px);
        }

        .login-card-shell::before {
            width: 120px;
            height: 120px;
            top: -44px;
            right: -42px;
            background: rgba(125, 211, 252, .18);
        }

        .login-card-shell::after {
            width: 92px;
            height: 92px;
            bottom: -34px;
            left: -34px;
            background: rgba(255, 255, 255, .12);
        }

        .login-centered .login-box {
            width: 100%;
            max-width: 432px;
            position: relative;
            z-index: 2;
            padding: 30px;
            border-radius: 18px;
            background: rgba(255, 255, 255, .96);
            border: 1px solid rgba(255, 255, 255, .42);
            box-shadow: 0 28px 70px rgba(5, 18, 52, .28), 0 1px 0 rgba(255, 255, 255, .65) inset;
            backdrop-filter: blur(16px) saturate(160%);
            -webkit-backdrop-filter: blur(16px) saturate(160%);
        }

        .login-page-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 22px;
            text-align: left;
        }

        .login-page-brand-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--navy), var(--sky));
            box-shadow: 0 10px 22px rgba(0, 112, 183, .25);
            overflow: hidden;
            flex-shrink: 0;
        }

        .login-page-brand-icon img {
            width: 34px;
            max-height: 34px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .login-page-brand-name {
            font-size: 17px;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -.025em;
            line-height: 1.2;
        }

        .login-page-brand-sub {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .04em;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-top: 2px;
        }

        .login-centered .login-mini-brand {
            margin: 0 auto 18px;
            width: fit-content;
            box-shadow: none;
            background: var(--primary-light);
            border-color: #bfdbfe;
        }

        .login-centered .login-welcome {
            margin-bottom: 22px;
            text-align: center;
        }

        .login-centered .login-logo-text {
            font-size: 28px;
        }

        .login-centered .login-logo-sub {
            margin-left: auto;
            margin-right: auto;
            max-width: 340px;
            font-size: 13px;
        }

        .login-centered .login-form-panel .form-group {
            margin-bottom: 13px;
        }

        .login-centered .login-form-panel .form-control {
            min-height: 44px;
            background: #F8FAFF;
        }

        .login-centered .login-form-panel .form-control:focus {
            background: #fff;
        }

        .login-centered .login-submit {
            min-height: 45px;
            border-radius: 10px;
        }

        .login-centered .simple-footer {
            color: rgba(255, 255, 255, .62);
            text-shadow: 0 1px 8px rgba(0, 0, 0, .18);
            margin-top: 16px;
        }

        .login-card-footer {
            margin-top: 18px;
            text-align: center;
            font-size: 10.5px;
            color: rgba(255, 255, 255, .54);
            position: relative;
            z-index: 2;
        }

        @media (max-width: 760px) {
            .login-screen.login-centered {
                display: flex;
                padding: 24px 16px;
                background:
                    radial-gradient(circle at 15% 12%, rgba(0, 151, 216, .30), transparent 32%),
                    linear-gradient(145deg, #0e2460 0%, #0070B7 100%);
            }

            .login-centered .login-box {
                padding: 24px 20px;
                border-radius: 16px;
            }

            .login-card-shell {
                max-width: 390px;
            }
        }


        /* @yield('custom-style')

        */
    </style>
</head>

<body class="auth-body">
    <div id="app">
        @yield('main')
    </div>

    <script src="{{ asset('assets/modules/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/modules/popper.js') }}"></script>
    <script src="{{ asset('assets/modules/tooltip.js') }}"></script>
    <script src="{{ asset('assets/modules/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/modules/nicescroll/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('assets/modules/moment.min.js') }}"></script>
    <script src="{{ asset('assets/modules/izitoast/js/iziToast.min.js') }}"></script>
    <script src="{{ asset('assets/js/stisla.js') }}"></script>

    @yield('custom-script')

    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/service-worker.js')
                .then(function(registration) {
                    console.log('Service Worker registered with scope:', registration.scope);
                })
                .catch(function(error) {
                    console.error('Service Worker registration failed:', error);
                });
        }
    </script>
</body>

</html>
