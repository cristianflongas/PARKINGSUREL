@extends('layouts.app')

@section('title', 'Reportes')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-chart-bar"></i> Análisis y Reportes Ejecutivo</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGuardarReporte">
            <i class="fas fa-bookmark me-1"></i> Guardar Reporte
        </button>
    </div>

    <!-- Filtros de Reportes -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('reportes') }}" method="GET" id="formFiltros">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Filtrado Rápido</label>
                        <select name="filtro_rapido" class="form-select" onchange="actualizarFiltroRapido()">
                            <option value="">Personalizado</option>
                            <option value="hoy" {{ $filtroRapido === 'hoy' ? 'selected' : '' }}>Hoy</option>
                            <option value="ayer" {{ $filtroRapido === 'ayer' ? 'selected' : '' }}>Ayer</option>
                            <option value="esta_semana" {{ $filtroRapido === 'esta_semana' ? 'selected' : '' }}>Esta Semana</option>
                            <option value="semana_pasada" {{ $filtroRapido === 'semana_pasada' ? 'selected' : '' }}>Semana Pasada</option>
                            <option value="este_mes" {{ $filtroRapido === 'este_mes' ? 'selected' : '' }}>Este Mes</option>
                            <option value="mes_pasado" {{ $filtroRapido === 'mes_pasado' ? 'selected' : '' }}>Mes Pasado</option>
                            <option value="este_año" {{ $filtroRapido === 'este_año' ? 'selected' : '' }}>Este Año</option>
                            <option value="año_pasado" {{ $filtroRapido === 'año_pasado' ? 'selected' : '' }}>Año Pasado</option>
                            <option value="ultimos_7_dias" {{ $filtroRapido === 'ultimos_7_dias' ? 'selected' : '' }}>Últimos 7 Días</option>
                            <option value="ultimos_30_dias" {{ $filtroRapido === 'ultimos_30_dias' ? 'selected' : '' }}>Últimos 30 Días</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tipo de Análisis</label>
                        <select name="tipo_reporte" class="form-select">
                            <option value="INGRESOS" {{ $tipoReporte === 'INGRESOS' ? 'selected' : '' }}>Ingresos</option>
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
                        <button type="button" class="btn btn-primary flex-fill" onclick="exportarReporteActual()">
                            <i class="fas fa-download me-1"></i> PDF
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen del Período -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i> {{ $resumen['titulo'] ?? 'Resumen del Período' }}</h5>
        </div>
        <div class="card-body py-4">
            <div class="row">
                @if($tipoReporte === 'INGRESOS')
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="text-center p-3 border rounded-3 bg-light">
                        <h3 class="text-dark font-weight-bold mb-1" style="font-weight: 900; font-size: 2.2rem;">${{ number_format($resumen['total'] ?? 0, 2) }}</h3>
                        <span class="badge bg-warning">Total Recaudado</span>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="text-center p-3 border rounded-3 bg-light">
                        <h3 class="text-dark font-weight-bold mb-1" style="font-weight: 900; font-size: 2.2rem;">{{ $resumen['cantidad'] ?? 0 }}</h3>
                        <span class="badge bg-dark">Total Comprobantes</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 border rounded-3 bg-light">
                        <h3 class="text-dark font-weight-bold mb-1" style="font-weight: 900; font-size: 2.2rem;">${{ number_format($resumen['promedio'] ?? 0, 2) }}</h3>
                        <span class="badge bg-success">Promedio por Ticket</span>
                    </div>
                </div>
                @elseif($tipoReporte === 'OCUPACION')
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="text-center p-3 border rounded-3 bg-light">
                        <h3 class="text-dark font-weight-bold mb-1" style="font-weight: 900; font-size: 2.2rem;">{{ $resumen['total_entradas'] ?? 0 }}</h3>
                        <span class="badge bg-dark">Total Entradas</span>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="text-center p-3 border rounded-3 bg-light">
                        <h3 class="text-dark font-weight-bold mb-1" style="font-weight: 900; font-size: 2.2rem;">{{ $resumen['total_salidas'] ?? 0 }}</h3>
                        <span class="badge bg-success">Total Salidas</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 border rounded-3 bg-light">
                        <h3 class="text-dark font-weight-bold mb-1" style="font-weight: 900; font-size: 2.2rem;">{{ $resumen['activos'] ?? 0 }}</h3>
                        <span class="badge bg-warning">Vehículos en Parqueadero</span>
                    </div>
                </div>
                @elseif($tipoReporte === 'SERVICIOS')
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="text-center p-3 border rounded-3 bg-light">
                        <h3 class="text-dark font-weight-bold mb-1" style="font-weight: 900; font-size: 2.2rem;">{{ $resumen['total_servicios'] ?? 0 }}</h3>
                        <span class="badge bg-dark">Servicios Prestados</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-center p-3 border rounded-3 bg-light">
                        <h3 class="text-dark font-weight-bold mb-1" style="font-weight: 900; font-size: 2rem;">{{ $resumen['servicio_mas_usado'] ?? 'N/A' }}</h3>
                        <span class="badge bg-warning">Servicio Más Demandado</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Gráficas Duales -->
    <div class="row mb-4">
        <!-- Gráfica de Barras -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        {{ $datosGraficaBarras['titulo'] ?? 'Análisis Temporal' }}
                    </h5>
                    <span class="badge bg-primary">Barras</span>
                </div>
                <div class="card-body">
                    <canvas id="chartBarras" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfica de Torta -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        {{ $datosGraficaTorta['titulo'] ?? 'Distribución' }}
                    </h5>
                    <span class="badge bg-warning text-dark">Torta</span>
                </div>
                <div class="card-body">
                    <canvas id="chartTorta" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard de Métricas del Período Seleccionado -->
    <div class="row">
        <!-- KPIs del Período Filtrado -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-area me-2"></i> 
                        Análisis del Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                    </h5>
                    <small class="text-muted">{{ $metricas['dias_periodo'] }} días analizados</small>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-warning bg-opacity-10 rounded-3 border border-warning border-opacity-25">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-dollar-sign fa-2x text-warning"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="mb-1 text-warning">${{ number_format($metricas['ingresos_periodo'] ?? 0, 2) }}</h4>
                                    <p class="mb-0 small text-muted">Ingresos del Período</p>
                                    <span class="badge {{ ($metricas['cambio_ingresos'] ?? 0) >= 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ ($metricas['cambio_ingresos'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($metricas['cambio_ingresos'] ?? 0, 1) }}% vs período anterior
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-25">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-chart-line fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="mb-1 text-primary">{{ $metricas['servicios_periodo'] ?? 0 }}</h4>
                                    <p class="mb-0 small text-muted">Servicios Prestados</p>
                                    <span class="badge bg-primary">{{ $metricas['facturas_periodo'] ?? 0 }} facturas emitidas</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-success bg-opacity-10 rounded-3 border border-success border-opacity-25">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-clock fa-2x text-success"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="mb-1 text-success">{{ $metricas['promedio_estancia'] ?? 0 }} min</h4>
                                    <p class="mb-0 small text-muted">Estancia Promedio</p>
                                    <span class="badge bg-success">{{ $metricas['servicio_mas_usado'] ?? 'N/A' }} más usado</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-info bg-opacity-10 rounded-3 border border-info border-opacity-25">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-parking fa-2x text-info"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="mb-1 text-info">{{ $metricas['modulo_mas_usado'] ?? 'N/A' }}</h4>
                                    <p class="mb-0 small text-muted">Módulo Más Utilizado</p>
                                    <span class="badge bg-info">{{ $metricas['promedio_estancia'] ? round($metricas['servicios_periodo'] / $metricas['dias_periodo'], 1) : 0 }} servicios/día promedio</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado Actual del Parqueadero (Tiempo Real) -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-wifi me-2"></i> Estado Actual (Tiempo Real)</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3 p-2 bg-success bg-opacity-10 rounded">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas fa-car fa-lg text-success"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-0 text-success">{{ $metricas['vehiculos_adentro'] ?? 0 }}</h5>
                            <small class="text-muted">Vehículos Estacionados</small>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3 p-2 bg-primary bg-opacity-10 rounded">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas fa-square-parking fa-lg text-primary"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-0 text-primary">{{ $metricas['modulos_disponibles'] ?? 0 }}</h5>
                            <small class="text-muted">Módulos Disponibles</small>
                        </div>
                    </div>
                    
                    @if(($metricas['modulos_mantenimiento'] ?? 0) > 0)
                    <div class="d-flex align-items-center mb-3 p-2 bg-warning bg-opacity-10 rounded">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas fa-tools fa-lg text-warning"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-0 text-warning">{{ $metricas['modulos_mantenimiento'] }}</h5>
                            <small class="text-muted">En Mantenimiento</small>
                        </div>
                    </div>
                    @endif
                    
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $metricas['ocupacion_porcentaje'] ?? 0 }}%"></div>
                    </div>
                    <small class="text-muted">Ocupación: {{ $metricas['ocupacion_porcentaje'] ?? 0 }}%</small>
                </div>
            </div>
            
            <!-- Indicadores de Rendimiento -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Indicadores del Período</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Ingresos Período:</span>
                        <strong class="text-dark">${{ number_format($metricas['ingresos_periodo'] ?? 0, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Período Anterior:</span>
                        <strong class="text-dark">${{ number_format($metricas['ingresos_periodo_anterior'] ?? 0, 2) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Variación:</span>
                        <span class="badge {{ ($metricas['cambio_ingresos'] ?? 0) >= 0 ? 'bg-success' : 'bg-danger' }}">
                            {{ ($metricas['cambio_ingresos'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($metricas['cambio_ingresos'] ?? 0, 1) }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Guardar Reporte -->
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
                                <option value="INGRESOS">Ingresos</option>
                                <option value="OCUPACION">Ocupación</option>
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
                            <label class="form-label">Observaciones Adicionales</label>
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
        // Colores del sistema
        const coloresSistema = [
            '#ffd700', '#0a0a0a', '#10b981', '#3b82f6', 
            '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'
        ];

        // Gráfica de Barras
        const ctxBarras = document.getElementById('chartBarras').getContext('2d');
        const datosBarras = @json($datosGraficaBarras ?? []);
        
        let configBarras;
        if (datosBarras.datasets) {
            // Gráfica con múltiples datasets (servicios por día)
            configBarras = {
                type: 'bar',
                data: {
                    labels: datosBarras.labels || [],
                    datasets: datosBarras.datasets || []
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            stacked: true,
                            grid: { color: '#e2e8f0' }
                        },
                        x: {
                            stacked: true,
                            grid: { display: false }
                        }
                    }
                }
            };
        } else {
            // Gráfica simple
            configBarras = {
                type: 'bar',
                data: {
                    labels: datosBarras.labels || [],
                    datasets: [{
                        label: datosBarras.titulo || 'Datos',
                        data: datosBarras.data || [],
                        backgroundColor: coloresSistema[0],
                        borderColor: coloresSistema[1],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#e2e8f0' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            };
        }

        new Chart(ctxBarras, configBarras);

        // Gráfica de Torta
        const ctxTorta = document.getElementById('chartTorta').getContext('2d');
        const datosTorta = @json($datosGraficaTorta ?? []);
        
        new Chart(ctxTorta, {
            type: 'doughnut',
            data: {
                labels: datosTorta.labels || [],
                datasets: [{
                    label: datosTorta.titulo || 'Distribución',
                    data: datosTorta.data || [],
                    backgroundColor: coloresSistema,
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'right'
                    }
                }
            }
        });

        // Función para actualizar fechas según filtro rápido
        function actualizarFiltroRapido() {
            const filtroRapido = document.querySelector('[name="filtro_rapido"]').value;
            
            if (filtroRapido) {
                // Auto-submit el formulario cuando se selecciona un filtro rápido
                document.getElementById('formFiltros').submit();
            }
        }

        // Función para exportar reporte actual
        function exportarReporteActual() {
            const tipoReporte = '{{ $tipoReporte }}';
            const fechaInicio = '{{ $fechaInicio }}';
            const fechaFin = '{{ $fechaFin }}';
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('reportes.descargar-temporal') }}';
            form.target = '_blank';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrfToken);
            
            const inputTipo = document.createElement('input');
            inputTipo.type = 'hidden';
            inputTipo.name = 'tipo_reporte';
            inputTipo.value = tipoReporte;
            form.appendChild(inputTipo);
            
            const inputInicio = document.createElement('input');
            inputInicio.type = 'hidden';
            inputInicio.name = 'fecha_inicio';
            inputInicio.value = fechaInicio;
            form.appendChild(inputInicio);
            
            const inputFin = document.createElement('input');
            inputFin.type = 'hidden';
            inputFin.name = 'fecha_fin';
            inputFin.value = fechaFin;
            form.appendChild(inputFin);
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        // Mantener compatibilidad con función anterior
        function descargarReporteActual() {
            exportarReporteActual();
        }
    </script>
@endsection
