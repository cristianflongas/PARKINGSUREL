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
            <form action="{{ route('reportes') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Tipo de Análisis</label>
                        <select name="tipo_reporte" class="form-select">
                            <option value="INGRESOS" {{ $tipoReporte === 'INGRESOS' ? 'selected' : '' }}>Reporte de Ingresos</option>
                            <option value="OCUPACION" {{ $tipoReporte === 'OCUPACION' ? 'selected' : '' }}>Ocupación de Módulos</option>
                            <option value="SERVICIOS" {{ $tipoReporte === 'SERVICIOS' ? 'selected' : '' }}>Uso de Servicios</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Desde</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ $fechaInicio }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Hasta</label>
                        <input type="date" name="fecha_fin" class="form-control" value="{{ $fechaFin }}" required>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-dark flex-1"><i class="fas fa-filter me-1"></i> Filtrar</button>
                        <button type="button" class="btn btn-primary flex-1" onclick="descargarReporteActual()"><i class="fas fa-download me-1"></i> Exportar</button>
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

    <!-- Gráfica Principal -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i> {{ $datosGrafica['titulo'] ?? 'Representación Gráfica' }}</h5>
        </div>
        <div class="card-body">
            <canvas id="principalChart" style="max-height: 380px;"></canvas>
        </div>
    </div>

    <!-- Reportes Guardados -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-folder-open me-2"></i> Reportes Archivos y Guardados</h5>
        </div>
        <div class="card-body">
            @if($reportesGuardados->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nombre Reporte</th>
                            <th>Tipo</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Monto Total</th>
                            <th class="text-end">Exportar / Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportesGuardados as $reporte)
                        <tr>
                            <td><strong class="text-dark">{{ $reporte->nombre_reporte }}</strong></td>
                            <td><span class="badge bg-dark">{{ $reporte->tipo_reporte }}</span></td>
                            <td>{{ $reporte->fecha_inicio ? \Carbon\Carbon::parse($reporte->fecha_inicio)->format('d/m/Y') : 'N/A' }}</td>
                            <td>{{ $reporte->fecha_fin ? \Carbon\Carbon::parse($reporte->fecha_fin)->format('d/m/Y') : 'N/A' }}</td>
                            <td><strong class="text-dark">${{ number_format($reporte->total_recaudado, 2) }}</strong></td>
                            <td class="text-end">
                                <a href="{{ route('reportes.pdf', $reporte->id_reporte) }}" class="btn btn-sm btn-danger me-1" target="_blank">
                                    <i class="fas fa-file-pdf me-1"></i> PDF
                                </a>
                                <a href="{{ route('reportes.excel', $reporte->id_reporte) }}" class="btn btn-sm btn-success me-1">
                                    <i class="fas fa-file-excel me-1"></i> Excel
                                </a>
                                <form action="{{ route('reportes.destroy', $reporte->id_reporte) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este reporte?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-center text-muted py-4">No hay reportes archivados previamente en el sistema</p>
            @endif
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
                                <option value="SERVICIOS">Servicios</option>
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
        // Gráfica Principal con datos reales y paleta oficial (Amarillo, Negro, Verde, Gris)
        const ctx = document.getElementById('principalChart').getContext('2d');
        
        const labels = @json($datosGrafica['labels'] ?? []);
        const data = @json($datosGrafica['data'] ?? []);
        const titulo = '{{ $datosGrafica['titulo'] ?? 'Gráfica' }}';
        
        const chartType = '{{ $tipoReporte === 'SERVICIOS' ? 'doughnut' : ($tipoReporte === 'OCUPACION' ? 'line' : 'bar') }}';
        
        const colors = [
            '#ffd700', '#0a0a0a', '#10b981', '#3b82f6', '#ef4444', '#8b5cf6', '#ec4899'
        ];
        
        new Chart(ctx, {
            type: chartType,
            data: {
                labels: labels,
                datasets: [{
                    label: titulo,
                    data: data,
                    backgroundColor: chartType === 'line' ? 'rgba(255, 215, 0, 0.15)' : colors,
                    borderColor: chartType === 'line' ? '#ffd700' : colors,
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: chartType === 'line'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: chartType === 'doughnut',
                        position: 'right'
                    }
                },
                scales: chartType !== 'doughnut' ? {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e2e8f0'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                } : {}
            }
        });

        // Función para descargar reporte actual
        function descargarReporteActual() {
            const tipoReporte = '{{ $tipoReporte }}';
            const fechaInicio = '{{ $fechaInicio }}';
            const fechaFin = '{{ $fechaFin }}';
            
            fetch('/reportes/descargar-temporal', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    tipo_reporte: tipoReporte,
                    fecha_inicio: fechaInicio,
                    fecha_fin: fechaFin
                })
            })
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `reporte_${tipoReporte}_${fechaInicio}_${fechaFin}.csv`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
@endsection
