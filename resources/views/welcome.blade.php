@extends('layouts.app')

@section('title', 'Biblioteca Digital')

@section('content')
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-book-reader me-2"></i>Biblioteca Digital
            </a>

            <div class="navbar-nav ms-auto">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-outline-light me-2">
                        <i class="fas fa-sign-in-alt me-1"></i>Iniciar Sesión
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-light">
                        <i class="fas fa-user-plus me-1"></i>Registrarse
                    </a>
                @else
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button"
                            data-bs-toggle="dropdown">
                            <div class="me-2">
                                <i class="fas fa-user-circle fa-lg me-1"></i>
                                <span>{{ $usuario['nombre'] ?? Auth::user()->name }}</span>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text">
                                    <i class="fas fa-id-badge me-2"></i>{{ $usuario['rol'] ?? 'Usuario' }}
                                </span></li>
                            <li><span class="dropdown-item-text">
                                    <i class="fas fa-envelope me-2"></i>{{ $usuario['email'] ?? Auth::user()->email }}
                                </span></li>
                            <li><hr class="dropdown-divider"></li>
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
                @endguest
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-75">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">
                            Tu biblioteca digital
                            <span class="text-gradient">inteligente</span>
                        </h1>
                        <p class="hero-subtitle">
                            Descubre, gestiona y disfruta de miles de libros con la tecnología más avanzada. 
                            Una experiencia de lectura completamente renovada.
                        </p>
                        <div class="hero-actions">
                            @guest
                                <a href="{{ route('register') }}" class="btn btn-hero-primary">
                                    <i class="fas fa-rocket me-2"></i>Comenzar Ahora
                                </a>
                                <a href="{{ route('libros.index') }}" class="btn btn-hero-secondary">
                                    <i class="fas fa-search me-2"></i>Explorar Catálogo
                                </a>
                            @else
                                <a href="{{ route('libros.index') }}" class="btn btn-hero-primary">
                                    <i class="fas fa-book-open me-2"></i>Mi Biblioteca
                                </a>
                                @if (isset($usuario) && $usuario['rol'] === 'Bibliotecario')
                                    <a href="{{ route('libros.create') }}" class="btn btn-hero-secondary">
                                        <i class="fas fa-plus me-2"></i>Agregar Libro
                                    </a>
                                @endif
                            @endguest
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-visual">
                        <div class="floating-elements">
                            <div class="floating-card book-card-1">
                                <i class="fas fa-book-open"></i>
                                <span>Catálogo Digital</span>
                            </div>
                            <div class="floating-card book-card-2">
                                <i class="fas fa-user-friends"></i>
                                <span>Comunidad</span>
                            </div>
                            <div class="floating-card book-card-3">
                                <i class="fas fa-chart-line"></i>
                                <span>Estadísticas</span>
                            </div>
                        </div>
                        <div class="hero-illustration">
                            <i class="fas fa-book-reader fa-10x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section py-5">
        <div class="container">
            <!-- Stats -->
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number">1,250</h3>
                            <p class="stat-label">Libros Disponibles</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon success">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number">890</h3>
                            <p class="stat-label">Lectores Activos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon warning">
                            <i class="fas fa-download"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number">340</h3>
                            <p class="stat-label">Préstamos Activos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon info">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number">4.9</h3>
                            <p class="stat-label">Calificación</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Features -->
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h4 class="feature-title">Búsqueda Inteligente</h4>
                        <p class="feature-description">
                            Encuentra cualquier libro con nuestro motor de búsqueda avanzado. 
                            Filtra por autor, género, año y más criterios.
                        </p>
                        <a href="{{ route('libros.index') }}" class="feature-link">
                            Explorar ahora <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4 class="feature-title">Acceso Móvil</h4>
                        <p class="feature-description">
                            Lleva tu biblioteca contigo. Accede desde cualquier dispositivo 
                            con nuestra plataforma completamente responsiva.
                        </p>
                        <a href="#" class="feature-link">
                            Próximamente <i class="fas fa-clock ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h4 class="feature-title">Gestión Avanzada</h4>
                        <p class="feature-description">
                            @auth
                                @if (isset($usuario) && $usuario['rol'] === 'Bibliotecario')
                                    Panel completo para administrar inventario, préstamos y generar reportes detallados.
                                @else
                                    Seguimiento personal de tus préstamos, historial y recomendaciones personalizadas.
                                @endif
                            @else
                                Herramientas completas para bibliotecarios y experiencia personalizada para lectores.
                            @endauth
                        </p>
                        <a href="#" class="feature-link">
                            Próximamente <i class="fas fa-clock ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    @guest
    <section class="cta-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 class="cta-title">¿Listo para comenzar?</h2>
                    <p class="cta-subtitle">
                        Únete a nuestra comunidad de lectores y descubre una nueva forma de disfrutar los libros.
                    </p>
                    <div class="cta-actions">
                        <a href="{{ route('register') }}" class="btn btn-cta-primary">
                            <i class="fas fa-user-plus me-2"></i>Crear Cuenta Gratis
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-cta-secondary">
                            <i class="fas fa-sign-in-alt me-2"></i>Ya tengo cuenta
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endguest

    <!-- Alerts -->
    @if (session('success') || session('error'))
    <div class="container mt-4">
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
    @endif
