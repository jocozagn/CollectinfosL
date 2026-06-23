<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Connexion Admin – Collectinfos</title>

    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon-32.png') }}" type="image/png" sizes="32x32">
    <link rel="icon" href="{{ asset('favicon-16.png') }}" type="image/png" sizes="16x16">
    <link rel="apple-touch-icon" href="{{ asset('favicon-180.png') }}">

</head>

<body class="admin-login-page">

    <div class="login-shell">

        <div class="login-card">

            <div class="login-brand-header">

                <img

                    src="{{ asset('images/collectinfo-logo.jpg') }}"

                    alt="Collectinfos"

                    class="login-brand-logo"

                >

                <p class="login-brand-name">Collectinfos</p>

                <p class="login-brand-subtitle">L'information, notre engagement.</p>

            </div>



            <div class="login-form-heading">

                <h1 class="login-form-title">Connexion</h1>

                <p class="login-form-subtitle">Accédez à votre espace d'administration</p>

            </div>



            <div class="login-form-body">

                @if ($errors->any())

                    <div class="alert alert-error">

                        @foreach ($errors->all() as $error)

                            <p style="margin:0">{{ $error }}</p>

                        @endforeach

                    </div>

                @endif



                <form method="POST" action="{{ route('admin.login.submit') }}">

                    @csrf

                    <div class="form-group">

                        <label for="email">E-mail</label>

                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>

                    </div>

                    <div class="form-group">

                        <label for="password">Mot de passe</label>

                        <input type="password" id="password" name="password" required>

                    </div>

                    <label class="checkbox-label">

                        <input type="checkbox" name="remember"> Se souvenir de moi

                    </label>

                    <button type="submit" class="btn btn-primary btn-block">

                        <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i> Se connecter

                    </button>

                </form>



                <a href="{{ route('home') }}" class="back-link">

                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour au site

                </a>

            </div>

        </div>

    </div>

</body>

</html>

