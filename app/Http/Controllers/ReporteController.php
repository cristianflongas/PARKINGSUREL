<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reporte;
use Illuminate\Support\Facades\DB;
use PDF;

class ReporteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Manejar filtrado rápido
        $filtroRapido = $request->input('filtro_rapido', '');
        
        if ($filtroRapido) {
            $fechas = $this->calcularRangoFechas($filtroRapido);
            $fechaInicio = $fechas['inicio'];
            $fechaFin = $fechas['fin'];
        } else {
            $fechaInicio = $request->input('fecha_inicio', now()->subDays(30)->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));
        }
        
        $tipoReporte = $request->input('tipo_reporte', 'INGRESOS');

        // Datos para gráficas (barras y torta)
        $datosGraficaBarras = $this->obtenerDatosGrafica($tipoReporte, $fechaInicio, $fechaFin, 'barras');
        $datosGraficaTorta = $this->obtenerDatosGrafica($tipoReporte, $fechaInicio, $fechaFin, 'torta');
        
        // Resumen de datos
        $resumen = $this->obtenerResumen($tipoReporte, $fechaInicio, $fechaFin);

        // Métricas según el período filtrado
        $metricas = $this->obtenerMetricasFiltradas($fechaInicio, $fechaFin);

        return view('reportes', [
            'active' => 'reportes',
            'datosGraficaBarras' => $datosGraficaBarras,
            'datosGraficaTorta' => $datosGraficaTorta,
            'resumen' => $resumen,
            'metricas' => $metricas,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'tipoReporte' => $tipoReporte,
            'filtroRapido' => $filtroRapido
        ]);
    }

    /**
     * Calcular rango de fechas según filtro rápido
     */
    private function calcularRangoFechas($filtroRapido)
    {
        $now = now();
        
        switch ($filtroRapido) {
            case 'hoy':
                return [
                    'inicio' => $now->format('Y-m-d'),
                    'fin' => $now->format('Y-m-d')
                ];
                
            case 'ayer':
                return [
                    'inicio' => $now->subDay()->format('Y-m-d'),
                    'fin' => $now->format('Y-m-d')
                ];
                
            case 'esta_semana':
                return [
                    'inicio' => $now->startOfWeek()->format('Y-m-d'),
                    'fin' => $now->endOfWeek()->format('Y-m-d')
                ];
                
            case 'semana_pasada':
                return [
                    'inicio' => $now->subWeek()->startOfWeek()->format('Y-m-d'),
                    'fin' => $now->subWeek()->endOfWeek()->format('Y-m-d')
                ];
                
            case 'este_mes':
                return [
                    'inicio' => $now->startOfMonth()->format('Y-m-d'),
                    'fin' => $now->endOfMonth()->format('Y-m-d')
                ];
                
            case 'mes_pasado':
                return [
                    'inicio' => $now->subMonth()->startOfMonth()->format('Y-m-d'),
                    'fin' => $now->subMonth()->endOfMonth()->format('Y-m-d')
                ];
                
            case 'este_año':
                return [
                    'inicio' => $now->startOfYear()->format('Y-m-d'),
                    'fin' => $now->endOfYear()->format('Y-m-d')
                ];
                
            case 'año_pasado':
                return [
                    'inicio' => $now->subYear()->startOfYear()->format('Y-m-d'),
                    'fin' => $now->subYear()->endOfYear()->format('Y-m-d')
                ];
                
            case 'ultimos_7_dias':
                return [
                    'inicio' => $now->subDays(7)->format('Y-m-d'),
                    'fin' => $now->format('Y-m-d')
                ];
                
            case 'ultimos_30_dias':
                return [
                    'inicio' => $now->subDays(30)->format('Y-m-d'),
                    'fin' => $now->format('Y-m-d')
                ];
                
            default:
                return [
                    'inicio' => $now->subDays(30)->format('Y-m-d'),
                    'fin' => $now->format('Y-m-d')
                ];
        }
    }

    /**
     * Obtener datos para la gráfica según el tipo de reporte y visualización
     */
    private function obtenerDatosGrafica($tipoReporte, $fechaInicio, $fechaFin, $tipoGrafica = 'barras')
    {
        switch ($tipoReporte) {
            case 'INGRESOS':
                return $this->obtenerDatosIngresos($fechaInicio, $fechaFin, $tipoGrafica);
            case 'OCUPACION':
                return $this->obtenerDatosOcupacion($fechaInicio, $fechaFin, $tipoGrafica);
            default:
                return $this->obtenerDatosIngresos($fechaInicio, $fechaFin, $tipoGrafica);
        }
    }

    /**
     * Datos de ingresos por día
     */
    private function obtenerDatosIngresos($fechaInicio, $fechaFin, $tipoGrafica = 'barras')
    {
        if ($tipoGrafica === 'torta') {
            // Para gráfica de torta: ingresos por tipo de servicio
            $datos = DB::table('factura')
                ->join('salida', 'factura.id_salida', '=', 'salida.id_salida')
                ->join('entrada', 'salida.id_entrada', '=', 'entrada.id_entrada')
                ->join('tipo_servicio', 'entrada.id_tipo_servicio', '=', 'tipo_servicio.id_tipo_servicio')
                ->select(
                    'tipo_servicio.nombre_tipo_servicio as label',
                    DB::raw('SUM(factura.monto_total) as total')
                )
                ->whereBetween('factura.fecha_emision', [$fechaInicio, $fechaFin . ' 23:59:59'])
                ->where('factura.estado_pago', 'PAGADO')
                ->groupBy('tipo_servicio.nombre_tipo_servicio')
                ->orderBy('total', 'desc')
                ->get();
                
            return [
                'labels' => $datos->pluck('label'),
                'data' => $datos->pluck('total'),
                'titulo' => 'Ingresos por Tipo de Servicio'
            ];
        } else {
            // Para gráfica de barras: ingresos por día
            $datos = DB::table('factura')
                ->join('salida', 'factura.id_salida', '=', 'salida.id_salida')
                ->join('entrada', 'salida.id_entrada', '=', 'entrada.id_entrada')
                ->select(
                    DB::raw('DATE(factura.fecha_emision) as fecha'),
                    DB::raw('SUM(factura.monto_total) as total')
                )
                ->whereBetween('factura.fecha_emision', [$fechaInicio, $fechaFin . ' 23:59:59'])
                ->where('factura.estado_pago', 'PAGADO')
                ->groupBy(DB::raw('DATE(factura.fecha_emision)'))
                ->orderBy('fecha')
                ->get();

            return [
                'labels' => $datos->pluck('fecha')->map(function($date) {
                    return \Carbon\Carbon::parse($date)->format('d/m/Y');
                }),
                'data' => $datos->pluck('total'),
                'titulo' => 'Ingresos por Día'
            ];
        }
    }

    /**
     * Datos de ocupación por día
     */
    private function obtenerDatosOcupacion($fechaInicio, $fechaFin, $tipoGrafica = 'barras')
    {
        if ($tipoGrafica === 'torta') {
            // Para gráfica de torta: ocupación por módulo
            $datos = DB::table('entrada')
                ->join('modulo', 'entrada.id_modulo', '=', 'modulo.id_modulo')
                ->select(
                    'modulo.ubicacion as label',
                    DB::raw('COUNT(*) as total')
                )
                ->whereBetween('entrada.fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
                ->groupBy('modulo.ubicacion')
                ->orderBy('total', 'desc')
                ->get();
                
            return [
                'labels' => $datos->pluck('label'),
                'data' => $datos->pluck('total'),
                'titulo' => 'Ocupación por Módulo'
            ];
        } else {
            // Para gráfica de barras: ocupación por día
            $datos = DB::table('entrada')
                ->select(
                    DB::raw('DATE(fecha_hora_entrada) as fecha'),
                    DB::raw('COUNT(*) as total')
                )
                ->whereBetween('fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
                ->groupBy(DB::raw('DATE(fecha_hora_entrada)'))
                ->orderBy('fecha')
                ->get();

            return [
                'labels' => $datos->pluck('fecha')->map(function($date) {
                    return \Carbon\Carbon::parse($date)->format('d/m/Y');
                }),
                'data' => $datos->pluck('total'),
                'titulo' => 'Ocupación por Día'
            ];
        }
    }

    /**
     * Datos de servicios más utilizados
     * NOTA: Método desactivado - redundante con reporte de INGRESOS que ya muestra ingresos por servicio
     */
    /*
    private function obtenerDatosServicios($fechaInicio, $fechaFin, $tipoGrafica = 'barras')
    {
        if ($tipoGrafica === 'torta') {
            // Para gráfica de torta: distribución de servicios
            $datos = DB::table('entrada')
                ->join('tipo_servicio', 'entrada.id_tipo_servicio', '=', 'tipo_servicio.id_tipo_servicio')
                ->select(
                    'tipo_servicio.nombre_tipo_servicio as label',
                    DB::raw('COUNT(*) as total')
                )
                ->whereBetween('entrada.fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
                ->groupBy('tipo_servicio.nombre_tipo_servicio')
                ->orderBy('total', 'desc')
                ->get();
                
            return [
                'labels' => $datos->pluck('label'),
                'data' => $datos->pluck('total'),
                'titulo' => 'Distribución de Servicios'
            ];
        } else {
            // Para gráfica de barras: servicios por día
            $datos = DB::table('entrada')
                ->join('tipo_servicio', 'entrada.id_tipo_servicio', '=', 'tipo_servicio.id_tipo_servicio')
                ->select(
                    DB::raw('DATE(entrada.fecha_hora_entrada) as fecha'),
                    'tipo_servicio.nombre_tipo_servicio',
                    DB::raw('COUNT(*) as total')
                )
                ->whereBetween('entrada.fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
                ->groupBy(DB::raw('DATE(entrada.fecha_hora_entrada)'), 'tipo_servicio.nombre_tipo_servicio')
                ->orderBy('fecha')
                ->get();

            // Agrupar por fecha para mostrar servicios apilados
            $fechas = $datos->pluck('fecha')->unique()->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('d/m/Y');
            });
            
            $servicios = $datos->pluck('nombre_tipo_servicio')->unique();
            
            $datasets = [];
            $colors = ['#ffd700', '#10b981', '#3b82f6', '#ef4444', '#8b5cf6'];
            
            foreach ($servicios as $index => $servicio) {
                $dataServicio = [];
                foreach ($fechas as $fecha) {
                    $originalFecha = \Carbon\Carbon::createFromFormat('d/m/Y', $fecha)->format('Y-m-d');
                    $encontrado = $datos->where('fecha', $originalFecha)
                                       ->where('nombre_tipo_servicio', $servicio)
                                       ->first();
                    $dataServicio[] = $encontrado ? $encontrado->total : 0;
                }
                
                $datasets[] = [
                    'label' => $servicio,
                    'data' => $dataServicio,
                    'backgroundColor' => $colors[$index % count($colors)],
                    'borderColor' => $colors[$index % count($colors)],
                ];
            }

            return [
                'labels' => $fechas,
                'datasets' => $datasets,
                'titulo' => 'Servicios por Día'
            ];
        }
    }
    */

    /**
     * Obtener resumen de datos
     */
    private function obtenerResumen($tipoReporte, $fechaInicio, $fechaFin)
    {
        switch ($tipoReporte) {
            case 'INGRESOS':
                return $this->obtenerResumenIngresos($fechaInicio, $fechaFin);
            case 'OCUPACION':
                return $this->obtenerResumenOcupacion($fechaInicio, $fechaFin);
            default:
                return $this->obtenerResumenIngresos($fechaInicio, $fechaFin);
        }
    }

    /**
     * Resumen de ingresos
     */
    private function obtenerResumenIngresos($fechaInicio, $fechaFin)
    {
        $totalIngresos = DB::table('factura')
            ->whereBetween('fecha_emision', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->where('estado_pago', 'PAGADO')
            ->sum('monto_total');

        $totalFacturas = DB::table('factura')
            ->whereBetween('fecha_emision', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->count();

        $promedio = $totalFacturas > 0 ? $totalIngresos / $totalFacturas : 0;

        return [
            'total' => $totalIngresos,
            'cantidad' => $totalFacturas,
            'promedio' => $promedio,
            'titulo' => 'Resumen de Ingresos'
        ];
    }

    /**
     * Resumen de ocupación
     */
    private function obtenerResumenOcupacion($fechaInicio, $fechaFin)
    {
        $totalEntradas = DB::table('entrada')
            ->whereBetween('fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->count();

        $totalSalidas = DB::table('salida')
            ->join('entrada', 'salida.id_entrada', '=', 'entrada.id_entrada')
            ->whereBetween('salida.fecha_hora_salida', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->count();

        $activos = DB::table('entrada')
            ->where('estado', 'ACTIVO')
            ->count();

        return [
            'total_entradas' => $totalEntradas,
            'total_salidas' => $totalSalidas,
            'activos' => $activos,
            'titulo' => 'Resumen de Ocupación'
        ];
    }

    /**
     * Resumen de servicios
     * NOTA: Método desactivado - redundante con reporte de INGRESOS
     */
    /*
    private function obtenerResumenServicios($fechaInicio, $fechaFin)
    {
        $totalServicios = DB::table('entrada')
            ->whereBetween('fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->count();

        $servicioMasUsado = DB::table('entrada')
            ->join('tipo_servicio', 'entrada.id_tipo_servicio', '=', 'tipo_servicio.id_tipo_servicio')
            ->select('tipo_servicio.nombre_tipo_servicio', DB::raw('COUNT(*) as total'))
            ->whereBetween('entrada.fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->groupBy('tipo_servicio.nombre_tipo_servicio')
            ->orderBy('total', 'desc')
            ->first();

        return [
            'total_servicios' => $totalServicios,
            'servicio_mas_usado' => $servicioMasUsado ? $servicioMasUsado->nombre_tipo_servicio : 'N/A',
            'titulo' => 'Resumen de Servicios'
        ];
    }
    */

    /**
     * Guardar reporte
     */
    public function guardar(Request $request)
    {
        $request->validate([
            'nombre_reporte' => 'required|string|max:255',
            'tipo_reporte' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'observaciones' => 'nullable|string',
        ]);

        $datosGraficaBarras = $this->obtenerDatosGrafica($request->tipo_reporte, $request->fecha_inicio, $request->fecha_fin, 'barras');
        $datosGraficaTorta = $this->obtenerDatosGrafica($request->tipo_reporte, $request->fecha_inicio, $request->fecha_fin, 'torta');
        $resumen = $this->obtenerResumen($request->tipo_reporte, $request->fecha_inicio, $request->fecha_fin);
        $metricas = $this->obtenerMetricasFiltradas($request->fecha_inicio, $request->fecha_fin);

        $reporte = Reporte::create([
            'nombre_reporte' => $request->nombre_reporte,
            'tipo_reporte' => $request->tipo_reporte,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'total_recaudado' => $metricas['ingresos_periodo'] ?? 0,
            'contenido' => [
                'grafica_barras' => $datosGraficaBarras,
                'grafica_torta' => $datosGraficaTorta,
                'resumen' => $resumen,
                'metricas' => $metricas
            ],
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->route('reportes')->with('success', 'Reporte guardado correctamente');
    }

    /**
     * Descargar reporte en PDF
     */
    public function descargarPDF($id)
    {
        $reporte = Reporte::findOrFail($id);
        
        // Generar HTML para el PDF con diseño empresarial
        $html = $this->generarHTMLEmpresarial($reporte);
        
        // Generar PDF usando DomPDF
        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('reporte_' . $reporte->id_reporte . '.pdf');
    }

    /**
     * Generar HTML empresarial para el reporte
     */
    private function generarHTMLEmpresarial($reporte)
    {
        $contenido = $reporte->contenido ?? [];
        $resumen = $contenido['resumen'] ?? [];
        $metricas = $contenido['metricas'] ?? [];
        $graficaBarras = $contenido['grafica_barras'] ?? [];
        $graficaTorta = $contenido['grafica_torta'] ?? [];

        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$reporte->nombre_reporte}</title>
            <style>
                body { font-family: 'Arial', sans-serif; margin: 0; padding: 20px; background-color: #f8f9fa; color: #333; }
                .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .header { text-align: center; border-bottom: 3px solid #ffd700; padding-bottom: 20px; margin-bottom: 30px; }
                .logo { font-size: 28px; font-weight: bold; color: #0a0a0a; margin-bottom: 5px; }
                .subtitle { color: #666; font-size: 14px; }
                .report-title { font-size: 24px; color: #0a0a0a; margin: 20px 0; text-align: center; }
                .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
                .info-box { background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #ffd700; }
                .info-label { font-weight: bold; color: #333; font-size: 12px; text-transform: uppercase; }
                .info-value { font-size: 16px; color: #0a0a0a; margin-top: 5px; }
                .metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0; }
                .metric-card { background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); padding: 20px; border-radius: 8px; text-align: center; color: #0a0a0a; }
                .metric-value { font-size: 28px; font-weight: bold; margin-bottom: 5px; }
                .metric-label { font-size: 12px; text-transform: uppercase; opacity: 0.8; }
                .section { margin: 30px 0; padding: 20px; border: 1px solid #e9ecef; border-radius: 5px; }
                .section-title { font-size: 18px; color: #0a0a0a; margin-bottom: 15px; border-bottom: 2px solid #ffd700; padding-bottom: 5px; }
                .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                .data-table th { background: #0a0a0a; color: white; padding: 12px; text-align: left; font-size: 12px; text-transform: uppercase; }
                .data-table td { padding: 10px 12px; border-bottom: 1px solid #dee2e6; }
                .data-table tr:nth-child(even) { background: #f8f9fa; }
                .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #dee2e6; font-size: 12px; color: #666; }
                .observations { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #10b981; }
                @media print { body { background: white; } .container { box-shadow: none; } }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <div class='logo'>PARKING SUREL</div>
                    <div class='subtitle'>Sistema de Gestión de Parqueaderos</div>
                </div>
                
                <h1 class='report-title'>{$reporte->nombre_reporte}</h1>
                
                <div class='info-grid'>
                    <div class='info-box'>
                        <div class='info-label'>Tipo de Reporte</div>
                        <div class='info-value'>{$reporte->tipo_reporte}</div>
                    </div>
                    <div class='info-box'>
                        <div class='info-label'>Período de Análisis</div>
                        <div class='info-value'>" . 
                        \Carbon\Carbon::parse($reporte->fecha_inicio)->format('d/m/Y') . 
                        " - " . 
                        \Carbon\Carbon::parse($reporte->fecha_fin)->format('d/m/Y') . 
                        "</div>
                    </div>
                </div>";

        // Métricas según el tipo de reporte
        if (!empty($metricas)) {
            $html .= "
                <div class='metrics'>
                    <div class='metric-card'>
                        <div class='metric-value'>$" . number_format($metricas['ingresos_periodo'] ?? 0, 2) . "</div>
                        <div class='metric-label'>Ingresos del Período</div>
                    </div>
                    <div class='metric-card'>
                        <div class='metric-value'>" . ($metricas['servicios_periodo'] ?? 0) . "</div>
                        <div class='metric-label'>Servicios Prestados</div>
                    </div>
                    <div class='metric-card'>
                        <div class='metric-value'>" . ($metricas['promedio_estancia'] ?? 0) . " min</div>
                        <div class='metric-label'>Estancia Promedio</div>
                    </div>
                </div>";
        } elseif ($reporte->tipo_reporte === 'INGRESOS') {
            $html .= "
                <div class='metrics'>
                    <div class='metric-card'>
                        <div class='metric-value'>$" . number_format($resumen['total'] ?? 0, 2) . "</div>
                        <div class='metric-label'>Total Recaudado</div>
                    </div>
                    <div class='metric-card'>
                        <div class='metric-value'>" . ($resumen['cantidad'] ?? 0) . "</div>
                        <div class='metric-label'>Facturas Emitidas</div>
                    </div>
                    <div class='metric-card'>
                        <div class='metric-value'>$" . number_format($resumen['promedio'] ?? 0, 2) . "</div>
                        <div class='metric-label'>Promedio por Ticket</div>
                    </div>
                </div>";
        }

        // Datos de gráficas
        if (!empty($graficaBarras['labels']) && !empty($graficaBarras['data'])) {
            $html .= "
                <div class='section'>
                    <div class='section-title'>Análisis Temporal - {$graficaBarras['titulo']}</div>
                    <table class='data-table'>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>";
            
            foreach ($graficaBarras['labels'] as $index => $label) {
                $value = $graficaBarras['data'][$index] ?? 0;
                $html .= "<tr><td>{$label}</td><td>" . 
                        ($reporte->tipo_reporte === 'INGRESOS' ? '$' . number_format($value, 2) : $value) . 
                        "</td></tr>";
            }
            
            $html .= "
                        </tbody>
                    </table>
                </div>";
        }

        if (!empty($graficaTorta['labels']) && !empty($graficaTorta['data'])) {
            $html .= "
                <div class='section'>
                    <div class='section-title'>Distribución por Categorías - {$graficaTorta['titulo']}</div>
                    <table class='data-table'>
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Valor</th>
                                <th>Porcentaje</th>
                            </tr>
                        </thead>
                        <tbody>";
            
            $total = array_sum($graficaTorta['data']->toArray());
            foreach ($graficaTorta['labels'] as $index => $label) {
                $value = $graficaTorta['data'][$index] ?? 0;
                $porcentaje = $total > 0 ? ($value / $total) * 100 : 0;
                $html .= "<tr>
                    <td>{$label}</td>
                    <td>" . ($reporte->tipo_reporte === 'INGRESOS' ? '$' . number_format($value, 2) : $value) . "</td>
                    <td>" . number_format($porcentaje, 1) . "%</td>
                </tr>";
            }
            
            $html .= "
                        </tbody>
                    </table>
                </div>";
        }

        // Observaciones
        if ($reporte->observaciones) {
            $html .= "
                <div class='observations'>
                    <div class='section-title'>Observaciones Ejecutivas</div>
                    <p>{$reporte->observaciones}</p>
                </div>";
        }

        $html .= "
                <div class='footer'>
                    <p>Reporte generado el " . now()->format('d/m/Y H:i:s') . " | Sistema PARKING SUREL</p>
                    <p>© " . date('Y') . " Parking Surel. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>";

        return $html;
    }

    /**
     * Descargar reporte en Excel
     */
    public function descargarExcel($id)
    {
        $reporte = Reporte::findOrFail($id);
        
        // Generar CSV simple
        $csv = "Reporte: {$reporte->nombre_reporte}\n";
        $csv .= "Tipo: {$reporte->tipo_reporte}\n";
        $csv .= "Fecha Inicio: {$reporte->fecha_inicio}\n";
        $csv .= "Fecha Fin: {$reporte->fecha_fin}\n";
        $csv .= "Total Recaudado: {$reporte->total_recaudado}\n\n";
        
        if ($reporte->contenido) {
            $csv .= "Datos del Reporte:\n";
            foreach ($reporte->contenido as $key => $value) {
                if (is_array($value)) {
                    $csv .= "$key: " . json_encode($value) . "\n";
                } else {
                    $csv .= "$key: $value\n";
                }
            }
        }
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="reporte_' . $reporte->id_reporte . '.csv"');
    }

    /**
     * Obtener métricas según el período filtrado
     */
    private function obtenerMetricasFiltradas($fechaInicio, $fechaFin)
    {
        // Calcular período anterior para comparación
        $diasPeriodo = \Carbon\Carbon::parse($fechaInicio)->diffInDays(\Carbon\Carbon::parse($fechaFin));
        $fechaInicioAnterior = \Carbon\Carbon::parse($fechaInicio)->subDays($diasPeriodo + 1)->format('Y-m-d');
        $fechaFinAnterior = \Carbon\Carbon::parse($fechaInicio)->subDay()->format('Y-m-d');

        // Vehículos actualmente estacionados (siempre tiempo real)
        $vehiculosAdentro = DB::table('entrada')
            ->where('estado', 'ACTIVO')
            ->count();

        // Total de módulos y disponibles (siempre tiempo real)
        $totalModulos = DB::table('modulo')->count();
        $modulosMantenimiento = DB::table('modulo')
            ->where('estado', 'MANTENIMIENTO')
            ->count();
        $modulosDisponibles = $totalModulos - $vehiculosAdentro - $modulosMantenimiento;
        $ocupacionPorcentaje = $totalModulos > 0 ? ($vehiculosAdentro / $totalModulos) * 100 : 0;

        // Ingresos del período filtrado
        $ingresosPeriodo = DB::table('factura')
            ->whereBetween('fecha_emision', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->where('estado_pago', 'PAGADO')
            ->sum('monto_total');

        // Ingresos del período anterior (para comparación)
        $ingresosPeriodoAnterior = DB::table('factura')
            ->whereBetween('fecha_emision', [$fechaInicioAnterior, $fechaFinAnterior . ' 23:59:59'])
            ->where('estado_pago', 'PAGADO')
            ->sum('monto_total');

        $cambioIngresos = $ingresosPeriodoAnterior > 0 ? (($ingresosPeriodo - $ingresosPeriodoAnterior) / $ingresosPeriodoAnterior) * 100 : 0;

        // Servicios del período filtrado
        $serviciosPeriodo = DB::table('entrada')
            ->whereBetween('fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->count();

        // Facturas del período filtrado
        $facturasPeriodo = DB::table('factura')
            ->whereBetween('fecha_emision', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->where('estado_pago', 'PAGADO')
            ->count();

        // Tiempo promedio de estancia del período filtrado
        $promedioEstancia = DB::table('entrada')
            ->join('salida', 'entrada.id_entrada', '=', 'salida.id_entrada')
            ->whereBetween('entrada.fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, entrada.fecha_hora_entrada, salida.fecha_hora_salida)) as promedio')
            ->value('promedio') ?? 0;

        // Servicio más utilizado del período
        $servicioMasUsado = DB::table('entrada')
            ->join('tipo_servicio', 'entrada.id_tipo_servicio', '=', 'tipo_servicio.id_tipo_servicio')
            ->whereBetween('entrada.fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->select('tipo_servicio.nombre_tipo_servicio', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo_servicio.nombre_tipo_servicio')
            ->orderBy('total', 'desc')
            ->first();

        // Módulo más utilizado del período
        $moduloMasUsado = DB::table('entrada')
            ->join('modulo', 'entrada.id_modulo', '=', 'modulo.id_modulo')
            ->whereBetween('entrada.fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->select('modulo.ubicacion', DB::raw('COUNT(*) as total'))
            ->groupBy('modulo.ubicacion')
            ->orderBy('total', 'desc')
            ->first();

        return [
            'vehiculos_adentro' => $vehiculosAdentro,
            'total_modulos' => $totalModulos,
            'modulos_disponibles' => $modulosDisponibles,
            'modulos_mantenimiento' => $modulosMantenimiento,
            'ocupacion_porcentaje' => round($ocupacionPorcentaje, 1),
            'ingresos_periodo' => $ingresosPeriodo,
            'ingresos_periodo_anterior' => $ingresosPeriodoAnterior,
            'cambio_ingresos' => round($cambioIngresos, 1),
            'servicios_periodo' => $serviciosPeriodo,
            'facturas_periodo' => $facturasPeriodo,
            'promedio_estancia' => round($promedioEstancia, 0),
            'servicio_mas_usado' => $servicioMasUsado ? $servicioMasUsado->nombre_tipo_servicio : 'N/A',
            'modulo_mas_usado' => $moduloMasUsado ? $moduloMasUsado->ubicacion : 'N/A',
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'dias_periodo' => $diasPeriodo + 1
        ];
    }

    /**
     * Descargar reporte temporal (sin guardar en BD)
     */
    public function descargarTemporal(Request $request)
    {
        $tipoReporte = $request->tipo_reporte;
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $datosGraficaBarras = $this->obtenerDatosGrafica($tipoReporte, $fechaInicio, $fechaFin, 'barras');
        $datosGraficaTorta = $this->obtenerDatosGrafica($tipoReporte, $fechaInicio, $fechaFin, 'torta');
        $resumen = $this->obtenerResumen($tipoReporte, $fechaInicio, $fechaFin);
        $metricas = $this->obtenerMetricasFiltradas($fechaInicio, $fechaFin);

        // Crear objeto temporal para generar PDF
        $reporteTemp = (object) [
            'nombre_reporte' => "Reporte Temporal de {$tipoReporte}",
            'tipo_reporte' => $tipoReporte,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'total_recaudado' => $metricas['ingresos_periodo'] ?? 0,
            'contenido' => [
                'grafica_barras' => $datosGraficaBarras,
                'grafica_torta' => $datosGraficaTorta,
                'resumen' => $resumen,
                'metricas' => $metricas
            ],
            'observaciones' => "Reporte generado temporalmente el " . now()->format('d/m/Y H:i:s') . ". Período analizado: {$metricas['dias_periodo']} días.",
        ];

        $html = $this->generarHTMLEmpresarial($reporteTemp);
        
        // Generar PDF usando DomPDF
        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("reporte_temporal_{$tipoReporte}_{$fechaInicio}_{$fechaFin}.pdf");
    }

    /**
     * Eliminar reporte
     */
    public function destroy($id)
    {
        try {
            $reporte = Reporte::findOrFail($id);
            $reporte->delete();
            
            return redirect()->route('reportes')->with('success', 'Reporte eliminado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('reportes')->withErrors(['error' => 'Error al eliminar el reporte: ' . $e->getMessage()]);
        }
    }

    /**
     * Método de prueba para verificar consultas
     */
    public function test()
    {
        try {
            $fechaInicio = now()->subDays(30)->format('Y-m-d');
            $fechaFin = now()->format('Y-m-d');
            
            $metricas = $this->obtenerMetricasFiltradas($fechaInicio, $fechaFin);
            
            return response()->json([
                'status' => 'success',
                'metricas' => $metricas,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
        }
    }
    
}
