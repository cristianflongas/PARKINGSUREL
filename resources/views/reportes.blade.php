@extends('layouts.app')

@section('title', 'Reportes')

@section('content')
<style>
    /* Corporate Report Styles - Gold/Dark Theme */
    .report-card { border: 1px solid #dfe7f1; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; }
    .report-card .card-header { background: #111827; color: #ffffff; border-bottom: none; font-weight: 600; padding: 12px 20px; border-top: 3px solid #d7a93a; }
    .report-card .card-header i { color: #d7a93a; }
    
    .kpi-box { background: #ffffff; border: 1px solid #dfe7f1; border-radius: 8px; padding: 20px; text-align: center; transition: all 0.2s; border-bottom: 4px solid #d7a93a; }
    .kpi-box:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
    .kpi-box.dark { border-bottom-color: #111827; }
    .kpi-box.green { border-bottom-color: #2e8b57; }
    .kpi-box.blue { border-bottom-color: #3d6ecf; }
    
    .kpi-value { font-size: 2.2rem; font-weight: 900; color: #102033; margin-bottom: 5px; }
    .kpi-badge { font-size: 0.75rem; padding: 4px 10px; border-radius: 12px; font-weight: 600; background: #f9f1d8; color: #8a5a00; }
    .kpi-box.dark .kpi-badge { background: #eef3f9; color: #102033; }
    .kpi-box.green .kpi-badge { background: #eafaf1; color: #1d5d3b; }
    
    .stat-row { display: flex; align-items: center; padding: 15px; border-radius: 8px; margin-bottom: 12px; border: 1px solid #dfe7f1; }
    .stat-row .icon-box { display: flex; align-items: center; justify-content: center; width: 48px; height: 48px; border-radius: 12px; margin-right: 15px; }
    
    .table-corporate { font-size: 13px; }
    .table-corporate thead th { background-color: #f4f7fb; color: #102033; font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; border-bottom: 2px solid #dfe7f1; padding: 12px; }
    .table-corporate tbody td { padding: 12px; vertical-align: middle; color: #5f6f82; }
    .table-corporate tbody tr:hover { background-color: #f9f1d8; }
    .table-corporate tfoot td { background-color: #f4f7fb; font-weight: 700; color: #102033; }
</style>

    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark mb-0"><i class="fas fa-chart-bar text-primary me-2"></i> Análisis y Reportes Ejecutivo</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalGuardarReporte">
                <i class="fas fa-bookmark me-1"></i> Guardar Reporte
            </button>
            <button type="button" class="btn btn-primary" onclick="exportarReporteActual()" id="btnExportarPDF">
                <i class="fas fa-file-pdf me-1"></i> Descargar PDF Completo
            </button>
        </div>
    </div>

    {{-- Filtros de Reportes --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('reportes') }}" method="GET" id="formFiltros">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Filtrado Rápido</label>
                        <select name="filtro_rapido" class="form-select" onchange="actualizarFiltroRapido()">
                            <option value="">Personalizado</option>
                            <option value="hoy"            {{ $filtroRapido === 'hoy'            ? 'selected' : '' }}>Hoy</option>
                            <option value="ayer"           {{ $filtroRapido === 'ayer'           ? 'selected' : '' }}>Ayer</option>
                            <option value="esta_semana"    {{ $filtroRapido === 'esta_semana'    ? 'selected' : '' }}>Esta Semana</option>
                            <option value="semana_pasada"  {{ $filtroRapido === 'semana_pasada'  ? 'selected' : '' }}>Semana Pasada</option>
                            <option value="este_mes"       {{ $filtroRapido === 'este_mes'       ? 'selected' : '' }}>Este Mes</option>
                            <option value="mes_pasado"     {{ $filtroRapido === 'mes_pasado'     ? 'selected' : '' }}>Mes Pasado</option>
                            <option value="este_año"       {{ $filtroRapido === 'este_año'       ? 'selected' : '' }}>Este Año</option>
                            <option value="año_pasado"     {{ $filtroRapido === 'año_pasado'     ? 'selected' : '' }}>Año Pasado</option>
                            <option value="ultimos_7_dias" {{ $filtroRapido === 'ultimos_7_dias' ? 'selected' : '' }}>Últimos 7 Días</option>
                            <option value="ultimos_30_dias"{{ $filtroRapido === 'ultimos_30_dias'? 'selected' : '' }}>Últimos 30 Días</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tipo de Análisis</label>
                        <select name="tipo_reporte" class="form-select">
                            <option value="INGRESOS"  {{ $tipoReporte === 'INGRESOS'  ? 'selected' : '' }}>Ingresos</option>
                            <option value="OCUPACION" {{ $tipoReporte === 'OCUPACION' ? 'selected' : '' }}>Ocupación</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Fecha Desde</label>
                        <input type="date" name="fecha_inicio" id="fechaInicio" class="form-control" value="{{ $fechaInicio }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Fecha Hasta</label>
                        <input type="date" name="fecha_fin" id="fechaFin" class="form-control" value="{{ $fechaFin }}" required>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-dark flex-fill">
                            <i class="fas fa-filter me-1"></i> Generar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Resumen del Período --}}
    <div class="card report-card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i> {{ $resumen['titulo'] ?? 'Resumen del Período' }}</h5>
        </div>
        <div class="card-body py-4 bg-white">
            <div class="row g-4">
                @if($tipoReporte === 'INGRESOS')
                <div class="col-md-4">
                    <div class="kpi-box">
                        <div class="kpi-value">${{ number_format($resumen['total'] ?? 0, 2) }}</div>
                        <div class="kpi-badge">Total Recaudado</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="kpi-box dark">
                        <div class="kpi-value">{{ $resumen['cantidad'] ?? 0 }}</div>
                        <div class="kpi-badge">Total Comprobantes</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="kpi-box green">
                        <div class="kpi-value">${{ number_format($resumen['promedio'] ?? 0, 2) }}</div>
                        <div class="kpi-badge">Promedio por Ticket</div>
                    </div>
                </div>
                @elseif($tipoReporte === 'OCUPACION')
                <div class="col-md-4">
                    <div class="kpi-box">
                        <div class="kpi-value">{{ $resumen['total_entradas'] ?? 0 }}</div>
                        <div class="kpi-badge">Total Entradas</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="kpi-box dark">
                        <div class="kpi-value">{{ $resumen['total_salidas'] ?? 0 }}</div>
                        <div class="kpi-badge">Total Salidas</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="kpi-box green">
                        <div class="kpi-value">{{ $resumen['activos'] ?? 0 }}</div>
                        <div class="kpi-badge">Vehículos en Parqueadero</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Gráficas Duales --}}
    <div class="row mb-4 g-4">
        <div class="col-md-6">
            <div class="card report-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white"><i class="fas fa-chart-bar me-2"></i> {{ $datosGraficaBarras['titulo'] ?? 'Análisis Temporal' }}</h5>
                    <span class="badge" style="background:#d7a93a; color:#111827;">Barras</span>
                </div>
                <div class="card-body bg-white">
                    <canvas id="chartBarras" style="max-height:300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card report-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white"><i class="fas fa-chart-pie me-2"></i> {{ $datosGraficaTorta['titulo'] ?? 'Distribución' }}</h5>
                    <span class="badge" style="background:#ffffff; color:#111827;">Torta</span>
                </div>
                <div class="card-body bg-white">
                    <canvas id="chartTorta" style="max-height:300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- KPIs del Período --}}
    <div class="row mb-4 g-4">
        <div class="col-md-8">
            <div class="card report-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i>
                        Análisis del Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                    </h5>
                    <small class="text-light opacity-75">{{ $metricas['dias_periodo'] }} días analizados</small>
                </div>
                <div class="card-body bg-white">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="stat-row bg-light">
                                <div class="icon-box" style="background: #fff7e8; color: #d28a00;"><i class="fas fa-dollar-sign fa-lg"></i></div>
                                <div>
                                    <h4 class="mb-1 text-dark fw-bold">${{ number_format($metricas['ingresos_periodo'] ?? 0, 2) }}</h4>
                                    <p class="mb-0 small text-muted">Ingresos del Período</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-row bg-light">
                                <div class="icon-box" style="background: #edf4ff; color: #3d6ecf;"><i class="fas fa-chart-line fa-lg"></i></div>
                                <div>
                                    <h4 class="mb-1 text-dark fw-bold">{{ $metricas['servicios_periodo'] ?? 0 }}</h4>
                                    <p class="mb-0 small text-muted">Servicios Prestados</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-row bg-light">
                                <div class="icon-box" style="background: #eafaf1; color: #2e8b57;"><i class="fas fa-clock fa-lg"></i></div>
                                <div>
                                    <h4 class="mb-1 text-dark fw-bold">{{ $metricas['promedio_estancia'] ?? 0 }} min</h4>
                                    <p class="mb-0 small text-muted">Estancia Promedio</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-row bg-light">
                                <div class="icon-box" style="background: #eef3f9; color: #102033;"><i class="fas fa-parking fa-lg"></i></div>
                                <div>
                                    <h4 class="mb-1 text-dark fw-bold">{{ $metricas['modulo_mas_usado'] ?? 'N/A' }}</h4>
                                    <p class="mb-0 small text-muted">Módulo Más Utilizado</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card report-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-wifi me-2"></i> Estado Actual (Tiempo Real)</h6>
                </div>
                <div class="card-body bg-white">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fas fa-car text-success me-2"></i>Vehículos Estacionados:</span>
                        <span class="fw-bold text-dark">{{ $metricas['vehiculos_adentro'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fas fa-square-parking text-primary me-2"></i>Módulos Disponibles:</span>
                        <span class="fw-bold text-dark">{{ $metricas['modulos_disponibles'] ?? 0 }}</span>
                    </div>
                    @if(($metricas['modulos_mantenimiento'] ?? 0) > 0)
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted"><i class="fas fa-tools text-warning me-2"></i>En Mantenimiento:</span>
                        <span class="fw-bold text-dark">{{ $metricas['modulos_mantenimiento'] }}</span>
                    </div>
                    @endif
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted fw-bold">Ocupación Total</small>
                            <small class="fw-bold">{{ $metricas['ocupacion_porcentaje'] ?? 0 }}%</small>
                        </div>
                        <div class="progress" style="height:6px; background-color: #eef3f9;">
                            <div class="progress-bar" style="background-color: #d7a93a; width:{{ $metricas['ocupacion_porcentaje'] ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card report-card mt-4">
                <div class="card-body bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold">Variación de Ingresos</span>
                        <span class="badge {{ ($metricas['cambio_ingresos'] ?? 0) >= 0 ? 'bg-success' : 'bg-danger' }}">
                            {{ ($metricas['cambio_ingresos'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($metricas['cambio_ingresos'] ?? 0, 1) }}%
                        </span>
                    </div>
                    <div class="progress" style="height:6px; background-color: #e2e8f0;">
                        <div class="progress-bar {{ ($metricas['cambio_ingresos'] ?? 0) >= 0 ? 'bg-success' : 'bg-danger' }}" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================== --}}
    {{-- ANÁLISIS ADICIONAL (Top servicios, módulos, horas pico)         --}}
    {{-- ============================================================== --}}
    <div class="row mb-4 g-4">
        {{-- Top Servicios --}}
        <div class="col-md-4">
            <div class="card report-card h-100">
                <div class="card-header">
                    <h6 class="mb-0 text-white"><i class="fas fa-star me-2"></i> Top Servicios Más Usados</h6>
                </div>
                <div class="card-body bg-white">
                    @php
                        $maxServ = $analisisAdicional['top_servicios']->max('total') ?: 1;
                    @endphp
                    @forelse($analisisAdicional['top_servicios'] as $idx => $servicio)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-bold text-dark">
                                <span class="badge" style="background:#111827; color:#fff;">{{ $idx + 1 }}</span>
                                {{ $servicio->nombre_tipo_servicio }}
                            </span>
                            <span class="small text-muted fw-bold">{{ $servicio->total }} usos</span>
                        </div>
                        <div class="progress" style="height:6px; background-color: #eef3f9;">
                            <div class="progress-bar"
                                 style="background-color: #d7a93a; width:{{ round(($servicio->total / $maxServ) * 100) }}%">
                            </div>
                        </div>
                    </div>
                    @empty
                        <p class="text-muted text-center small mb-0">Sin datos en este período</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Top Módulos --}}
        <div class="col-md-4">
            <div class="card report-card h-100">
                <div class="card-header">
                    <h6 class="mb-0 text-white"><i class="fas fa-parking me-2"></i> Módulos Más Utilizados</h6>
                </div>
                <div class="card-body bg-white">
                    @php
                        $maxMod = $analisisAdicional['top_modulos']->max('total') ?: 1;
                    @endphp
                    @forelse($analisisAdicional['top_modulos'] as $idx => $modulo)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-bold text-dark">
                                <span class="badge" style="background:#111827; color:#fff;">{{ $idx + 1 }}</span>
                                {{ $modulo->ubicacion }}
                            </span>
                            <span class="small text-muted fw-bold">{{ $modulo->total }} entradas</span>
                        </div>
                        <div class="progress" style="height:6px; background-color: #eef3f9;">
                            <div class="progress-bar"
                                 style="background-color: #3d6ecf; width:{{ round(($modulo->total / $maxMod) * 100) }}%">
                            </div>
                        </div>
                    </div>
                    @empty
                        <p class="text-muted text-center small mb-0">Sin datos en este período</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Horas Pico --}}
        <div class="col-md-4">
            <div class="card report-card h-100">
                <div class="card-header">
                    <h6 class="mb-0 text-white"><i class="fas fa-clock me-2"></i> Horas Pico de Afluencia</h6>
                </div>
                <div class="card-body bg-white">
                    @php
                        $maxHora = $analisisAdicional['horas_pico']->max('total') ?: 1;
                    @endphp
                    @forelse($analisisAdicional['horas_pico'] as $idx => $hora)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-bold text-dark">
                                <i class="fas fa-clock me-1 text-muted" style="font-size:10px;"></i>
                                {{ $hora->hora_formateada }}
                            </span>
                            <span class="small text-muted fw-bold">{{ $hora->total }} entradas</span>
                        </div>
                        <div class="progress" style="height:6px; background-color: #eef3f9;">
                            <div class="progress-bar"
                                 style="background-color: #2e8b57; width:{{ round(($hora->total / $maxHora) * 100) }}%">
                            </div>
                        </div>
                    </div>
                    @empty
                        <p class="text-muted text-center small mb-0">Sin datos en este período</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================== --}}
    {{-- TABLA DE REGISTROS DETALLADOS                                   --}}
    {{-- ============================================================== --}}
    <div class="card report-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-table me-2"></i>
                Registros Detallados del Período
                <span class="badge ms-2" style="background: rgba(255,255,255,0.2);">{{ $registrosDetallados->total() }} registros totales</span>
            </h5>
            <div class="d-flex gap-2 align-items-center">
                <small class="text-light opacity-75">
                    Pág. {{ $registrosDetallados->currentPage() }} de {{ $registrosDetallados->lastPage() }}
                </small>
                <button class="btn btn-sm btn-light text-dark fw-bold" onclick="exportarReporteActual()" title="El PDF incluye TODOS los registros">
                    <i class="fas fa-file-pdf text-danger me-1"></i> Descargar PDF Completo
                </button>
            </div>
        </div>

        @if($tipoReporte === 'INGRESOS')
        <div class="table-responsive">
            <table class="table table-corporate mb-0">
                <thead>
                    <tr>
                        <th>N° Factura</th>
                        <th>Fecha / Hora</th>
                        <th>Placa</th>
                        <th>Propietario</th>
                        <th>Tipo Vehículo</th>
                        <th>Servicio</th>
                        <th>Módulo</th>
                        <th>Estancia</th>
                        <th class="text-end">Monto</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrosDetallados as $registro)
                    <tr>
                        <td><code class="text-dark fw-bold">#{{ str_pad($registro->id_factura, 5, '0', STR_PAD_LEFT) }}</code></td>
                        <td>{{ $registro->fecha_emision ? \Carbon\Carbon::parse($registro->fecha_emision)->format('d/m/Y H:i') : '—' }}</td>
                        <td><span class="badge" style="background:#111827;">{{ $registro->placa ?? '—' }}</span></td>
                        <td class="fw-bold">{{ $registro->nombre_propietario ?? '<span class="text-muted fst-italic fw-normal">Sin registro</span>' }}</td>
                        <td>{{ $registro->tipo_vehiculo ?? '—' }}</td>
                        <td><span class="badge" style="background:#3d6ecf;">{{ $registro->nombre_tipo_servicio ?? '—' }}</span></td>
                        <td>{{ $registro->modulo ?? '—' }}</td>
                        <td class="text-center">
                            @if($registro->minutos_estancia)
                                @php
                                    $h = floor($registro->minutos_estancia / 60);
                                    $m = $registro->minutos_estancia % 60;
                                @endphp
                                <small class="fw-bold">{{ $h > 0 ? $h.'h ' : '' }}{{ $m }}m</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold text-success">${{ number_format($registro->monto_total ?? 0, 2) }}</td>
                        <td>
                            @if($registro->estado_pago === 'PAGADO')
                                <span class="badge bg-success">PAGADO</span>
                            @else
                                <span class="badge bg-danger">{{ $registro->estado_pago ?? 'N/A' }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
                            <h5 class="fw-bold">No hay registros de ingresos en este período</h5>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($registrosDetallados->count() > 0)
                <tfoot>
                    <tr>
                        <td colspan="8">SUBTOTAL (pág. {{ $registrosDetallados->currentPage() }})</td>
                        <td class="text-end text-dark" style="font-size: 1.1em;">
                            ${{ number_format($registrosDetallados->sum('monto_total'), 2) }}
                        </td>

                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @elseif($tipoReporte === 'OCUPACION')
        <div class="table-responsive">
            <table class="table table-corporate mb-0">
                <thead>
                    <tr>
                        <th>ID Entrada</th>
                        <th>Fecha Entrada</th>
                        <th>Fecha Salida</th>
                        <th>Placa</th>
                        <th>Propietario</th>
                        <th>Servicio</th>
                        <th>Módulo</th>
                        <th class="text-center">Estancia</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrosDetallados as $registro)
                    <tr>
                        <td><code class="text-dark fw-bold">#{{ str_pad($registro->id_entrada, 5, '0', STR_PAD_LEFT) }}</code></td>
                        <td>{{ $registro->fecha_hora_entrada ? \Carbon\Carbon::parse($registro->fecha_hora_entrada)->format('d/m/Y H:i') : '—' }}</td>
                        <td>
                            @if($registro->fecha_hora_salida)
                                {{ \Carbon\Carbon::parse($registro->fecha_hora_salida)->format('d/m/Y H:i') }}
                            @else
                                <span class="badge" style="background:#d7a93a; color:#111827;">En curso</span>
                            @endif
                        </td>
                        <td><span class="badge" style="background:#111827;">{{ $registro->placa ?? '—' }}</span></td>
                        <td class="fw-bold">{{ $registro->nombre_propietario ?? '<span class="text-muted fst-italic fw-normal">Sin registro</span>' }}</td>
                        <td><span class="badge" style="background:#3d6ecf;">{{ $registro->nombre_tipo_servicio ?? '—' }}</span></td>
                        <td>{{ $registro->modulo ?? '—' }}</td>
                        <td class="text-center">
                            @if($registro->minutos_estancia)
                                @php
                                    $h = floor($registro->minutos_estancia / 60);
                                    $m = $registro->minutos_estancia % 60;
                                @endphp
                                <small class="fw-bold">{{ $h > 0 ? $h.'h ' : '' }}{{ $m }}m</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($registro->estado === 'ACTIVO')
                                <span class="badge bg-success">ACTIVO</span>
                            @else
                                <span class="badge" style="background:#5f6f82;">COMPLETADO</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
                            <h5 class="fw-bold">No hay registros de ocupación en este período</h5>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($registrosDetallados->count() > 0)
                <tfoot>
                    <tr>
                        <td colspan="8">TOTAL EN ESTA PÁGINA</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        @endif

        {{-- Paginación --}}
        @if($registrosDetallados->hasPages())
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Mostrando {{ $registrosDetallados->firstItem() }}–{{ $registrosDetallados->lastItem() }}
                de {{ $registrosDetallados->total() }} registros
                &nbsp;|&nbsp;
                <strong>El PDF descarga TODOS los {{ $registrosDetallados->total() }} registros</strong>
            </small>
            <div>
                {{ $registrosDetallados->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @else
        <div class="card-footer">
            <small class="text-muted">Mostrando todos los {{ $registrosDetallados->total() }} registros del período</small>
        </div>
        @endif
    </div>

    {{-- Modal Guardar Reporte --}}
    <div class="modal fade" id="modalGuardarReporte" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-save me-2"></i> Archivar Reporte Actual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('reportes.guardar') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Reporte</label>
                            <input type="text" name="nombre_reporte" class="form-control" placeholder="Ej: Consolidado Mensual Julio" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo de Reporte</label>
                            <select name="tipo_reporte" class="form-select" required>
                                <option value="INGRESOS"  {{ $tipoReporte === 'INGRESOS'  ? 'selected' : '' }}>Ingresos</option>
                                <option value="OCUPACION" {{ $tipoReporte === 'OCUPACION' ? 'selected' : '' }}>Ocupación</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control" value="{{ $fechaInicio }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control" value="{{ $fechaFin }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observaciones Ejecutivas</label>
                            <textarea name="observaciones" class="form-control" rows="3" placeholder="Comentarios ejecutivos sobre el período..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Reporte</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const coloresSistema = [
            '#ffd700', '#10b981', '#3b82f6', '#8b5cf6',
            '#ef4444', '#ec4899', '#06b6d4', '#0a0a0a'
        ];

        // Gráfica de Barras
        const ctxBarras  = document.getElementById('chartBarras').getContext('2d');
        const datosBarras = @json($datosGraficaBarras ?? []);

        let configBarras;
        if (datosBarras.datasets) {
            configBarras = {
                type: 'bar',
                data: { labels: datosBarras.labels || [], datasets: datosBarras.datasets || [] },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: true, position: 'top' } },
                    scales: {
                        y: { beginAtZero: true, stacked: true, grid: { color: '#e2e8f0' } },
                        x: { stacked: true, grid: { display: false } }
                    }
                }
            };
        } else {
            configBarras = {
                type: 'bar',
                data: {
                    labels: datosBarras.labels || [],
                    datasets: [{
                        label: datosBarras.titulo || 'Datos',
                        data: datosBarras.data || [],
                        backgroundColor: coloresSistema[0],
                        borderColor: '#e6c200',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#e2e8f0' } },
                        x: { grid: { display: false } }
                    }
                }
            };
        }
        new Chart(ctxBarras, configBarras);

        // Gráfica de Torta
        const ctxTorta  = document.getElementById('chartTorta').getContext('2d');
        const datosTorta = @json($datosGraficaTorta ?? []);
        new Chart(ctxTorta, {
            type: 'doughnut',
            data: {
                labels: datosTorta.labels || [],
                datasets: [{ label: datosTorta.titulo || 'Distribución', data: datosTorta.data || [], backgroundColor: coloresSistema, borderColor: '#ffffff', borderWidth: 2 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: true, position: 'right' } }
            }
        });

        // Filtro rápido
        function actualizarFiltroRapido() {
            const filtroRapido = document.querySelector('[name="filtro_rapido"]').value;
            if (filtroRapido) document.getElementById('formFiltros').submit();
        }

        // Exportar PDF completo
        function exportarReporteActual() {
            const btn = document.getElementById('btnExportarPDF');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generando PDF...';

            const tipoReporte = '{{ $tipoReporte }}';
            const fechaInicio = '{{ $fechaInicio }}';
            const fechaFin    = '{{ $fechaFin }}';

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('reportes.descargar-temporal') }}';
            form.target = '_blank';

            const fields = {
                '_token':       document.querySelector('meta[name="csrf-token"]').content,
                'tipo_reporte': tipoReporte,
                'fecha_inicio': fechaInicio,
                'fecha_fin':    fechaFin,
            };

            for (const [name, value] of Object.entries(fields)) {
                const inp = document.createElement('input');
                inp.type  = 'hidden';
                inp.name  = name;
                inp.value = value;
                form.appendChild(inp);
            }

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);

            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-file-pdf me-1"></i> Descargar PDF Completo';
            }, 4000);
        }

        function descargarReporteActual() { exportarReporteActual(); }
    </script>
@endsection
