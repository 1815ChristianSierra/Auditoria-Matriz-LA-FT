@extends('layouts.app')

@php
    // $usuarios y $roles llegan desde UserController@index; si no llegan (o llegan null) se usan colecciones vacías
    $usuarios = $usuarios ?? collect();
    $roles    = $roles ?? collect();
    $estados = ['Activo', 'Bloqueado'];

    // Columnas de la matriz (mismo orden que los permisos de abajo)
    $columnasRol = ['Analista', 'Supervisor', 'Admin.', 'Auditor'];

    // [función del sistema, [Analista, Supervisor, Admin., Auditor]]  1 = permitido, 0 = no
    $permisos = $permisos ?? [
        ['Ver tablero y resultados',                         [1, 1, 1, 1]],
        ['Registrar eventos y calificar factores',           [1, 0, 0, 0]],
        ['Registrar decisión y plan de acción',              [1, 1, 0, 0]],
        ['Gestionar clientes y coincidencias',               [1, 1, 0, 0]],
        ['Análisis de sensibilidad',                         [0, 1, 1, 1]],
        ['Editar catálogos y borradores de parámetros',      [0, 0, 1, 0]],
        ['Proponer versión de parámetros',                   [0, 0, 1, 0]],
        ['Aprobar o rechazar versión (por confirmar)',       [0, 1, 0, 1]],
        ['Ver bitácora y verificar integridad',              [0, 1, 0, 1]],
        ['Gestionar usuarios y roles',                       [0, 0, 1, 0]],
    ];
@endphp

@section('title', 'Usuarios y roles · Matriz LA/FT')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/usuarios.css') }}">
@endpush

