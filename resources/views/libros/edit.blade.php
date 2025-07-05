@extends('layouts.app')

@section('title', 'Editar Libro')

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
                    <div>
                        <h2><i class="fas fa-edit me-2"></i>Editar Libro</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('libros.index') }}">Libros</a></li>
                                <li class="breadcrumb-item active">Editar</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="{{ route('libros.show', $libro['ID']) }}" class="btn btn-secondary me-2">
                            <i class="fas fa-eye me-2"></i>Ver
                        </a>
                        <a href="{{ route('libros.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver
                        </a>
                    </div>
                </div>

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-book me-2"></i>Información del Libro</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('libros.update', $libro['ID']) }}">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="titulo" class="form-label">Título <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="titulo" name="titulo"
                                            value="{{ old('titulo', $libro['TITULO']) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="isbn" class="form-label">ISBN</label>
                                        <input type="text" class="form-control" id="isbn" name="isbn"
                                            value="{{ old('isbn', $libro['ISBN']) }}" placeholder="978-84-123456-7-8">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="autor_id" class="form-label">Autor <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="autor_id" name="autor_id" required>
                                            <option value="">Seleccionar autor</option>
                                            @foreach ($autores as $autor)
                                                <option value="{{ $autor['ID'] }}"
                                                    {{ old('autor_id', $libro['AUTOR_ID']) == $autor['ID'] ? 'selected' : '' }}>
                                                    {{ $autor['NOMBRE'] }} {{ $autor['APELLIDO'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="categoria_id" class="form-label">Categoría <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="categoria_id" name="categoria_id" required>
                                            <option value="">Seleccionar categoría</option>
                                            @foreach ($categorias as $categoria)
                                                <option value="{{ $categoria['ID'] }}"
                                                    {{ old('categoria_id', $libro['CATEGORIA_ID']) == $categoria['ID'] ? 'selected' : '' }}>
                                                    {{ $categoria['NOMBRE'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editorial" class="form-label">Editorial</label>
                                        <input type="text" class="form-control" id="editorial" name="editorial"
                                            value="{{ old('editorial', $libro['EDITORIAL']) }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="fecha_publicacion" class="form-label">Fecha de Publicación</label>
                                        <input type="date" class="form-control" id="fecha_publicacion"
                                            name="fecha_publicacion"
                                            value="{{ old('fecha_publicacion', $libro['FECHA_PUBLICACION'] ? \Carbon\Carbon::parse($libro['FECHA_PUBLICACION'])->format('Y-m-d') : '') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="numero_paginas" class="form-label">Número de Páginas</label>
                                        <input type="number" class="form-control" id="numero_paginas"
                                            name="numero_paginas"
                                            value="{{ old('numero_paginas', $libro['NUMERO_PAGINAS']) }}" min="1">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="idioma" class="form-label">Idioma</label>
                                        <input type="text" class="form-control" id="idioma" name="idioma"
                                            value="{{ old('idioma', $libro['IDIOMA']) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="ubicacion" class="form-label">Ubicación</label>
                                        <input type="text" class="form-control" id="ubicacion" name="ubicacion"
                                            value="{{ old('ubicacion', $libro['UBICACION']) }}"
                                            placeholder="Ej: Estante A-1">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="cantidad_total" class="form-label">Cantidad Total <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="cantidad_total"
                                            name="cantidad_total"
                                            value="{{ old('cantidad_total', $libro['CANTIDAD_TOTAL']) }}" min="1"
                                            required>
                                        <div class="form-text">
                                            Actualmente disponibles: {{ $libro['CANTIDAD_DISPONIBLE'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="resumen" class="form-label">Resumen</label>
                                <textarea class="form-control" id="resumen" name="resumen" rows="4"
                                    placeholder="Breve descripción del contenido del libro">{{ old('resumen', $libro['RESUMEN']) }}</textarea>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary me-2" onclick="history.back()">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Actualizar Libro
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
