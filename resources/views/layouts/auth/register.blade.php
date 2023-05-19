@extends('layouts.root.auth')

@section('main')
    <section class="section">
        <div class="container mt-5">
            <div class="row">
                <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
                    <h3 class="text-dark text-center mb-3 mt-5">ELECTRIC</h3>

                    <div class="card card-primary">
                        <div class="card-header">
                            <h4>Register</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('register.store') }}" class="needs-validation"
                                novalidate="">
                                @csrf
                                @method('POST')
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input id="name" type="name"
                                        class="form-control @error('name') is-invalid @enderror" name="name"
                                        tabindex="1" required autofocus>
                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input id="email" type="email"
                                        class="form-control @error('email') is-invalid @enderror" name="email"
                                        tabindex="1" required autofocus>
                                    @error('email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="npk">NPK</label>
                                    <input id="npk" type="npk"
                                        class="form-control @error('npk') is-invalid @enderror" name="npk"
                                        tabindex="1" required autofocus>
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
                                    <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4">
                                        Register
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                    <div class="mt-3 text-muted text-center">
                        Already have account? <a href="{{ route('login.index') }}">Sign in</a>
                    </div>
                    <div class="simple-footer">
                        Copyright &copy; ITD 2023
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
