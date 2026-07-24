@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-users"></i> Gestión de Personal y Usuarios</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarUsuario">
            <i class="fas fa-user-plus me-1"></i> Registrar Usuario
        </button>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar Usuario o Nombre</label>
                    <input type="text" class="form-control" placeholder="Nombre, cédula o correo...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filtrar por Rol</label>
                    <select class="form-select">
                        <option selected>Todos los roles</option>
                        <option>ADMINISTRADOR</option>
                        <option>OPERADOR</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-dark w-100"><i class="fas fa-search me-1"></i> Filtrar Personal</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-id-badge me-2"></i> Usuarios del Sistema</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Nombre Completo</th>
                            <th>Cédula</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($usuarios as $usuario)
                        <tr>
                            <td><strong class="text-dark">{{ $usuario->usuario }}</strong></td>
                            <td>{{ $usuario->correo }}</td>
                            <td>{{ $usuario->nombre_completo }}</td>
                            <td>{{ $usuario->cedula }}</td>
                            <td>{{ $usuario->telefono ?? '-' }}</td>
                            <td><span class="badge {{ $usuario->nombre_rol === 'Administrador' ? 'bg-warning' : 'bg-dark' }}">{{ $usuario->nombre_rol }}</span></td>
                            <td><span class="badge bg-success">Activo</span></td>
                            <td>{{ $usuario->personal_created_at ? \Carbon\Carbon::parse($usuario->personal_created_at)->format('d/m/Y') : 'N/A' }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-info me-1" onclick="verUsuario({{ $usuario->id_personal }})"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-sm btn-warning me-1" onclick="editarUsuario({{ $usuario->id_personal }})"><i class="fas fa-edit"></i></button>
                                <form action="{{ route('usuarios.destroy', $usuario->id_personal) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este usuario?')"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No hay usuarios registrados en el sistema</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Usuario -->
    <div class="modal fade" id="modalAgregarUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i> Crear Cuenta de Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('usuarios.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Número de Cédula</label>
                            <input type="text" name="cedula" class="form-control" placeholder="Ej: 1234567890" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Nombres y apellidos" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" placeholder="usuario@parkingsure.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono de Contacto</label>
                            <input type="text" name="telefono" class="form-control" placeholder="Ej: 0991234567">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre de Usuario (Login)</label>
                            <input type="text" name="usuario" class="form-control" placeholder="ej: admin.juan" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Asignación de Rol</label>
                            <select name="id_rol" class="form-select" required>
                                <option value="">Seleccione un rol...</option>
                                <option value="1">Administrador</option>
                                <option value="2">Operador</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Registrar Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Usuario -->
    <div class="modal fade" id="modalEditarUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i> Modificar Datos de Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditarUsuario" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Cédula</label>
                            <input type="text" name="cedula" id="edit_cedula" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" id="edit_telefono" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" name="usuario" id="edit_usuario" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <select name="id_rol" id="edit_id_rol" class="form-select" required>
                                <option value="">Seleccione un rol...</option>
                                <option value="1">Administrador</option>
                                <option value="2">Operador</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editarUsuario(id) {
            fetch('{{ route('usuarios.edit', ':id') }}'.replace(':id', id))
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id_personal;
                    document.getElementById('edit_cedula').value = data.cedula;
                    document.getElementById('edit_nombre').value = data.nombre_completo;
                    document.getElementById('edit_email').value = data.correo;
                    document.getElementById('edit_telefono').value = data.telefono || '';
                    document.getElementById('edit_usuario').value = data.usuario;
                    document.getElementById('edit_id_rol').value = data.id_rol;
                    
                    document.getElementById('formEditarUsuario').action = '{{ route('usuarios.update', ':id') }}'.replace(':id', id);
                    
                    var modal = new bootstrap.Modal(document.getElementById('modalEditarUsuario'));
                    modal.show();
                })
                .catch(error => console.error('Error:', error));
        }

        function verUsuario(id) {
            alert('Consultando información detallada del usuario ID: ' + id);
        }
    </script>
@endsection
