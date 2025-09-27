<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Basic Page Info -->
    <meta charset="utf-8">
    <title>Register - {{ config('app.name', 'Laravel') }}</title>

    <!-- Site favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/vendors/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/vendors/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/vendors/images/favicon-16x16.png">

    <!-- Mobile Specific Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="/vendors/styles/core.css">
    <link rel="stylesheet" type="text/css" href="/vendors/styles/icon-font.min.css">

    <link rel="stylesheet" type="text/css" href="/vendors/styles/style.css">

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-119386393-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'UA-119386393-1');
    </script>
</head>

<body class="login-page">

    <div class="register-page-wrap d-flex align-items-center flex-wrap justify-content-center">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 col-lg-7">
                    <img src="/vendors/images/logo.png" alt="Register">
                </div>
                <div class="col-md-6 col-lg-5">
                    <div class="register-box bg-white box-shadow border-radius-10">
                        <div class="login-title">
                            <h2 class="text-center text-primary">Register </h2>
                        </div>

                        <div class="wizard-content">
                            <form class="tab-wizard2 wizard-circle wizard" method="POST"
                                action="{{ route('register') }}">
                                @csrf

                                <h5>Basic Account Credentials</h5>
                                <section>
                                    <div class="form-wrap max-width-600 mx-auto">
                                        <!-- Name -->
                                        <div class="form-group row">
                                            <label class="col-sm-4 col-form-label" for="name">Full Name*</label>
                                            <div class="col-sm-8">
                                                <input type="text" id="name" name="name" class="form-control"
                                                    value="{{ old('name') }}" required autofocus autocomplete="name">
                                                <x-input-error :messages="$errors->get('name')" class="mt-2 text-danger" />
                                            </div>
                                        </div>

                                        <!-- Email Address -->
                                        <div class="form-group row">
                                            <label class="col-sm-4 col-form-label" for="email">Email Address*</label>
                                            <div class="col-sm-8">
                                                <input type="email" id="email" name="email" class="form-control"
                                                    value="{{ old('email') }}" required autocomplete="username">
                                                <x-input-error :messages="$errors->get('email')" class="mt-2 text-danger" />
                                            </div>
                                        </div>

                                        <!-- Password -->
                                        <div class="form-group row">
                                            <label class="col-sm-4 col-form-label" for="password">Password*</label>
                                            <div class="col-sm-8">
                                                <input type="password" id="password" name="password"
                                                    class="form-control" required autocomplete="new-password">
                                                <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger" />
                                            </div>
                                        </div>

                                        <!-- Confirm Password -->
                                        <div class="form-group row">
                                            <label class="col-sm-4 col-form-label" for="password_confirmation">Confirm
                                                Password*</label>
                                            <div class="col-sm-8">
                                                <input type="password" id="password_confirmation"
                                                    name="password_confirmation" class="form-control" required
                                                    autocomplete="new-password">
                                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-danger" />
                                            </div>
                                        </div>

                                        <div class="form-group row mt-4">
                                            <div class="col-sm-12">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="terms"
                                                        name="terms" required>
                                                    <label class="custom-control-label" for="terms">
                                                        I have read and agreed to the terms of services and privacy
                                                        policy
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row mt-3">
                                            <div class="col-sm-12 text-center">
                                                <a class="text-primary" href="{{ route('login') }}">
                                                    {{ __('Already registered?') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('status'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                $('#success-modal').modal('show');
            });
        </script>
    @endif

    <!-- js -->
    <script src="/vendors/scripts/core.js"></script>
    <script src="/vendors/scripts/script.min.js"></script>
    <script src="/vendors/scripts/process.js"></script>
    <script src="/vendors/scripts/layout-settings.js"></script>
</body>

</html>
