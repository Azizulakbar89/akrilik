<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="login-box bg-white box-shadow border-radius-10">
        <div class="login-title">
            <h2 class="text-center text-primary">Login To {{ config('app.name', 'Laravel') }}</h2>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div class="input-group custom">
                <input type="email" id="email" name="email" class="form-control form-control-lg"
                    placeholder="Email" value="{{ old('email') }}" required autofocus autocomplete="username">
                <div class="input-group-append custom">
                    <span class="input-group-text"><i class="icon-copy dw dw-user1"></i></span>
                </div>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />

            <!-- Password -->
            <div class="input-group custom mt-4">
                <input type="password" id="password" name="password" class="form-control form-control-lg"
                    placeholder="Password" required autocomplete="current-password">
                <div class="input-group-append custom">
                    <span class="input-group-text"><i class="dw dw-padlock1"></i></span>
                </div>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />

            <!-- Remember Me -->
            <div class="row pb-30 mt-4">
                <div class="col-6">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="remember_me" name="remember">
                        <label class="custom-control-label" for="remember_me">Remember Me</label>
                    </div>
                </div>
                <div class="col-6">
                    @if (Route::has('password.request'))
                        <div class="forgot-password">
                            <a href="{{ route('password.request') }}">Forgot Password?</a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="input-group mb-0">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            Sign In
                        </button>
                    </div>

                    @if (Route::has('register'))
                        <div class="font-16 weight-600 pt-10 pb-10 text-center" data-color="#707373">OR</div>
                        <div class="input-group mb-0">
                            <a class="btn btn-outline-primary btn-lg btn-block" href="{{ route('register') }}">
                                Register To Create Account
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</x-guest-layout>
