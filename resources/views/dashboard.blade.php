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
                <div class="kpi-label">Plazas Disponibles</div>
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

    <!-- Tabla de Últimas Entradas -->
    <div class="row mt-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i> Últimos Ingresos Registrados</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Placa</th>
                                    <th>Propietario</th>
                                    <th>Hora y Fecha de Entrada</th>
                                    <th>Módulo</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ultimosIngresos as $ingreso)
                                <tr>
                                    <td><strong class="text-dark">{{ $ingreso->placa }}</strong></td>
                                    <td>{{ $ingreso->vehiculo->cliente->user->nombre ?? 'Cliente General' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($ingreso->fecha_hora_entrada)->format('h:i A - d/m/Y') }}</td>
                                    <td><span class="badge bg-warning">Módulo {{ $ingreso->modulo->ubicacion ?? 'N/A' }}</span></td>
                                    <td><span class="badge bg-success">{{ $ingreso->estado }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No hay vehículos estacionados en este momento.</td>
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
