@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-book-reader me-2"></i>Sistema de Préstamos
            </a>

            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button"
                        data-bs-toggle="dropdown">
                        <div class="me-2">
                            <i class="fas fa-user-circle fa-lg me-1"></i>
                            <span>{{ $usuario['nombre'] }}</span>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text">
                                <i class="fas fa-id-badge me-2"></i>Rol: {{ $usuario['rol'] }}
                            </span></li>
                        <li><span class="dropdown-item-text">
                                <i class="fas fa-envelope me-2"></i>{{ $usuario['email'] }}
                            </span></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-tachometer-alt me-2"></i>Panel de Control</h2>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>
        </div>

        <div class="row g-4">
            <!-- Card Libros -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-book fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title">Gestión de Libros</h5>
                        <p class="card-text text-muted">
                            Consulta el catálogo de libros disponibles
                            @if ($usuario['rol'] === 'Bibliotecario')
                                y administra el inventario
                            @endif
                        </p>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="{{ route('libros.index') }}" class="btn btn-primary w-100">
                            <i class="fas fa-eye me-2"></i>Ver Libros
                        </a>
                        @if ($usuario['rol'] === 'Bibliotecario')
                            <a href="{{ route('libros.create') }}" class="btn btn-success w-100 mt-2">
                                <i class="fas fa-plus me-2"></i>Agregar Libro
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card Préstamos (próximamente) -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-handshake fa-3x text-success"></i>
                        </div>
                        <h5 class="card-title">Gestión de Préstamos</h5>
                        <p class="card-text text-muted">
                            @if ($usuario['rol'] === 'Bibliotecario')
                                Administra los préstamos de libros
                            @else
                                Consulta tus préstamos activos
                            @endif
                        </p>
                    </div>
                    <div class="card-footer bg-transparent">
                        <button class="btn btn-secondary w-100" disabled>
                            <i class="fas fa-clock me-2"></i>Próximamente
                        </button>
                    </div>
                </div>
            </div>

            <!-- Card Reportes (solo bibliotecarios) -->
            @if ($usuario['rol'] === 'Bibliotecario')
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-chart-bar fa-3x text-warning"></i>
                            </div>
                            <h5 class="card-title">Reportes</h5>
                            <p class="card-text text-muted">
                                Genera reportes de libros más prestados y estadísticas
                            </p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <button class="btn btn-secondary w-100" disabled>
                                <i class="fas fa-clock me-2"></i>Próximamente
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Información del usuario -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Información de la Sesión</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nombre:</strong> {{ $usuario['nombre'] }}</p>
                                <p><strong>Email:</strong> {{ $usuario['email'] }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Rol:</strong>
                                    <span
                                        class="badge {{ $usuario['rol'] === 'Bibliotecario' ? 'bg-primary' : 'bg-secondary' }}">
                                        {{ $usuario['rol'] }}
                                    </span>
                                </p>
                                <p><strong>Último acceso:</strong> {{ date('d/m/Y H:i:s') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
