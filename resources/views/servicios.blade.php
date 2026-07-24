@extends('layouts.app')

@section('title', 'Servicios')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-cogs"></i> Catálogo de Servicios y Tarifas</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarServicio">
            <i class="fas fa-plus-circle me-1"></i> Nuevo Servicio
        </button>
    </div>

    <!-- Tabla de Servicios -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i> Tipos de Servicios Configurados</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Servicio</th>
                            <th>Descripción</th>
                            <th>Tarifa Aplicada</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($servicios as $servicio)
                        <tr>
                            <td><strong class="text-dark">#{{ $servicio->id_tipo_servicio }}</strong></td>
                            <td><strong class="text-dark">{{ $servicio->nombre_tipo_servicio }}</strong></td>
                            <td>{{ $servicio->descripcion ?? 'Sin descripción' }}</td>
                            <td><strong class="text-dark">${{ number_format($servicio->tarifa, 2) }}</strong></td>
                            <td><span class="badge {{ $servicio->estado === 'ACTIVO' ? 'bg-success' : 'bg-danger' }}">{{ $servicio->estado }}</span></td>
                            <td>{{ $servicio->created_at ? \Carbon\Carbon::parse($servicio->created_at)->format('d/m/Y') : 'N/A' }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-warning me-1" onclick="editarServicio({{ $servicio->id_tipo_servicio }})"><i class="fas fa-edit"></i></button>
                                <form action="{{ route('servicios.destroy', $servicio->id_tipo_servicio) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este servicio?')"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No hay servicios registrados en la plataforma</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Servicio -->
    <div class="modal fade" id="modalAgregarServicio" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-cogs me-2"></i> Crear Tipo de Servicio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('servicios.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Servicio</label>
                            <input type="text" name="nombre_tipo_servicio" class="form-control" placeholder="Ej: Tarifa Hora Carro" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3" placeholder="Detalles de cobertura del servicio..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tarifa Base ($)</label>
                            <input type="number" name="tarifa" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estado Inicial</label>
                            <select name="estado" class="form-select" required>
                                <option value="ACTIVO">Activo</option>
                                <option value="INACTIVO">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Servicio</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Servicio -->
    <div class="modal fade" id="modalEditarServicio" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Modificar Servicio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditarServicio" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Servicio</label>
                            <input type="text" name="nombre_tipo_servicio" id="edit_nombre_tipo_servicio" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" id="edit_descripcion" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tarifa ($)</label>
                            <input type="number" name="tarifa" id="edit_tarifa" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select name="estado" id="edit_estado" class="form-select" required>
                                <option value="ACTIVO">Activo</option>
                                <option value="INACTIVO">Inactivo</option>
                            </select>
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
        function editarServicio(id) {
            fetch('{{ route('servicios.edit', ':id') }}'.replace(':id', id))
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id_tipo_servicio;
                    document.getElementById('edit_nombre_tipo_servicio').value = data.nombre_tipo_servicio;
                    document.getElementById('edit_descripcion').value = data.descripcion || '';
                    document.getElementById('edit_tarifa').value = data.tarifa;
                    document.getElementById('edit_estado').value = data.estado;
                    
                    document.getElementById('formEditarServicio').action = '{{ route('servicios.update', ':id') }}'.replace(':id', id);
                    
                    var modal = new bootstrap.Modal(document.getElementById('modalEditarServicio'));
                    modal.show();
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
@endsection
