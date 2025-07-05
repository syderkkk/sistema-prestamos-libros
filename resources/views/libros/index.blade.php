@extends('layouts.app')

@section('title', 'Catálogo de Libros')

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
                        <h2><i class="fas fa-book me-2"></i>Catálogo de Libros</h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Libros</li>
                            </ol>
                        </nav>
                    </div>
                    @if ($usuario['rol'] === 'Bibliotecario')
                        <a href="{{ route('libros.create') }}" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Agregar Libro
                        </a>
                    @endif
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

        <!-- Filtros de búsqueda -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-search me-2"></i>Filtros de Búsqueda</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('libros.index') }}">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="titulo" class="form-label">Título</label>
                                    <input type="text" class="form-control" id="titulo" name="titulo"
                                        value="{{ $filtros['titulo'] ?? '' }}" placeholder="Buscar por título...">
                                </div>
                                <div class="col-md-4">
                                    <label for="autor" class="form-label">Autor</label>
                                    <input type="text" class="form-control" id="autor" name="autor"
                                        value="{{ $filtros['autor'] ?? '' }}" placeholder="Buscar por autor...">
                                </div>
                                <div class="col-md-4">
                                    <label for="categoria_id" class="form-label">Categoría</label>
                                    <select class="form-select" id="categoria_id" name="categoria_id">
                                        <option value="">Todas las categorías</option>
                                        @foreach ($categorias as $categoria)
                                            <option value="{{ $categoria['ID'] }}"
                                                {{ ($filtros['categoria_id'] ?? '') == $categoria['ID'] ? 'selected' : '' }}>
                                                {{ $categoria['NOMBRE'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Buscar
                                    </button>
                                    <a href="{{ route('libros.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Limpiar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de libros -->
        <div class="row">
            <div class="col-12">
                @if (count($libros) > 0)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>Libros Encontrados
                                <span class="badge bg-primary">{{ count($libros) }}</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Título</th>
                                            <th>Autor</th>
                                            <th>Categoría</th>
                                            <th>Editorial</th>
                                            <th>Disponibilidad</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($libros as $libro)
                                            <tr>
                                                <td>
                                                    <div>
                                                        <strong>{{ $libro['TITULO'] }}</strong>
                                                        @if (!empty($libro['ISBN']))
                                                            <br><small class="text-muted">ISBN:
                                                                {{ $libro['ISBN'] }}</small>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>{{ $libro['AUTOR_NOMBRE'] }}</td>
                                                <td>
                                                    <span class="badge bg-info">{{ $libro['CATEGORIA_NOMBRE'] }}</span>
                                                </td>
                                                <td>{{ $libro['EDITORIAL'] ?? 'No especificada' }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if ($libro['CANTIDAD_DISPONIBLE'] > 0)
                                                            <span class="badge bg-success me-2">Disponible</span>
                                                            <small class="text-muted">
                                                                {{ $libro['CANTIDAD_DISPONIBLE'] }}/{{ $libro['CANTIDAD_TOTAL'] }}
                                                            </small>
                                                        @else
                                                            <span class="badge bg-danger">No disponible</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('libros.show', $libro['ID']) }}"
                                                            class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        @if ($usuario['rol'] === 'Bibliotecario')
                                                            <a href="{{ route('libros.edit', $libro['ID']) }}"
                                                                class="btn btn-sm btn-outline-warning" title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                title="Eliminar"
                                                                onclick="confirmarEliminacion({{ $libro['ID'] }}, '{{ $libro['TITULO'] }}')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No se encontraron libros</h5>
                            <p class="text-muted">
                                @if (!empty($filtros['titulo']) || !empty($filtros['autor']) || !empty($filtros['categoria_id']))
                                    Intenta ajustar los filtros de búsqueda.
                                @else
                                    No hay libros registrados en el sistema.
                                @endif
                            </p>
                            @if ($usuario['rol'] === 'Bibliotecario')
                                <a href="{{ route('libros.create') }}" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Agregar Primer Libro
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal para confirmar eliminación -->
    <div class="modal fade" id="modalEliminar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar el libro <strong id="libroTitulo"></strong>?</p>
                    <p class="text-muted">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="formEliminar" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmarEliminacion(id, titulo) {
            document.getElementById('libroTitulo').textContent = titulo;
            document.getElementById('formEliminar').action = `/libros/${id}`;
            new bootstrap.Modal(document.getElementById('modalEliminar')).show();
        }
    </script>
@endsection
