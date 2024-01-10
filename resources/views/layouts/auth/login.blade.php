@extends('layouts.root.auth')

@section('main')
    <section class="section">
        <div class="container mt-5">
            <div class="text-center mb-4">
                <img src="{{ asset('assets/img/bella.png') }}" alt="Bella" class="img-fluid"
                    style="max-width: 200px; margin: auto">
            </div>
            <div class="row">
                <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4>Login</h4>
                        </div>

                        <div class="card-body">
                            <form method="POST" action="{{ route('login.auth') }}" class="needs-validation">
                                @csrf
                                @method('POST')
                                <div class="form-group">
                                    <label for="npk">NPK</label>
                                    <input id="npk" type="string"
                                        class="form-control @error('npk') is-invalid @enderror" name="npk"
                                        tabindex="1" required autofocus autocomplete="off">
                                    @error('npk')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <div class="d-block">
                                        <label for="password" class="control-label">Password</label>
                                    </div>
                                    <input id="password" type="password"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        tabindex="2" required>
                                    @error('password')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="remember" class="custom-control-input" tabindex="3"
                                            id="remember-me">
                                        <label class="custom-control-label" for="remember-me">Remember Me</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4"
                                        id="login">
                                        Login
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="mt-3 text-muted text-center">
                        Don't have an account? <a href="{{ route('register.index') }}">Create One</a>
                    </div>
                    <div class="simple-footer">
                        Copyright &copy; ITD 2023
                    </div>
                </div>
            </div>
        </div>
    </section>
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

            $('#npk').focus();

            $("#npk").keypress(function(e) {
                if (e.keyCode == 124) {
                    e.preventDefault();
                    $("#password").focus();
                }
            });

        });

        function notif(type, message) {
            if (type == 'error') {
                iziToast.error({
                    title: 'Error!  ' + message,
                    position: 'topCenter'
                });
            } else if (type == 'success') {
                iziToast.success({
                    title: 'Success! ' + message,
                    position: 'topCenter'
                });
            }
        }
    </script>
@endsection
