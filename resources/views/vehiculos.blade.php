@extends('layouts.app')

@section('title', 'Vehículos')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-car"></i> Gestión de Vehículos</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarVehiculo">
            <i class="fas fa-plus-circle me-1"></i> Registrar Vehículo
        </button>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('vehiculos') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Buscar por Placa o Propietario</label>
                        <input type="text" name="buscar" class="form-control" value="{{ $buscar ?? '' }}" placeholder="Ej: ABC-123 o Juan...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado de Permanencia</label>
                        <select name="estado" class="form-select">
                            <option {{ ($estadoFiltro ?? '') === 'Todos los estados' ? 'selected' : '' }}>Todos los estados</option>
                            <option value="Adentro" {{ ($estadoFiltro ?? '') === 'Adentro' ? 'selected' : '' }}>Adentro</option>
                            <option value="Afuera" {{ ($estadoFiltro ?? '') === 'Afuera' ? 'selected' : '' }}>Afuera</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-dark w-100"><i class="fas fa-search me-1"></i> Buscar Vehículos</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Vehículos -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i> Listado de Vehículos Registrados</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Placa</th>
                            <th>Propietario</th>
                            <th>Marca / Modelo</th>
                            <th>Tipo / Color</th>
                            <th>Estado</th>
                            <th>Último Acceso</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vehiculos as $v)
                        @php
                            $ultimoIngreso = $v->entradas->first();
                            $estaAdentro = $ultimoIngreso && $ultimoIngreso->estado === 'ACTIVO';
                        @endphp
                        <tr>
                            <td><strong class="text-dark">{{ $v->placa }}</strong></td>
                            <td>{{ $v->cliente->user->nombre ?? 'Cliente General' }}</td>
                            <td>{{ $v->marca }} {{ $v->modelo }}</td>
                            <td><span class="badge bg-dark">{{ $v->color ?? 'Sedan' }}</span></td>
                            <td>
                                @if($estaAdentro)
                                    <span class="badge bg-success">Adentro (Plaza {{ $ultimoIngreso->modulo->ubicacion ?? 'N/A' }})</span>
                                @else
                                    <span class="badge bg-warning">Afuera</span>
                                @endif
                            </td>
                            <td>{{ $ultimoIngreso ? \Carbon\Carbon::parse($ultimoIngreso->fecha_hora_entrada)->format('h:i A - d/m/Y') : 'Sin registros' }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-warning me-1" 
                                        onclick="editarVehiculo('{{ $v->placa }}', '{{ addslashes($v->cliente->user->nombre ?? '') }}', '{{ addslashes($v->marca) }}', '{{ addslashes($v->modelo) }}', '{{ addslashes($v->color ?? '') }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('vehiculos.destroy', $v->placa) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar el vehículo {{ $v->placa }}?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No se encontraron vehículos registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Vehículo -->
    <div class="modal fade" id="modalAgregarVehiculo" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-car me-2"></i> Registrar Nuevo Vehículo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('vehiculos.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Placa del Vehículo</label>
                            <input type="text" name="placa" class="form-control" placeholder="Ej: ABC-123" required style="text-transform: uppercase;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre del Propietario</label>
                            <input type="text" name="propietario" class="form-control" placeholder="Nombre completo" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Marca y Modelo</label>
                            <input type="text" name="marca_modelo" class="form-control" placeholder="Ej: Toyota Corolla" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo / Categoría</label>
                            <select name="tipo" class="form-select">
                                <option value="Sedan">Sedan</option>
                                <option value="SUV">SUV</option>
                                <option value="Camioneta">Camioneta</option>
                                <option value="Motocicleta">Motocicleta</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Vehículo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Vehículo -->
    <div class="modal fade" id="modalEditarVehiculo" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Modificar Datos de Vehículo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditarVehiculo" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Placa (No modificable)</label>
                            <input type="text" id="edit_placa" class="form-control" readonly disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Propietario</label>
                            <input type="text" name="propietario" id="edit_propietario" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Marca</label>
                            <input type="text" name="marca" id="edit_marca" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Modelo</label>
                            <input type="text" name="modelo" id="edit_modelo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo / Categoría</label>
                            <input type="text" name="color" id="edit_color" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editarVehiculo(placa, propietario, marca, modelo, color) {
            document.getElementById('edit_placa').value = placa;
            document.getElementById('edit_propietario').value = propietario;
            document.getElementById('edit_marca').value = marca;
            document.getElementById('edit_modelo').value = modelo;
            document.getElementById('edit_color').value = color;

            document.getElementById('formEditarVehiculo').action = '{{ route('vehiculos.update', ':placa') }}'.replace(':placa', placa);

            var modal = new bootstrap.Modal(document.getElementById('modalEditarVehiculo'));
            modal.show();
        }
    </script>
@endsection
