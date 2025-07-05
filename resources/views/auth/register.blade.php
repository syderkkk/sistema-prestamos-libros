@extends('layouts.app')

@section('title', 'Registrarse')

@section('content')
<div class="login-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="icon-container">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <h3 class="fw-bold">Crear Cuenta</h3>
                            <p class="text-muted">Completa la información para registrarte</p>
                        </div>

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="nombre" class="form-label">
                                    <i class="fas fa-user me-2"></i>Nombre Completo
                                </label>
                                <input type="text" 
                                       class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="{{ old('nombre') }}" 
                                       placeholder="Tu nombre completo"
                                       required>
                                @error('nombre')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-times-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Correo Electrónico
                                </label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       placeholder="ejemplo@correo.com"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-times-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Contraseña
                                </label>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Mínimo 6 caracteres"
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-times-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Confirmar Contraseña
                                </label>
                                <input type="password" 
                                       class="form-control @error('password_confirmation') is-invalid @enderror" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       placeholder="Confirma tu contraseña"
                                       required>
                                @error('password_confirmation')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-times-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="rol_id" class="form-label">
                                    <i class="fas fa-users me-2"></i>Tipo de Usuario
                                </label>
                                <select class="form-control @error('rol_id') is-invalid @enderror" 
                                        id="rol_id" 
                                        name="rol_id" 
                                        required>
                                    <option value="">Selecciona tu rol</option>
                                    @foreach($roles as $rol)
                                        <option value="{{ $rol['id'] }}" {{ old('rol_id') == $rol['id'] ? 'selected' : '' }}>
                                            {{ $rol['nombre'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('rol_id')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-times-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-4">
                                <i class="fas fa-user-plus me-2"></i>Crear Cuenta
                            </button>
                        </form>

                        <div class="text-center">
                            <p class="mb-0">¿Ya tienes una cuenta? 
                                <a href="{{ route('login') }}" class="text-link">Inicia sesión aquí</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection