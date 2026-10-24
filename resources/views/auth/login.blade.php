@extends('layouts.guest')

@section('title', 'Matriz LA/FT - Iniciar Sesión')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
<div class="login-layout">

    {{-- Nota izquierda --}}


    {{-- Tarjeta de login --}}
    <section class="login-card">

        <div class="login-card__header">
            <div class="app-icon"><span>M</span></div>
            <h1 class="login-card__title">Matriz LA/FT</h1>
            <p class="login-card__subtitle">Priorización de eventos de riesgo</p>
        </div>

        <div class="divider"></div>

        <h2 class="login-card__form-title">Iniciar sesión</h2>

        <form method="POST" action="{{ route('login') }}" class="login-form" id="login-form">
            @csrf

            {{-- Usuario --}}
            <div class="field">
                <label class="field__label" for="email">
                    Correo electrónico <span class="required">*</span>
                </label>
                <div class="field__control">
                    <input
                        class="field__input @error('email') field__input--error @enderror"
                        id="email"
                        name="email"
                        type="text"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                        autofocus>
                    <span class="field__icon">
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                        </svg>
                    </span>
                </div>
            </div>

            {{-- Contraseña --}}
            <div class="field">
                <label class="field__label" for="password">
                    Contraseña <span class="required">*</span>
                </label>
                <div class="field__control">
                    <input
                        class="field__input field__input--password @error('password') field__input--error @enderror"
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required>
                    <button type="button" class="field__toggle" id="toggle-password" aria-label="Mostrar contraseña">
                        <svg class="icon icon--eye" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                        </svg>
                        <svg class="icon icon--eye-off is-hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Recordar / olvidó contraseña --}}
            <div class="form-helpers">
                <label class="checkbox">
                    <input type="checkbox" name="remember" {{ old('remember', true) ? 'checked' : '' }}>
                    <span>Recordar usuario</span>
                </label>
                <span>Solicite ayuda al administrador para recuperar el acceso.</span>
            </div>

            <button type="submit" class="btn-primary">
                <span>Ingresar</span>
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                </svg>
            </button>
        </form>

        {{-- Estado de error (se muestra solo si falla el login) --}}
        @if ($errors->any())
            <div class="alert-error" role="alert">
                <svg class="icon icon--sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                </svg>
                <span>Usuario o contraseña incorrectos.</span>
            </div>
        @endif

        {{-- Acceso rápido por rol --}}
    </section>

    {{-- Nota derecha --}}
    <aside class="note note--right">
        <div class="note__title">
            <svg class="icon icon--sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
            </svg>
            Política institucional
        </div>
        <p class="note__text note__text--italic">
            No hay registro público: el Administrador crea los usuarios.
        </p>
    </aside>

</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/login.js') }}"></script>
@endpush
