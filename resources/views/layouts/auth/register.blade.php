@extends('layouts.root.auth')

@section('title', 'Bella - Register')

@section('main')
    <main class="login-screen login-centered">
        <canvas class="login-left-canvas" id="loginCanvas"></canvas>

        <section class="login-card-shell" aria-label="Register Bella System">
            <div class="login-box">
                <div class="login-mini-brand">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"
                        stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="16" rx="3"></rect>
                        <path d="M7 8h10M7 12h6M7 16h4"></path>
                    </svg>
                    <span>BELLA</span>
                </div>

                <div class="login-welcome">
                    <div class="login-logo-text">Create Account</div>
                    <div class="login-logo-sub">
                        Register your Bella account to access Body Electronic Logistic Application
                    </div>
                </div>

                @if (session('error'))
                    <div class="auth-alert">{{ session('error') }}</div>
                @endif

                @if (session('success'))
                    <div class="auth-alert auth-alert-success">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('register.store') }}" class="login-form-panel needs-validation"
                    novalidate>
                    @csrf
                    @method('POST')

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                            name="name" value="{{ old('name') }}" tabindex="1" required autofocus autocomplete="off"
                            placeholder="Masukkan nama lengkap">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" tabindex="2" required autocomplete="off"
                            placeholder="Masukkan email">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="npk">NPK</label>
                        <input id="npk" type="text" class="form-control @error('npk') is-invalid @enderror"
                            name="npk" value="{{ old('npk') }}" tabindex="3" required autocomplete="off"
                            placeholder="Masukkan NPK">
                        @error('npk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="login-password-row">
                            <label for="password" class="mb-0">Password</label>
                        </div>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                            name="password" tabindex="4" required placeholder="Buat password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary login-submit" tabindex="5">
                        <span>Register</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"></path>
                            <path d="M13 6l6 6-6 6"></path>
                        </svg>
                    </button>

                    <div class="login-footnote">
                        Internal use only <span></span> ITD <span></span> Bella
                    </div>
                </form>

                <div class="login-register-note">
                    Already have an account? <a href="{{ route('login.index') }}">Sign in</a>
                </div>
            </div>

            <div class="login-card-footer">
                Copyright &copy; ITD {{ date('Y') }} · Bella System
            </div>
        </section>
    </main>
@endsection

@section('custom-script')
    <script>
        var errorMessage = "{!! session('error') !!}";
        var successMessage = "{!! session('success') !!}";

        if (errorMessage) {
            notif('error', errorMessage);
        } else if (successMessage) {
            notif('success', successMessage);
        }

        $(document).ready(function() {
            $('#name').focus();

            $('#npk').keypress(function(e) {
                if (e.keyCode === 124) {
                    e.preventDefault();
                    $('#password').focus();
                }
            });
        });

        function notif(type, message) {
            if (type === 'error') {
                iziToast.error({
                    title: 'Error!',
                    message: message,
                    position: 'topCenter'
                });
            } else if (type === 'success') {
                iziToast.success({
                    title: 'Success!',
                    message: message,
                    position: 'topCenter'
                });
            }
        }

        (function initLoginCanvas() {
            const canvas = document.getElementById('loginCanvas');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            let width = 0;
            let height = 0;
            let dots = [];
            let raf = null;

            function resize() {
                width = canvas.offsetWidth;
                height = canvas.offsetHeight;
                canvas.width = width * window.devicePixelRatio;
                canvas.height = height * window.devicePixelRatio;
                ctx.setTransform(window.devicePixelRatio, 0, 0, window.devicePixelRatio, 0, 0);
                dots = Array.from({
                    length: width < 520 ? 34 : 58
                }, function() {
                    return {
                        x: Math.random() * width,
                        y: Math.random() * height,
                        vx: (Math.random() - .5) * .28,
                        vy: (Math.random() - .5) * .28,
                        r: Math.random() * 1.7 + .75
                    };
                });
            }

            function draw() {
                ctx.clearRect(0, 0, width, height);

                dots.forEach(function(dot) {
                    dot.x += dot.vx;
                    dot.y += dot.vy;

                    if (dot.x < 0 || dot.x > width) dot.vx *= -1;
                    if (dot.y < 0 || dot.y > height) dot.vy *= -1;

                    ctx.beginPath();
                    ctx.arc(dot.x, dot.y, dot.r, 0, Math.PI * 2);
                    ctx.fillStyle = 'rgba(255,255,255,.46)';
                    ctx.fill();
                });

                for (let i = 0; i < dots.length; i++) {
                    for (let j = i + 1; j < dots.length; j++) {
                        const dx = dots[i].x - dots[j].x;
                        const dy = dots[i].y - dots[j].y;
                        const dist = Math.sqrt(dx * dx + dy * dy);

                        if (dist < 130) {
                            ctx.beginPath();
                            ctx.moveTo(dots[i].x, dots[i].y);
                            ctx.lineTo(dots[j].x, dots[j].y);
                            ctx.strokeStyle = 'rgba(255,255,255,' + (0.18 * (1 - dist / 130)) + ')';
                            ctx.lineWidth = 1;
                            ctx.stroke();
                        }
                    }
                }

                raf = requestAnimationFrame(draw);
            }

            resize();
            draw();
            window.addEventListener('resize', function() {
                if (raf) cancelAnimationFrame(raf);
                resize();
                draw();
            });
        })();
    </script>
@endsection
