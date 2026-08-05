@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header">
        <h1><i class="fas fa-chart-line"></i> Dashboard Principal</h1>
    </div>

    <div class="row">
        <!-- Tarjeta de Parqueaderos Disponibles -->
        <div class="col-md-3 mb-4">
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-parking"></i></div>
                <div class="kpi-value">{{ $disponibles }}</div>
                <div class="kpi-label">Módulos Disponibles</div>
            </div>
        </div>

        <!-- Tarjeta de Vehículos Estacionados -->
        <div class="col-md-3 mb-4">
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-car"></i></div>
                <div class="kpi-value">{{ $estacionados }}</div>
                <div class="kpi-label">Vehículos Activos</div>
            </div>
        </div>

        <!-- Tarjeta de Ingresos Hoy -->
        <div class="col-md-3 mb-4">
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-dollar-sign"></i></div>
                <div class="kpi-value">${{ number_format($ingresosHoy, 2) }}</div>
                <div class="kpi-label">Recaudación Hoy</div>
            </div>
        </div>

        <!-- Tarjeta de Personal Activo (Solo Admin) -->
        @php
            $user = auth()->user();
            $userWithRole = \App\Models\Personal::join('rol', 'personal.id_rol', '=', 'rol.id_rol')
                ->where('personal.id_personal', $user->id_personal)
                ->select('personal.*', 'rol.nombre_rol')
                ->first();
            $userRole = $userWithRole ? $userWithRole->nombre_rol : null;
        @endphp
        @if($userRole === 'Administrador')
            <div class="col-md-3 mb-4">
                <div class="kpi-card">
                    <div class="kpi-icon"><i class="fas fa-users"></i></div>
                    <div class="kpi-value">{{ $personalActivo }}</div>
                    <div class="kpi-label">Personal Activo</div>
                </div>
            </div>
        @endif
    </div>

    <!-- Tablas de Actividad Reciente -->
    <div class="row mt-2">
        <!-- Últimas Entradas (Vehículos Actualmente Estacionados) -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-sign-in-alt me-2 text-success"></i> Vehículos Estacionados</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-sm">
                            <thead>
                                <tr>
                                    <th>Placa</th>
                                    <th>Propietario</th>
                                    <th>Entrada</th>
                                    <th>Módulo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ultimosIngresos as $ingreso)
                                <tr>
                                    <td><strong class="text-dark">{{ $ingreso->placa }}</strong></td>
                                    <td class="small">{{ $ingreso->vehiculo->cliente->user->nombre ?? 'Cliente General' }}</td>
                                    <td class="small">{{ \Carbon\Carbon::parse($ingreso->fecha_hora_entrada)->format('H:i') }}</td>
                                    <td><span class="badge bg-success badge-sm">{{ $ingreso->modulo->ubicacion ?? 'N/A' }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted small">No hay vehículos estacionados</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimos Servicios Completados -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-check-circle me-2 text-primary"></i> Últimos Servicios Completados</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-sm">
                            <thead>
                                <tr>
                                    <th>Placa</th>
                                    <th>Cliente</th>
                                    <th>Servicio</th>
                                    <th>Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ultimosServicios as $servicio)
                                @php
                                    $factura = $servicio->salida?->factura;
                                @endphp
                                <tr>
                                    <td><strong class="text-dark">{{ $servicio->placa }}</strong></td>
                                    <td class="small">{{ $servicio->vehiculo->cliente->user->nombre ?? 'Cliente General' }}</td>
                                    <td><span class="badge bg-info badge-sm">{{ $servicio->tipoServicio->nombre_tipo_servicio ?? 'N/A' }}</span></td>
                                    <td class="small">
                                        @if($factura)
                                            <strong class="text-success">${{ number_format($factura->monto_total, 2) }}</strong>
                                        @else
                                            <span class="text-muted">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted small">No hay servicios recientes</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