@section('content')

    {{-- Encabezado de página --}}
    <header class="page-header">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li>Administración</li>
                <li class="breadcrumb__sep">/</li>
                <li class="breadcrumb__current">Usuarios y roles</li>
            </ol>
        </nav>
        <h1 class="page-title">Usuarios y roles</h1>
    </header>

    <div class="page-body">
        <div class="layout">

            {{-- Columna izquierda: usuarios + matriz de permisos --}}
            <div class="col">

                {{-- Usuarios del sistema --}}
                <section class="panel" aria-label="Usuarios del sistema">
                    <div class="panel__head">
                        <div class="panel__head-left">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            <h2 class="panel__title">Usuarios del sistema</h2>
                        </div>
                        <span class="panel__meta">{{ count($usuarios) }} usuarios registrados</span>
                    </div>

                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Usuario</th>
                                    <th scope="col">Nombre completo</th>
                                    <th scope="col">Correo</th>
                                    <th scope="col">Rol</th>
                                    <th scope="col" class="col-center">Estado</th>
                                    <th scope="col">Actualizado</th>
                                    <th scope="col" class="col-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($usuarios as $u)
                                    <tr data-fila-usuario>
                                        <td>
                                            <span class="user role--{{ strtolower($u->rol?->nombre ?? '') }}">
                                                <span class="avatar">{{ strtoupper(substr($u->username, 0, 2)) }}</span>
                                                {{ $u->username }}
                                            </span>
                                        </td>
                                        <td class="col-strong">{{ $u->name }}</td>
                                        <td class="col-mono">{{ $u->email }}</td>
                                        <td class="col-strong">{{ ucfirst($u->rol?->nombre ?? '—') }}</td>
                                        <td class="col-center"><span class="state state--{{ strtolower($u->estado) }}">{{ ucfirst($u->estado) }}</span></td>
                                        <td class="col-mono">{{ $u->updated_at ? $u->updated_at->format('Y-m-d') : '—' }}</td>
                                        <td class="col-right">
                                            <button type="button" class="link" data-editar
                                                    data-url="{{ Route::has('usuarios.update') ? route('usuarios.update', $u->id) : '#' }}"
                                                    data-usuario="{{ $u->username }}"
                                                    data-nombre="{{ $u->name }}"
                                                    data-correo="{{ $u->email }}"
                                                    data-rol="{{ $u->rol_id }}"
                                                    data-estado="{{ ucfirst($u->estado) }}">Editar</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="col-center col-muted">Aún no hay usuarios registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- Matriz de permisos por rol --}}
                <section class="panel" aria-label="Matriz de permisos por rol">
                    <div class="panel__head">
                        <div class="panel__head-left">
                            <svg class="is-ok" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            <h2 class="panel__title">Matriz de permisos por rol</h2>
                        </div>
                        <span class="panel__meta">Control de acceso basado en roles (RBAC)</span>
                    </div>

                    <div class="table-wrap">
                        <table class="table table--perms">
                            <thead>
                                <tr>
                                    <th scope="col">Función del sistema</th>
                                    @foreach ($columnasRol as $columna)
                                        <th scope="col" class="col-center col-role">{{ $columna }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permisos as [$funcion, $accesos])
                                    <tr>
                                        <td class="col-fn">{{ $funcion }}</td>
                                        @foreach ($accesos as $permitido)
                                            <td class="col-center">
                                                @if ($permitido)
                                                    <span class="check" title="Permitido">
                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"/></svg>
                                                    </span>
                                                @else
                                                    <span class="dash" title="Sin acceso">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            {{-- Columna derecha: alta / edición de usuario --}}
            <section class="panel" aria-label="Formulario de usuario">
                <div class="panel__head">
                    <div class="panel__head-left">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        <h2 class="panel__title" id="form-titulo">Nuevo usuario</h2>
                    </div>
                    <span class="panel__meta panel__meta--mono" id="form-etiqueta">Alta RBAC</span>
                </div>

                {{-- Alta: usuarios.store. Edición: el JS cambia el action a usuarios.update y envía _method=PUT --}}
                <form method="POST" action="{{ Route::has('usuarios.store') ? route('usuarios.store') : '#' }}" id="form-usuario" class="form">
                    @csrf
                    <input type="hidden" name="_method" value="PUT" id="u-metodo" disabled>

                    <div>
                        <label class="field-label" for="u-usuario">Usuario <span class="required">*</span></label>
                        <div class="control-wrap control-wrap--prefix">
                            <span class="control-wrap__prefix">&#64;</span>
                            <input type="text" id="u-usuario" name="usuario" class="control" placeholder="usuario" value="{{ old('usuario') }}" autocomplete="off" required>
                        </div>
                    </div>

                    <div>
                        <label class="field-label" for="u-nombre">Nombre completo <span class="required">*</span></label>
                        <input type="text" id="u-nombre" name="nombre" class="control" placeholder="Nombre completo" value="{{ old('nombre') }}" required>
                    </div>

                    <div>
                        <label class="field-label" for="u-correo">Correo electrónico <span class="required">*</span></label>
                        <input type="email" id="u-correo" name="correo" class="control control--mono" placeholder="correo@ejemplo.test" value="{{ old('correo') }}" required>
                    </div>

                    <div class="row">
                        <div>
                            <label class="field-label" for="u-rol">Rol <span class="required">*</span></label>
                            <select id="u-rol" name="rol_id" class="control" required>
                                @foreach ($roles as $rol)
                                    <option value="{{ $rol->id }}" {{ (old('rol_id') ? old('rol_id') == $rol->id : $rol->nombre === 'analista') ? 'selected' : '' }}>{{ ucfirst($rol->nombre) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="field-label" for="u-estado">Estado</label>
                            <select id="u-estado" name="estado" class="control">
                                @foreach ($estados as $estado)
                                    <option value="{{ $estado }}" {{ old('estado', 'Activo') === $estado ? 'selected' : '' }}>{{ $estado }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="field-label" for="u-pass">Contraseña temporal <span class="required">*</span></label>
                        <div class="control-wrap control-wrap--suffix">
                            <input type="password" id="u-pass" name="contrasena" class="control" placeholder="••••••••••••" autocomplete="new-password" required>
                            <button type="button" class="control-wrap__btn" id="u-pass-ver" title="Mostrar contraseña" aria-label="Mostrar u ocultar contraseña">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            </button>
                        </div>
                        <p class="field-hint" id="u-pass-ayuda">El usuario deberá actualizarla en su primer inicio de sesión.</p>
                    </div>

                    <div class="info">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        <span>Las acciones de creación y cambio de roles quedan auditadas de forma inmutable con firma criptográfica.</span>
                    </div>

                    <div class="form__actions">
                        <button type="submit" id="btn-guardar" class="btn-secondary btn--grow">Guardar usuario</button>
                        <button type="reset" class="btn-ghost">Cancelar</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/usuarios.js') }}"></script>
@endpush
 