@endsection

@section('scripts')
<style>
    .hero-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        padding: 2rem 0;
        position: relative;
        overflow: hidden;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%),
                    radial-gradient(circle at 80% 20%, rgba(255,255,255,0.05) 0%, transparent 50%);
    }

    .min-vh-75 {
        min-height: 75vh;
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    .hero-title {
        font-size: 3.5rem;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 1.5rem;
    }

    .text-gradient {
        background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-subtitle {
        font-size: 1.25rem;
        margin-bottom: 2rem;
        opacity: 0.9;
        line-height: 1.6;
    }

    .hero-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-hero-primary {
        background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        color: var(--primary-color);
        border: none;
        padding: 1rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
    }

    .btn-hero-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(255, 215, 0, 0.4);
        color: var(--primary-color);
    }

    .btn-hero-secondary {
        background: transparent;
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
        padding: 1rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .btn-hero-secondary:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.6);
        color: white;
        transform: translateY(-3px);
    }

    .hero-visual {
        position: relative;
        height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero-illustration {
        opacity: 0.3;
        animation: float 6s ease-in-out infinite;
    }

    .floating-elements {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }

    .floating-card {
        position: absolute;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 15px;
        padding: 1rem;
        text-align: center;
        animation: floatCard 8s ease-in-out infinite;
    }

    .floating-card i {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        display: block;
    }

    .floating-card span {
        font-size: 0.9rem;
        font-weight: 600;
    }

    .book-card-1 {
        top: 10%;
        left: 10%;
        animation-delay: 0s;
    }

    .book-card-2 {
        top: 60%;
        right: 15%;
        animation-delay: 2s;
    }

    .book-card-3 {
        bottom: 20%;
        left: 20%;
        animation-delay: 4s;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }

    @keyframes floatCard {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        25% { transform: translateY(-10px) rotate(2deg); }
        50% { transform: translateY(-20px) rotate(-2deg); }
        75% { transform: translateY(-10px) rotate(1deg); }
    }

    .features-section {
        background: #f8f9fa;
    }

    .stat-card {
        display: flex;
        align-items: center;
        background: white;
        padding: 1.5rem;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1.5rem;
        color: white;
    }

    .stat-icon.success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .stat-icon.warning {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    }

    .stat-icon.info {
        background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0;
        color: var(--primary-color);
    }

    .stat-label {
        margin-bottom: 0;
        color: #6c757d;
        font-weight: 500;
    }

    .feature-card {
        background: white;
        padding: 2rem;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        height: 100%;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    }

    .feature-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        font-size: 2rem;
        color: white;
    }

    .feature-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--primary-color);
    }

    .feature-description {
        color: #6c757d;
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }

    .feature-link {
        color: var(--secondary-color);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .feature-link:hover {
        color: var(--primary-color);
    }

    .cta-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        padding: 4rem 0;
    }

    .cta-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .cta-subtitle {
        font-size: 1.2rem;
        margin-bottom: 2rem;
        opacity: 0.9;
    }

    .cta-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-cta-primary {
        background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        color: var(--primary-color);
        border: none;
        padding: 1rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }

    .btn-cta-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(255, 215, 0, 0.4);
        color: var(--primary-color);
    }

    .btn-cta-secondary {
        background: transparent;
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
        padding: 1rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }

    .btn-cta-secondary:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.6);
        color: white;
        transform: translateY(-3px);
    }

    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.5rem;
        }
        
        .hero-subtitle {
            font-size: 1.1rem;
        }
        
        .hero-actions {
            flex-direction: column;
        }
        
        .btn-hero-primary,
        .btn-hero-secondary {
            width: 100%;
            text-align: center;
        }
        
        .cta-title {
            font-size: 2rem;
        }
        
        .cta-actions {
            flex-direction: column;
            align-items: center;
        }
        
        .btn-cta-primary,
        .btn-cta-secondary {
            width: 100%;
            max-width: 300px;
        }
    }
</style>
@endsection