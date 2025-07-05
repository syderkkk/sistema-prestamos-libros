@extends('layouts.app')

@section('title', 'Detalles del Libro')

@section('content')
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <i class="fas fa-book-reader me-2"></i>Sistema de Préstamos
        </a>
        
        <div class="navbar-nav ms-auto">
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
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
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-book me-2"></i>{{ $libro['TITULO'] }}</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('libros.index') }}">Libros</a></li>
                            <li class="breadcrumb-item active">Detalles</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('libros.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Volver
                    </a>
                    @if($usuario['rol'] === 'Bibliotecario')
                        <a href="{{ route('libros.edit', $libro['ID']) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Editar
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Libro</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4"><strong>Título:</strong></div>
                        <div class="col-sm-8">{{ $libro['TITULO'] }}</div>
                    </div>
                    <hr>
                    
                    @if(!empty($libro['ISBN']))
                        <div class="row">
                            <div class="col-sm-4"><strong>ISBN:</strong></div>
                            <div class="col-sm-8">{{ $libro['ISBN'] }}</div>
                        </div>
                        <hr>
                    @endif
                    
                    <div class="row">
                        <div class="col-sm-4"><strong>Autor:</strong></div>
                        <div class="col-sm-8">{{ $libro['AUTOR_NOMBRE'] }} {{ $libro['AUTOR_APELLIDO'] }}</div>
                    </div>
                    <hr>
                    
                    <div class="row">
                        <div class="col-sm-4"><strong>Categoría:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge bg-info">{{ $libro['CATEGORIA_NOMBRE'] }}</span>
                        </div>
                    </div>
                    <hr>
                    
                    @if(!empty($libro['EDITORIAL']))
                        <div class="row">
                            <div class="col-sm-4"><strong>Editorial:</strong></div>
                            <div class="col-sm-8">{{ $libro['EDITORIAL'] }}</div>
                        </div>
                        <hr>
                    @endif
                    
                    @if(!empty($libro['FECHA_PUBLICACION']))
                        <div class="row">
                            <div class="col-sm-4"><strong>Fecha de Publicación:</strong></div>
                            <div class="col-sm-8">{{ \Carbon\Carbon::parse($libro['FECHA_PUBLICACION'])->format('d/m/Y') }}</div>
                        </div>
                        <hr>
                    @endif
                    
                    @if(!empty($libro['NUMERO_PAGINAS']))
                        <div class="row">
                            <div class="col-sm-4"><strong>Número de Páginas:</strong></div>
                            <div class="col-sm-8">{{ $libro['NUMERO_PAGINAS'] }}</div>
                        </div>
                        <hr>
                    @endif
                    
                    <div class="row">
                        <div class="col-sm-4"><strong>Idioma:</strong></div>
                        <div class="col-sm-8">{{ $libro['IDIOMA'] ?? 'No especificado' }}</div>
                    </div>
                    <hr>
                    
                    @if(!empty($libro['UBICACION']))
                        <div class="row">
                            <div class="col-sm-4"><strong>Ubicación:</strong></div>
                            <div class="col-sm-8">{{ $libro['UBICACION'] }}</div>
                        </div>
                        <hr>
                    @endif
                    
                    @if(!empty($libro['RESUMEN']))
                        <div class="row">
                            <div class="col-sm-4"><strong>Resumen:</strong></div>
                            <div class="col-sm-8">{{ $libro['RESUMEN'] }}</div>
                        </div>
                        <hr>
                    @endif
                    
                    <div class="row">
                        <div class="col-sm-4"><strong>Fecha de Registro:</strong></div>
                        <div class="col-sm-8">{{ \Carbon\Carbon::parse($libro['FECHA_CREACION'])->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Disponibilidad</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($libro['CANTIDAD_DISPONIBLE'] > 0)
                            <span class="badge bg-success fs-6">Disponible</span>
                        @else
                            <span class="badge bg-danger fs-6">No Disponible</span>
                        @endif
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-success">{{ $libro['CANTIDAD_DISPONIBLE'] }}</h4>
                                <small class="text-muted">Disponibles</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-primary">{{ $libro['CANTIDAD_TOTAL'] }}</h4>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                    
                    @if($libro['CANTIDAD_TOTAL'] > $libro['CANTIDAD_DISPONIBLE'])
                        <div class="mt-3">
                            <small class="text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                {{ $libro['CANTIDAD_TOTAL'] - $libro['CANTIDAD_DISPONIBLE'] }} en préstamo
                            </small>
                        </div>
                    @endif
                    
                    @if($libro['CANTIDAD_DISPONIBLE'] > 0)
                        <div class="mt-3">
                            <button class="btn btn-success btn-sm" disabled>
                                <i class="fas fa-hand-paper me-2"></i>Solicitar Préstamo
                            </button>
                            <small class="d-block text-muted mt-1">Funcionalidad próximamente</small>
                        </div>
                    @endif
                </div>
            </div>
            
            @if($usuario['rol'] === 'Bibliotecario')
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Acciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('libros.edit', $libro['ID']) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Editar Libro
                            </a>
                            <button type="button" class="btn btn-danger" 
                                    onclick="confirmarEliminacion({{ $libro['ID'] }}, '{{ $libro['TITULO'] }}')">
                                <i class="fas fa-trash me-2"></i>Eliminar Libro
                            </button>
                        </div>
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