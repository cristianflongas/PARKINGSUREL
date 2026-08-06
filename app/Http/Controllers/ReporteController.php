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
        $datosGraficaTorta  = $this->obtenerDatosGrafica($tipoReporte, $fechaInicio, $fechaFin, 'torta');

        // Resumen de datos
        $resumen = $this->obtenerResumen($tipoReporte, $fechaInicio, $fechaFin);

        // Métricas según el período filtrado
        $metricas = $this->obtenerMetricasFiltradas($fechaInicio, $fechaFin);

        // Registros detallados para la tabla en la vista (paginados)
        $registrosDetallados = $this->obtenerRegistrosDetallados($tipoReporte, $fechaInicio, $fechaFin, 15);

        // Análisis adicional
        $analisisAdicional = $this->obtenerAnalisisAdicional($fechaInicio, $fechaFin);

        return view('reportes', [
            'active'              => 'reportes',
            'datosGraficaBarras'  => $datosGraficaBarras,
            'datosGraficaTorta'   => $datosGraficaTorta,
            'resumen'             => $resumen,
            'metricas'            => $metricas,
            'registrosDetallados' => $registrosDetallados,
            'analisisAdicional'   => $analisisAdicional,
            'fechaInicio'         => $fechaInicio,
            'fechaFin'            => $fechaFin,
            'tipoReporte'         => $tipoReporte,
            'filtroRapido'        => $filtroRapido,
        ]);
    }

    // =========================================================================
    // RANGOS DE FECHAS
    // =========================================================================

    private function calcularRangoFechas($filtroRapido)
    {
        $now = now();

        switch ($filtroRapido) {
            case 'hoy':
                return ['inicio' => $now->format('Y-m-d'), 'fin' => $now->format('Y-m-d')];
            case 'ayer':
                return ['inicio' => $now->subDay()->format('Y-m-d'), 'fin' => $now->format('Y-m-d')];
            case 'esta_semana':
                return ['inicio' => $now->startOfWeek()->format('Y-m-d'), 'fin' => $now->endOfWeek()->format('Y-m-d')];
            case 'semana_pasada':
                return ['inicio' => $now->subWeek()->startOfWeek()->format('Y-m-d'), 'fin' => $now->subWeek()->endOfWeek()->format('Y-m-d')];
            case 'este_mes':
                return ['inicio' => $now->startOfMonth()->format('Y-m-d'), 'fin' => $now->endOfMonth()->format('Y-m-d')];
            case 'mes_pasado':
                return ['inicio' => $now->subMonth()->startOfMonth()->format('Y-m-d'), 'fin' => $now->subMonth()->endOfMonth()->format('Y-m-d')];
            case 'este_año':
                return ['inicio' => $now->startOfYear()->format('Y-m-d'), 'fin' => $now->endOfYear()->format('Y-m-d')];
            case 'año_pasado':
                return ['inicio' => $now->subYear()->startOfYear()->format('Y-m-d'), 'fin' => $now->subYear()->endOfYear()->format('Y-m-d')];
            case 'ultimos_7_dias':
                return ['inicio' => $now->subDays(7)->format('Y-m-d'), 'fin' => $now->format('Y-m-d')];
            case 'ultimos_30_dias':
                return ['inicio' => $now->subDays(30)->format('Y-m-d'), 'fin' => $now->format('Y-m-d')];
            default:
                return ['inicio' => $now->subDays(30)->format('Y-m-d'), 'fin' => $now->format('Y-m-d')];
        }
    }

    // =========================================================================
    // DATOS DE GRÁFICAS
    // =========================================================================

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

    private function obtenerDatosIngresos($fechaInicio, $fechaFin, $tipoGrafica = 'barras')
    {
        if ($tipoGrafica === 'torta') {
            $datos = DB::table('factura')
                ->join('salida', 'factura.id_salida', '=', 'salida.id_salida')
                ->join('entrada', 'salida.id_entrada', '=', 'entrada.id_entrada')
                ->join('tipo_servicio', 'entrada.id_tipo_servicio', '=', 'tipo_servicio.id_tipo_servicio')
                ->select('tipo_servicio.nombre_tipo_servicio as label', DB::raw('SUM(factura.monto_total) as total'))
                ->whereBetween('factura.fecha_emision', [$fechaInicio, $fechaFin . ' 23:59:59'])
                ->where('factura.estado_pago', 'PAGADO')
                ->groupBy('tipo_servicio.nombre_tipo_servicio')
                ->orderBy('total', 'desc')
                ->get();

            return [
                'labels' => $datos->pluck('label'),
                'data'   => $datos->pluck('total'),
                'titulo' => 'Ingresos por Tipo de Servicio',
            ];
        } else {
            $datos = DB::table('factura')
                ->select(DB::raw('DATE(factura.fecha_emision) as fecha'), DB::raw('SUM(factura.monto_total) as total'))
                ->whereBetween('factura.fecha_emision', [$fechaInicio, $fechaFin . ' 23:59:59'])
                ->where('factura.estado_pago', 'PAGADO')
                ->groupBy(DB::raw('DATE(factura.fecha_emision)'))
                ->orderBy('fecha')
                ->get();

            return [
                'labels' => $datos->pluck('fecha')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m/Y')),
                'data'   => $datos->pluck('total'),
                'titulo' => 'Ingresos por Día',
            ];
        }
    }

    private function obtenerDatosOcupacion($fechaInicio, $fechaFin, $tipoGrafica = 'barras')
    {
        if ($tipoGrafica === 'torta') {
            $datos = DB::table('entrada')
                ->join('modulo', 'entrada.id_modulo', '=', 'modulo.id_modulo')
                ->select('modulo.ubicacion as label', DB::raw('COUNT(*) as total'))
                ->whereBetween('entrada.fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
                ->groupBy('modulo.ubicacion')
                ->orderBy('total', 'desc')
                ->get();

            return [
                'labels' => $datos->pluck('label'),
                'data'   => $datos->pluck('total'),
                'titulo' => 'Ocupación por Módulo',
            ];
        } else {
            $datos = DB::table('entrada')
                ->select(DB::raw('DATE(fecha_hora_entrada) as fecha'), DB::raw('COUNT(*) as total'))
                ->whereBetween('fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
                ->groupBy(DB::raw('DATE(fecha_hora_entrada)'))
                ->orderBy('fecha')
                ->get();

            return [
                'labels' => $datos->pluck('fecha')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m/Y')),
                'data'   => $datos->pluck('total'),
                'titulo' => 'Ocupación por Día',
            ];
        }
    }

    // =========================================================================
    // RESUMEN
    // =========================================================================

    private function obtenerResumen($tipoReporte, $fechaInicio, $fechaFin)
    {
        switch ($tipoReporte) {
            case 'INGRESOS':  return $this->obtenerResumenIngresos($fechaInicio, $fechaFin);
            case 'OCUPACION': return $this->obtenerResumenOcupacion($fechaInicio, $fechaFin);
            default:          return $this->obtenerResumenIngresos($fechaInicio, $fechaFin);
        }
    }

    private function obtenerResumenIngresos($fechaInicio, $fechaFin)
    {
        $totalIngresos = DB::table('factura')
            ->whereBetween('fecha_emision', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->where('estado_pago', 'PAGADO')
            ->sum('monto_total');

        $totalFacturas = DB::table('factura')
            ->whereBetween('fecha_emision', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->count();

        return [
            'total'    => $totalIngresos,
            'cantidad' => $totalFacturas,
            'promedio' => $totalFacturas > 0 ? $totalIngresos / $totalFacturas : 0,
            'titulo'   => 'Resumen de Ingresos',
        ];
    }

    private function obtenerResumenOcupacion($fechaInicio, $fechaFin)
    {
        $totalEntradas = DB::table('entrada')
            ->whereBetween('fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->count();

        $totalSalidas = DB::table('salida')
            ->join('entrada', 'salida.id_entrada', '=', 'entrada.id_entrada')
            ->whereBetween('salida.fecha_hora_salida', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->count();

        $activos = DB::table('entrada')->where('estado', 'ACTIVO')->count();

        return [
            'total_entradas' => $totalEntradas,
            'total_salidas'  => $totalSalidas,
            'activos'        => $activos,
            'titulo'         => 'Resumen de Ocupación',
        ];
    }

    // =========================================================================
    // REGISTROS DETALLADOS (para la tabla en pantalla y el PDF)
    // =========================================================================

    /**
     * Obtener registros detallados del período filtrado.
     * $limite = null trae todos los registros (para PDF), número trae paginado (para vista).
     */
    private function obtenerRegistrosDetallados($tipoReporte, $fechaInicio, $fechaFin, $limite = null)
    {
        if ($tipoReporte === 'INGRESOS') {
            return $this->obtenerRegistrosIngresos($fechaInicio, $fechaFin, $limite);
        } else {
            return $this->obtenerRegistrosOcupacion($fechaInicio, $fechaFin, $limite);
        }
    }

    private function obtenerRegistrosIngresos($fechaInicio, $fechaFin, $limite = null)
    {
        $query = DB::table('factura')
            ->join('salida', 'factura.id_salida', '=', 'salida.id_salida')
            ->join('entrada', 'salida.id_entrada', '=', 'entrada.id_entrada')
            ->join('tipo_servicio', 'entrada.id_tipo_servicio', '=', 'tipo_servicio.id_tipo_servicio')
            ->join('modulo', 'entrada.id_modulo', '=', 'modulo.id_modulo')
            ->leftJoin('vehiculo', 'entrada.placa', '=', 'vehiculo.placa')
            ->leftJoin('cliente', 'vehiculo.id_cliente', '=', 'cliente.id_cliente')
            ->leftJoin('users', 'cliente.cedula_users', '=', 'users.cedula')
            ->select(
                'factura.id_factura',
                'factura.fecha_emision',
                'factura.monto_total',
                'factura.estado_pago',
                'entrada.placa',
                'users.nombre as nombre_propietario',
                'vehiculo.color as tipo_vehiculo',
                'tipo_servicio.nombre_tipo_servicio',
                'modulo.ubicacion as modulo',
                'entrada.fecha_hora_entrada',
                'salida.fecha_hora_salida',
                DB::raw('TIMESTAMPDIFF(MINUTE, entrada.fecha_hora_entrada, salida.fecha_hora_salida) as minutos_estancia')
            )
            ->whereBetween('factura.fecha_emision', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->orderBy('factura.fecha_emision', 'desc');

        if ($limite) {
            return $query->paginate($limite);
        }

        return $query->get();
    }

    private function obtenerRegistrosOcupacion($fechaInicio, $fechaFin, $limite = null)
    {
        $query = DB::table('entrada')
            ->join('tipo_servicio', 'entrada.id_tipo_servicio', '=', 'tipo_servicio.id_tipo_servicio')
            ->join('modulo', 'entrada.id_modulo', '=', 'modulo.id_modulo')
            ->leftJoin('vehiculo', 'entrada.placa', '=', 'vehiculo.placa')
            ->leftJoin('cliente', 'vehiculo.id_cliente', '=', 'cliente.id_cliente')
            ->leftJoin('users', 'cliente.cedula_users', '=', 'users.cedula')
            ->leftJoin('salida', 'entrada.id_entrada', '=', 'salida.id_entrada')
            ->select(
                'entrada.id_entrada',
                'entrada.placa',
                'users.nombre as nombre_propietario',
                'vehiculo.color as tipo_vehiculo',
                'tipo_servicio.nombre_tipo_servicio',
                'modulo.ubicacion as modulo',
                'entrada.fecha_hora_entrada',
                'salida.fecha_hora_salida',
                'entrada.estado',
                DB::raw('TIMESTAMPDIFF(MINUTE, entrada.fecha_hora_entrada, salida.fecha_hora_salida) as minutos_estancia')
            )
            ->whereBetween('entrada.fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->orderBy('entrada.fecha_hora_entrada', 'desc');

        if ($limite) {
            return $query->paginate($limite);
        }

        return $query->get();
    }

    // =========================================================================
    // ANÁLISIS ADICIONAL
    // =========================================================================

    private function obtenerAnalisisAdicional($fechaInicio, $fechaFin)
    {
        // Top 5 servicios más usados
        $topServicios = DB::table('entrada')
            ->join('tipo_servicio', 'entrada.id_tipo_servicio', '=', 'tipo_servicio.id_tipo_servicio')
            ->select('tipo_servicio.nombre_tipo_servicio', DB::raw('COUNT(*) as total'))
            ->whereBetween('entrada.fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->groupBy('tipo_servicio.nombre_tipo_servicio')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // Top 5 módulos más utilizados
        $topModulos = DB::table('entrada')
            ->join('modulo', 'entrada.id_modulo', '=', 'modulo.id_modulo')
            ->select('modulo.ubicacion', DB::raw('COUNT(*) as total'))
            ->whereBetween('entrada.fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->groupBy('modulo.ubicacion')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // Horas pico del período
        $horasPico = DB::table('entrada')
            ->select(DB::raw('HOUR(fecha_hora_entrada) as hora'), DB::raw('COUNT(*) as total'))
            ->whereBetween('fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->groupBy(DB::raw('HOUR(fecha_hora_entrada)'))
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $item->hora_formateada = str_pad($item->hora, 2, '0', STR_PAD_LEFT) . ':00 - ' . str_pad($item->hora + 1, 2, '0', STR_PAD_LEFT) . ':00';
                return $item;
            });

        // Tipos de vehículo más comunes
        $tiposVehiculo = DB::table('entrada')
            ->leftJoin('vehiculo', 'entrada.placa', '=', 'vehiculo.placa')
            ->select('vehiculo.color as tipo_vehiculo', DB::raw('COUNT(*) as total'))
            ->whereBetween('entrada.fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->groupBy('vehiculo.color')
            ->orderBy('total', 'desc')
            ->get();

        return [
            'top_servicios'  => $topServicios,
            'top_modulos'    => $topModulos,
            'horas_pico'     => $horasPico,
            'tipos_vehiculo' => $tiposVehiculo,
        ];
    }

    // =========================================================================
    // MÉTRICAS FILTRADAS
    // =========================================================================

    private function obtenerMetricasFiltradas($fechaInicio, $fechaFin)
    {
        $diasPeriodo          = \Carbon\Carbon::parse($fechaInicio)->diffInDays(\Carbon\Carbon::parse($fechaFin));
        $fechaInicioAnterior  = \Carbon\Carbon::parse($fechaInicio)->subDays($diasPeriodo + 1)->format('Y-m-d');
        $fechaFinAnterior     = \Carbon\Carbon::parse($fechaInicio)->subDay()->format('Y-m-d');

        $vehiculosAdentro      = DB::table('entrada')->where('estado', 'ACTIVO')->count();
        $totalModulos          = DB::table('modulo')->count();
        $modulosMantenimiento  = DB::table('modulo')->where('estado', 'MANTENIMIENTO')->count();
        $modulosDisponibles    = $totalModulos - $vehiculosAdentro - $modulosMantenimiento;
        $ocupacionPorcentaje   = $totalModulos > 0 ? ($vehiculosAdentro / $totalModulos) * 100 : 0;

        $ingresosPeriodo         = DB::table('factura')->whereBetween('fecha_emision', [$fechaInicio, $fechaFin . ' 23:59:59'])->where('estado_pago', 'PAGADO')->sum('monto_total');
        $ingresosPeriodoAnterior = DB::table('factura')->whereBetween('fecha_emision', [$fechaInicioAnterior, $fechaFinAnterior . ' 23:59:59'])->where('estado_pago', 'PAGADO')->sum('monto_total');
        $cambioIngresos          = $ingresosPeriodoAnterior > 0 ? (($ingresosPeriodo - $ingresosPeriodoAnterior) / $ingresosPeriodoAnterior) * 100 : 0;

        $serviciosPeriodo = DB::table('entrada')->whereBetween('fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])->count();
        $facturasPeriodo  = DB::table('factura')->whereBetween('fecha_emision', [$fechaInicio, $fechaFin . ' 23:59:59'])->where('estado_pago', 'PAGADO')->count();

        $promedioEstancia = DB::table('entrada')
            ->join('salida', 'entrada.id_entrada', '=', 'salida.id_entrada')
            ->whereBetween('entrada.fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, entrada.fecha_hora_entrada, salida.fecha_hora_salida)) as promedio')
            ->value('promedio') ?? 0;

        $servicioMasUsado = DB::table('entrada')
            ->join('tipo_servicio', 'entrada.id_tipo_servicio', '=', 'tipo_servicio.id_tipo_servicio')
            ->whereBetween('entrada.fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->select('tipo_servicio.nombre_tipo_servicio', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo_servicio.nombre_tipo_servicio')
            ->orderBy('total', 'desc')
            ->first();

        $moduloMasUsado = DB::table('entrada')
            ->join('modulo', 'entrada.id_modulo', '=', 'modulo.id_modulo')
            ->whereBetween('entrada.fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->select('modulo.ubicacion', DB::raw('COUNT(*) as total'))
            ->groupBy('modulo.ubicacion')
            ->orderBy('total', 'desc')
            ->first();

        return [
            'vehiculos_adentro'        => $vehiculosAdentro,
            'total_modulos'            => $totalModulos,
            'modulos_disponibles'      => $modulosDisponibles,
            'modulos_mantenimiento'    => $modulosMantenimiento,
            'ocupacion_porcentaje'     => round($ocupacionPorcentaje, 1),
            'ingresos_periodo'         => $ingresosPeriodo,
            'ingresos_periodo_anterior'=> $ingresosPeriodoAnterior,
            'cambio_ingresos'          => round($cambioIngresos, 1),
            'servicios_periodo'        => $serviciosPeriodo,
            'facturas_periodo'         => $facturasPeriodo,
            'promedio_estancia'        => round($promedioEstancia, 0),
            'servicio_mas_usado'       => $servicioMasUsado ? $servicioMasUsado->nombre_tipo_servicio : 'N/A',
            'modulo_mas_usado'         => $moduloMasUsado ? $moduloMasUsado->ubicacion : 'N/A',
            'fecha_inicio'             => $fechaInicio,
            'fecha_fin'                => $fechaFin,
            'dias_periodo'             => $diasPeriodo + 1,
        ];
    }

    // =========================================================================
    // GUARDAR REPORTE
    // =========================================================================

    public function guardar(Request $request)
    {
        $request->validate([
            'nombre_reporte' => 'required|string|max:255',
            'tipo_reporte'   => 'required|string',
            'fecha_inicio'   => 'required|date',
            'fecha_fin'      => 'required|date|after_or_equal:fecha_inicio',
            'observaciones'  => 'nullable|string',
        ]);

        $datosGraficaBarras = $this->obtenerDatosGrafica($request->tipo_reporte, $request->fecha_inicio, $request->fecha_fin, 'barras');
        $datosGraficaTorta  = $this->obtenerDatosGrafica($request->tipo_reporte, $request->fecha_inicio, $request->fecha_fin, 'torta');
        $resumen            = $this->obtenerResumen($request->tipo_reporte, $request->fecha_inicio, $request->fecha_fin);
        $metricas           = $this->obtenerMetricasFiltradas($request->fecha_inicio, $request->fecha_fin);
        $analisis           = $this->obtenerAnalisisAdicional($request->fecha_inicio, $request->fecha_fin);

        $reporte = Reporte::create([
            'nombre_reporte'  => $request->nombre_reporte,
            'tipo_reporte'    => $request->tipo_reporte,
            'fecha_inicio'    => $request->fecha_inicio,
            'fecha_fin'       => $request->fecha_fin,
            'total_recaudado' => $metricas['ingresos_periodo'] ?? 0,
            'contenido'       => [
                'grafica_barras' => $datosGraficaBarras,
                'grafica_torta'  => $datosGraficaTorta,
                'resumen'        => $resumen,
                'metricas'       => $metricas,
                'analisis'       => $analisis,
            ],
            'observaciones'   => $request->observaciones,
        ]);

        return redirect()->route('reportes')->with('success', 'Reporte guardado correctamente');
    }

    // =========================================================================
    // DESCARGA PDF (reporte guardado)
    // =========================================================================

    public function descargarPDF($id)
    {
        $reporte = Reporte::findOrFail($id);

        // Obtener registros detallados frescos de BD
        $registros = $this->obtenerRegistrosDetallados(
            $reporte->tipo_reporte,
            $reporte->fecha_inicio,
            $reporte->fecha_fin,
            null // Sin límite → todos los registros
        );
        $analisis = $this->obtenerAnalisisAdicional($reporte->fecha_inicio, $reporte->fecha_fin);

        $html = $this->generarHTMLProfesional($reporte, $registros, $analisis);

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('reporte_' . $reporte->id_reporte . '.pdf');
    }

    // =========================================================================
    // DESCARGA TEMPORAL (sin guardar)
    // =========================================================================

    public function descargarTemporal(Request $request)
    {
        $tipoReporte = $request->tipo_reporte;
        $fechaInicio = $request->fecha_inicio;
        $fechaFin    = $request->fecha_fin;

        $datosGraficaBarras = $this->obtenerDatosGrafica($tipoReporte, $fechaInicio, $fechaFin, 'barras');
        $datosGraficaTorta  = $this->obtenerDatosGrafica($tipoReporte, $fechaInicio, $fechaFin, 'torta');
        $resumen            = $this->obtenerResumen($tipoReporte, $fechaInicio, $fechaFin);
        $metricas           = $this->obtenerMetricasFiltradas($fechaInicio, $fechaFin);
        $registros          = $this->obtenerRegistrosDetallados($tipoReporte, $fechaInicio, $fechaFin, null);
        $analisis           = $this->obtenerAnalisisAdicional($fechaInicio, $fechaFin);

        $reporteTemp = (object) [
            'nombre_reporte'  => 'Reporte de ' . ($tipoReporte === 'INGRESOS' ? 'Ingresos' : 'Ocupación'),
            'tipo_reporte'    => $tipoReporte,
            'fecha_inicio'    => $fechaInicio,
            'fecha_fin'       => $fechaFin,
            'total_recaudado' => $metricas['ingresos_periodo'] ?? 0,
            'contenido'       => [
                'grafica_barras' => $datosGraficaBarras,
                'grafica_torta'  => $datosGraficaTorta,
                'resumen'        => $resumen,
                'metricas'       => $metricas,
                'analisis'       => $analisis,
            ],
            'observaciones'   => 'Generado el ' . now()->format('d/m/Y H:i:s') . '. Período: ' . $metricas['dias_periodo'] . ' días.',
            'id_reporte'      => null,
        ];

        $html = $this->generarHTMLProfesional($reporteTemp, $registros, $analisis);

        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');

        $nombreArchivo = 'reporte_' . strtolower($tipoReporte) . '_' . $fechaInicio . '_' . $fechaFin . '.pdf';
        return $pdf->download($nombreArchivo);
    }

    // =========================================================================
    // GENERADOR DE HTML PROFESIONAL PARA PDF
    // =========================================================================

    private function generarHTMLProfesional($reporte, $registros, $analisis)
    {
        $contenido      = (array) ($reporte->contenido ?? []);
        $resumen        = $contenido['resumen']        ?? [];
        $metricas       = $contenido['metricas']       ?? [];
        $graficaBarras  = $contenido['grafica_barras'] ?? [];
        $graficaTorta   = $contenido['grafica_torta']  ?? [];

        $fechaInicio = \Carbon\Carbon::parse($reporte->fecha_inicio)->format('d/m/Y');
        $fechaFin    = \Carbon\Carbon::parse($reporte->fecha_fin)->format('d/m/Y');
        $diasPeriodo = $metricas['dias_periodo'] ?? '-';
        $tipoLabel   = $reporte->tipo_reporte === 'INGRESOS' ? 'Ingresos' : 'Ocupación';

        // ------------------------------------------------------------------ CSS
        $css = "
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 11px; color: #102033; background: #ffffff; }

        /* Portada */
        .cover { padding: 40px 50px; border-bottom: 5px solid #d7a93a; margin-bottom: 30px; background-color: #111827; color: #ffffff; }
        .cover-top { display: table; width: 100%; margin-bottom: 20px; }
        .cover-logo { display: table-cell; vertical-align: middle; }
        .cover-logo .brand { font-size: 28px; font-weight: 900; color: #ffffff; letter-spacing: 1.5px; }
        .cover-logo .brand span { color: #d7a93a; }
        .cover-logo .tagline { font-size: 10px; color: #8a9aa9; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }
        .cover-meta { display: table-cell; text-align: right; vertical-align: middle; }
        .cover-meta .badge-tipo { background: #d7a93a; color: #111827; padding: 5px 14px; border-radius: 4px; font-size: 10px; font-weight: bold; letter-spacing: 1px; }
        .cover-meta .fecha-gen { font-size: 9px; color: #8a9aa9; margin-top: 6px; }
        .cover-title { text-align: center; padding: 25px 0 15px; border-top: 1px solid #1f2937; }
        .cover-title h1 { font-size: 24px; font-weight: 800; color: #ffffff; margin-bottom: 8px; }
        .cover-title .periodo { font-size: 12px; color: #8a9aa9; }
        .cover-title .periodo strong { color: #d7a93a; background: rgba(215,169,58,0.15); padding: 2px 6px; border-radius: 4px; }

        /* Secciones */
        .section { margin: 0 40px 25px; }
        .section-header { background: #111827; color: #ffffff; padding: 10px 16px; border-radius: 4px 4px 0 0; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; border-top: 3px solid #d7a93a; }
        .section-header .icon { margin-right: 6px; color: #d7a93a; }
        .section-body { border: 1px solid #dfe7f1; border-top: none; border-radius: 0 0 4px 4px; padding: 18px; background: #ffffff; }

        /* KPIs */
        .kpi-grid { display: table; width: 100%; border-collapse: separate; border-spacing: 10px; margin-left: -5px; width: calc(100% + 10px); }
        .kpi-row  { display: table-row; }
        .kpi-cell { display: table-cell; width: 25%; }
        .kpi-card { background: #ffffff; border: 1px solid #dfe7f1; border-radius: 6px; padding: 16px; text-align: center; border-bottom: 3px solid #d7a93a; }
        .kpi-card.dark { background: #ffffff; border-bottom: 3px solid #111827; }
        .kpi-card.green { background: #ffffff; border-bottom: 3px solid #2e8b57; }
        .kpi-card.blue  { background: #ffffff; border-bottom: 3px solid #3d6ecf; }
        .kpi-value { font-size: 22px; font-weight: 800; color: #102033; margin-bottom: 4px; }
        .kpi-label { font-size: 9px; text-transform: uppercase; color: #5f6f82; font-weight: bold; letter-spacing: 0.5px; }
        .kpi-badge { font-size: 9px; margin-top: 6px; padding: 3px 8px; background: #f9f1d8; color: #8a5a00; border-radius: 12px; display: inline-block; font-weight: bold; }
        .kpi-card.dark .kpi-badge { background: #eef3f9; color: #102033; }
        .kpi-card.green .kpi-badge { background: #eafaf1; color: #1d5d3b; }

        /* Tablas de datos */
        .data-table { width: 100%; border-collapse: collapse; font-size: 10px; }
        .data-table thead tr { background: #f4f7fb; }
        .data-table thead th { padding: 10px; text-align: left; font-weight: bold; color: #102033; text-transform: uppercase; font-size: 9px; letter-spacing: 0.5px; border-bottom: 2px solid #dfe7f1; }
        .data-table tbody tr:nth-child(even) { background: #ffffff; }
        .data-table tbody tr:hover { background: #f4f7fb; }
        .data-table tbody td { padding: 8px 10px; border-bottom: 1px solid #dfe7f1; color: #5f6f82; vertical-align: middle; }
        .data-table .td-mono { font-family: monospace; font-size: 10px; font-weight: bold; color: #102033; }
        .data-table tfoot tr { background: #f4f7fb; color: #102033; font-weight: bold; }
        .data-table tfoot td { padding: 10px; border-top: 2px solid #dfe7f1; }

        /* Barras de progreso */
        .bar-row { display: table; width: 100%; margin-bottom: 8px; }
        .bar-label { display: table-cell; width: 35%; vertical-align: middle; font-size: 10px; font-weight: bold; color: #102033; padding-right: 8px; }
        .bar-container { display: table-cell; vertical-align: middle; width: 50%; }
        .bar-bg { background: #eef3f9; border-radius: 3px; height: 12px; overflow: hidden; }
        .bar-fill { background: #d7a93a; height: 12px; border-radius: 3px; }
        .bar-fill.alt1 { background: #111827; }
        .bar-fill.alt2 { background: #3d6ecf; }
        .bar-fill.alt3 { background: #2e8b57; }
        .bar-fill.alt4 { background: #d44949; }
        .bar-value { display: table-cell; width: 15%; text-align: right; vertical-align: middle; font-size: 10px; font-weight: bold; color: #102033; padding-left: 8px; }

        /* Badges */
        .badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 9px; font-weight: bold; }
        .badge-success { background: #eafaf1; color: #1d5d3b; }
        .badge-warning { background: #f9f1d8; color: #8a5a00; }
        .badge-danger  { background: #fff1f1; color: #9a2d2d; }
        .badge-info    { background: #edf4ff; color: #234a99; }
        .badge-dark    { background: #111827; color: #ffffff; }

        /* Análisis lateral (2 columnas) */
        .two-col { display: table; width: 100%; border-spacing: 12px; border-collapse: separate; }
        .col-left  { display: table-cell; width: 50%; vertical-align: top; }
        .col-right { display: table-cell; width: 50%; vertical-align: top; padding-left: 12px; }
        .mini-section { margin-bottom: 14px; }
        .mini-title { font-size: 10px; font-weight: bold; color: #102033; text-transform: uppercase; letter-spacing: 0.5px; padding-bottom: 6px; border-bottom: 2px solid #dfe7f1; margin-bottom: 12px; }

        /* Observaciones */
        .obs-box { background: #f4f7fb; border-left: 4px solid #d7a93a; padding: 14px; border-radius: 0 4px 4px 0; font-size: 11px; color: #5f6f82; line-height: 1.6; }

        /* Footer */
        .footer { margin: 30px 40px 20px; padding-top: 15px; border-top: 2px solid #dfe7f1; display: table; width: calc(100% - 80px); }
        .footer-left  { display: table-cell; vertical-align: middle; font-size: 9px; color: #8a9aa9; }
        .footer-right { display: table-cell; text-align: right; vertical-align: middle; font-size: 9px; color: #8a9aa9; }
        .footer-brand { font-weight: bold; color: #d7a93a; font-size: 10px; }

        /* Separador */
        .divider { border: none; border-top: 1px solid #dfe7f1; margin: 0 40px 20px; }

        /* Contador de registros */
        .record-count { font-size: 10px; color: #5f6f82; margin-bottom: 10px; padding: 6px 10px; background: #f4f7fb; border-radius: 4px; border-left: 3px solid #d7a93a; }
        ";

        // ------------------------------------------------------------------ PORTADA
        $html = "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>{$reporte->nombre_reporte}</title><style>{$css}</style></head><body>";

        $html .= "
        <div class='cover'>
            <div class='cover-top'>
                <div class='cover-logo'>
                    <div class='brand'>PARKING<span>SURE</span>L</div>
                    <div class='tagline'>Sistema de Gestión de Parqueaderos</div>
                </div>
                <div class='cover-meta'>
                    <div class='badge-tipo'>REPORTE DE {$tipoLabel}</div>
                    <div class='fecha-gen'>Generado: " . now()->format('d/m/Y H:i:s') . "</div>
                </div>
            </div>
            <div class='cover-title'>
                <h1>{$reporte->nombre_reporte}</h1>
                <div class='periodo'>Período analizado: <strong>{$fechaInicio}</strong> al <strong>{$fechaFin}</strong> &nbsp;|&nbsp; {$diasPeriodo} días</div>
            </div>
        </div>";

        // ------------------------------------------------------------------ KPIs
        $html .= "<div class='section'>
            <div class='section-header'><span class='icon'>&#9733;</span> Resumen Ejecutivo</div>
            <div class='section-body'>
                <div class='kpi-grid'><div class='kpi-row'>";

        if ($reporte->tipo_reporte === 'INGRESOS') {
            $cambio       = $metricas['cambio_ingresos'] ?? 0;
            $signo        = $cambio >= 0 ? '+' : '';
            $html .= "
                <div class='kpi-cell'>
                    <div class='kpi-card'>
                        <div class='kpi-value'>\$" . number_format($metricas['ingresos_periodo'] ?? 0, 2) . "</div>
                        <div class='kpi-label'>Total Recaudado</div>
                        <div class='kpi-badge'>{$signo}" . number_format($cambio, 1) . "% vs período ant.</div>
                    </div>
                </div>
                <div class='kpi-cell'>
                    <div class='kpi-card dark'>
                        <div class='kpi-value'>" . ($metricas['facturas_periodo'] ?? 0) . "</div>
                        <div class='kpi-label'>Facturas Emitidas</div>
                        <div class='kpi-badge'>" . ($metricas['servicios_periodo'] ?? 0) . " entradas</div>
                    </div>
                </div>
                <div class='kpi-cell'>
                    <div class='kpi-card green'>
                        <div class='kpi-value'>\$" . number_format(($metricas['facturas_periodo'] ?? 0) > 0 ? ($metricas['ingresos_periodo'] ?? 0) / ($metricas['facturas_periodo'] ?? 1) : 0, 2) . "</div>
                        <div class='kpi-label'>Promedio / Ticket</div>
                        <div class='kpi-badge'>Período anterior: \$" . number_format($metricas['ingresos_periodo_anterior'] ?? 0, 2) . "</div>
                    </div>
                </div>
                <div class='kpi-cell'>
                    <div class='kpi-card blue'>
                        <div class='kpi-value'>" . ($metricas['promedio_estancia'] ?? 0) . " min</div>
                        <div class='kpi-label'>Estancia Promedio</div>
                        <div class='kpi-badge'>" . ($metricas['servicio_mas_usado'] ?? 'N/A') . "</div>
                    </div>
                </div>";
        } else {
            $totalEntradas = $resumen['total_entradas'] ?? 0;
            $totalSalidas  = $resumen['total_salidas']  ?? 0;
            $activos       = $resumen['activos']         ?? 0;
            $html .= "
                <div class='kpi-cell'>
                    <div class='kpi-card'>
                        <div class='kpi-value'>{$totalEntradas}</div>
                        <div class='kpi-label'>Total Entradas</div>
                        <div class='kpi-badge'>En el período</div>
                    </div>
                </div>
                <div class='kpi-cell'>
                    <div class='kpi-card dark'>
                        <div class='kpi-value'>{$totalSalidas}</div>
                        <div class='kpi-label'>Total Salidas</div>
                        <div class='kpi-badge'>Completadas</div>
                    </div>
                </div>
                <div class='kpi-cell'>
                    <div class='kpi-card green'>
                        <div class='kpi-value'>{$activos}</div>
                        <div class='kpi-label'>Activos Ahora</div>
                        <div class='kpi-badge'>Tiempo real</div>
                    </div>
                </div>
                <div class='kpi-cell'>
                    <div class='kpi-card blue'>
                        <div class='kpi-value'>" . ($metricas['promedio_estancia'] ?? 0) . " min</div>
                        <div class='kpi-label'>Estancia Promedio</div>
                        <div class='kpi-badge'>" . ($metricas['modulo_mas_usado'] ?? 'N/A') . "</div>
                    </div>
                </div>";
        }

        $html .= "</div></div></div></div></div>";

        // ------------------------------------------------------------------ ANÁLISIS TEMPORAL (Barras → tabla visual)
        if (!empty($graficaBarras['labels']) && count($graficaBarras['labels']) > 0) {
            $labelsBarras = is_array($graficaBarras['labels']) ? $graficaBarras['labels'] : $graficaBarras['labels']->toArray();
            $dataBarras   = is_array($graficaBarras['data'])   ? $graficaBarras['data']   : $graficaBarras['data']->toArray();
            $maxBarras    = count($dataBarras) > 0 ? max($dataBarras) : 1;
            if ($maxBarras == 0) $maxBarras = 1;

            $colorClasses = ['', 'alt1', 'alt2', 'alt3', 'alt4'];

            $html .= "<div class='section'>
                <div class='section-header'><span class='icon'>&#9641;</span> Análisis Temporal — {$graficaBarras['titulo']}</div>
                <div class='section-body'>";

            $totalGral = array_sum($dataBarras);
            foreach ($labelsBarras as $i => $label) {
                $val   = $dataBarras[$i] ?? 0;
                $pct   = $maxBarras > 0 ? round(($val / $maxBarras) * 100, 1) : 0;
                $color = $colorClasses[$i % count($colorClasses)];
                $valFmt = $reporte->tipo_reporte === 'INGRESOS' ? '$' . number_format($val, 2) : $val;
                $html .= "
                <div class='bar-row'>
                    <div class='bar-label'>{$label}</div>
                    <div class='bar-container'><div class='bar-bg'><div class='bar-fill {$color}' style='width:{$pct}%'></div></div></div>
                    <div class='bar-value'>{$valFmt}</div>
                </div>";
            }

            $totalFmt = $reporte->tipo_reporte === 'INGRESOS' ? '$' . number_format($totalGral, 2) : $totalGral;
            $html .= "<div style='border-top:2px solid #ffd700; margin-top:10px; padding-top:8px; text-align:right; font-weight:700; font-size:12px; color:#0a0a0a;'>
                        TOTAL PERÍODO: {$totalFmt}
                      </div>";

            $html .= "</div></div>";
        }

        // ------------------------------------------------------------------ DISTRIBUCIÓN (Torta → tabla con %)
        if (!empty($graficaTorta['labels']) && count($graficaTorta['labels']) > 0) {
            $labelsTorta = is_array($graficaTorta['labels']) ? $graficaTorta['labels'] : $graficaTorta['labels']->toArray();
            $dataTorta   = is_array($graficaTorta['data'])   ? $graficaTorta['data']   : $graficaTorta['data']->toArray();
            $totalTorta  = array_sum($dataTorta) ?: 1;

            $html .= "<div class='section'>
                <div class='section-header'><span class='icon'>&#9678;</span> Distribución por Categoría — {$graficaTorta['titulo']}</div>
                <div class='section-body'>
                <table class='data-table'>
                    <thead><tr>
                        <th>#</th>
                        <th>Categoría</th>
                        <th>Valor</th>
                        <th>% del Total</th>
                        <th>Participación Visual</th>
                    </tr></thead>
                    <tbody>";

            $colorClasses = ['', 'alt1', 'alt2', 'alt3', 'alt4'];
            foreach ($labelsTorta as $i => $label) {
                $val  = $dataTorta[$i] ?? 0;
                $pct  = round(($val / $totalTorta) * 100, 1);
                $color = $colorClasses[$i % count($colorClasses)];
                $valFmt = $reporte->tipo_reporte === 'INGRESOS' ? '$' . number_format($val, 2) : $val;
                $html .= "<tr>
                    <td style='font-weight:700; color:#999;'>" . ($i + 1) . "</td>
                    <td style='font-weight:600;'>{$label}</td>
                    <td style='font-weight:700; color:#0a0a0a;'>{$valFmt}</td>
                    <td><span class='badge badge-warning'>{$pct}%</span></td>
                    <td style='width:150px;'><div class='bar-bg' style='height:10px;'><div class='bar-fill {$color}' style='width:{$pct}%; height:10px;'></div></div></td>
                </tr>";
            }

            $totalFmt = $reporte->tipo_reporte === 'INGRESOS' ? '$' . number_format($totalTorta, 2) : $totalTorta;
            $html .= "  </tbody>
                    <tfoot><tr>
                        <td colspan='2'>TOTAL</td>
                        <td>{$totalFmt}</td>
                        <td>100%</td>
                        <td></td>
                    </tr></tfoot>
                </table>
                </div></div>";
        }

        // ------------------------------------------------------------------ TABLA COMPLETA DE REGISTROS
        $totalRegistros = is_object($registros) && method_exists($registros, 'count') ? $registros->count() : count($registros);

        $html .= "<div class='section'>
            <div class='section-header'><span class='icon'>&#9776;</span> Registros Detallados del Período (" . number_format($totalRegistros) . " registros)</div>
            <div class='section-body'>
            <div class='record-count'>&#9432; Se muestran todos los registros del período: {$fechaInicio} al {$fechaFin}</div>";

        if ($reporte->tipo_reporte === 'INGRESOS') {
            $html .= "<table class='data-table'>
                <thead><tr>
                    <th>N° Factura</th>
                    <th>Fecha / Hora</th>
                    <th>Placa</th>
                    <th>Propietario</th>
                    <th>Tipo Vehículo</th>
                    <th>Servicio</th>
                    <th>Módulo</th>
                    <th>Estancia</th>
                    <th>Monto</th>
                    <th>Estado</th>
                </tr></thead>
                <tbody>";

            $sumaTotal = 0;
            foreach ($registros as $r) {
                $sumaTotal    += $r->monto_total ?? 0;
                $estancia      = $r->minutos_estancia ?? 0;
                $horas         = floor($estancia / 60);
                $mins          = $estancia % 60;
                $estanciaFmt   = $estancia > 0 ? ($horas > 0 ? "{$horas}h {$mins}m" : "{$mins}m") : '—';
                $estadoBadge   = ($r->estado_pago ?? '') === 'PAGADO'
                    ? "<span class='badge badge-success'>PAGADO</span>"
                    : "<span class='badge badge-danger'>" . ($r->estado_pago ?? 'N/A') . "</span>";

                $html .= "<tr>
                    <td class='td-mono'>#" . str_pad($r->id_factura ?? '?', 5, '0', STR_PAD_LEFT) . "</td>
                    <td>" . ($r->fecha_emision ? \Carbon\Carbon::parse($r->fecha_emision)->format('d/m/Y H:i') : '—') . "</td>
                    <td class='td-mono' style='font-weight:700;'>" . ($r->placa ?? '—') . "</td>
                    <td>" . ($r->nombre_propietario ?? 'Sin registro') . "</td>
                    <td>" . ($r->tipo_vehiculo ?? '—') . "</td>
                    <td>" . ($r->nombre_tipo_servicio ?? '—') . "</td>
                    <td>" . ($r->modulo ?? '—') . "</td>
                    <td style='text-align:center;'>{$estanciaFmt}</td>
                    <td style='font-weight:700; color:#0a0a0a;'>\$" . number_format($r->monto_total ?? 0, 2) . "</td>
                    <td>{$estadoBadge}</td>
                </tr>";
            }

            $html .= "  </tbody>
                <tfoot><tr>
                    <td colspan='8'>TOTAL DEL PERÍODO</td>
                    <td>\$" . number_format($sumaTotal, 2) . "</td>
                    <td></td>
                </tr></tfoot>
            </table>";
        } else {
            // Ocupación
            $html .= "<table class='data-table'>
                <thead><tr>
                    <th>ID Entrada</th>
                    <th>Fecha Entrada</th>
                    <th>Fecha Salida</th>
                    <th>Placa</th>
                    <th>Propietario</th>
                    <th>Servicio</th>
                    <th>Módulo</th>
                    <th>Estancia</th>
                    <th>Estado</th>
                </tr></thead>
                <tbody>";

            foreach ($registros as $r) {
                $estancia    = $r->minutos_estancia ?? 0;
                $horas       = floor($estancia / 60);
                $mins        = $estancia % 60;
                $estanciaFmt = $estancia > 0 ? ($horas > 0 ? "{$horas}h {$mins}m" : "{$mins}m") : '—';
                $estadoBadge = ($r->estado ?? '') === 'ACTIVO'
                    ? "<span class='badge badge-success'>ACTIVO</span>"
                    : "<span class='badge badge-info'>COMPLETADO</span>";

                $html .= "<tr>
                    <td class='td-mono'>#" . str_pad($r->id_entrada ?? '?', 5, '0', STR_PAD_LEFT) . "</td>
                    <td>" . ($r->fecha_hora_entrada  ? \Carbon\Carbon::parse($r->fecha_hora_entrada)->format('d/m/Y H:i')  : '—') . "</td>
                    <td>" . ($r->fecha_hora_salida   ? \Carbon\Carbon::parse($r->fecha_hora_salida)->format('d/m/Y H:i')   : '<span class=\"badge badge-warning\">En curso</span>') . "</td>
                    <td class='td-mono' style='font-weight:700;'>" . ($r->placa ?? '—') . "</td>
                    <td>" . ($r->nombre_propietario ?? 'Sin registro') . "</td>
                    <td>" . ($r->nombre_tipo_servicio ?? '—') . "</td>
                    <td>" . ($r->modulo ?? '—') . "</td>
                    <td style='text-align:center;'>{$estanciaFmt}</td>
                    <td>{$estadoBadge}</td>
                </tr>";
            }

            $html .= "  </tbody>
                <tfoot><tr>
                    <td colspan='7'>TOTAL DE REGISTROS</td>
                    <td colspan='2'>" . number_format($totalRegistros) . " entradas</td>
                </tr></tfoot>
            </table>";
        }

        $html .= "</div></div>";

        // ------------------------------------------------------------------ ANÁLISIS ADICIONAL
        $html .= "<div class='section'>
            <div class='section-header'><span class='icon'>&#9670;</span> Análisis Adicional del Período</div>
            <div class='section-body'>
            <div class='two-col'>
                <div class='col-left'>";

        // Top Servicios
        $topServicios = $analisis['top_servicios'] ?? collect();
        $maxServicios = $topServicios->max('total') ?: 1;
        $html .= "<div class='mini-section'><div class='mini-title'>Top Servicios Más Usados</div>";
        foreach ($topServicios as $i => $s) {
            $pct   = round(($s->total / $maxServicios) * 100, 0);
            $color = ['', 'alt1', 'alt2', 'alt3', 'alt4'][$i % 5];
            $html .= "<div class='bar-row'>
                <div class='bar-label'>{$s->nombre_tipo_servicio}</div>
                <div class='bar-container'><div class='bar-bg'><div class='bar-fill {$color}' style='width:{$pct}%'></div></div></div>
                <div class='bar-value'>{$s->total}</div>
            </div>";
        }
        $html .= "</div>";

        // Top Módulos
        $topModulos = $analisis['top_modulos'] ?? collect();
        $maxModulos = $topModulos->max('total') ?: 1;
        $html .= "<div class='mini-section'><div class='mini-title'>Módulos Más Utilizados</div>";
        foreach ($topModulos as $i => $m) {
            $pct   = round(($m->total / $maxModulos) * 100, 0);
            $color = ['alt2', 'alt3', 'alt4', '', 'alt1'][$i % 5];
            $html .= "<div class='bar-row'>
                <div class='bar-label'>{$m->ubicacion}</div>
                <div class='bar-container'><div class='bar-bg'><div class='bar-fill {$color}' style='width:{$pct}%'></div></div></div>
                <div class='bar-value'>{$m->total}</div>
            </div>";
        }
        $html .= "</div>";

        $html .= "</div><div class='col-right'>";

        // Horas pico
        $horasPico = $analisis['horas_pico'] ?? collect();
        $maxHoras  = $horasPico->max('total') ?: 1;
        $html .= "<div class='mini-section'><div class='mini-title'>Horas Pico de Mayor Afluencia</div>";
        foreach ($horasPico as $i => $h) {
            $pct   = round(($h->total / $maxHoras) * 100, 0);
            $color = ['alt3', '', 'alt1', 'alt2', 'alt4'][$i % 5];
            $html .= "<div class='bar-row'>
                <div class='bar-label'>{$h->hora_formateada}</div>
                <div class='bar-container'><div class='bar-bg'><div class='bar-fill {$color}' style='width:{$pct}%'></div></div></div>
                <div class='bar-value'>{$h->total}</div>
            </div>";
        }
        $html .= "</div>";

        // Tipos de vehículo
        $tiposVehiculo = $analisis['tipos_vehiculo'] ?? collect();
        if ($tiposVehiculo->count() > 0) {
            $maxTipo = $tiposVehiculo->max('total') ?: 1;
            $html .= "<div class='mini-section'><div class='mini-title'>Tipos de Vehículo</div>";
            foreach ($tiposVehiculo as $i => $tv) {
                $pct   = round(($tv->total / $maxTipo) * 100, 0);
                $color = ['alt1', 'alt2', '', 'alt3', 'alt4'][$i % 5];
                $tipoNombre = $tv->tipo_vehiculo ?? 'Sin clasificar';
                $html .= "<div class='bar-row'>
                    <div class='bar-label'>{$tipoNombre}</div>
                    <div class='bar-container'><div class='bar-bg'><div class='bar-fill {$color}' style='width:{$pct}%'></div></div></div>
                    <div class='bar-value'>{$tv->total}</div>
                </div>";
            }
            $html .= "</div>";
        }

        $html .= "</div></div></div></div>";

        // ------------------------------------------------------------------ OBSERVACIONES
        if (!empty($reporte->observaciones)) {
            $html .= "<div class='section'>
                <div class='section-header'><span class='icon'>&#9999;</span> Observaciones Ejecutivas</div>
                <div class='section-body'>
                    <div class='obs-box'>" . nl2br(htmlspecialchars($reporte->observaciones)) . "</div>
                </div>
            </div>";
        }

        // ------------------------------------------------------------------ FOOTER
        $html .= "
        <div class='footer'>
            <div class='footer-left'>
                <div class='footer-brand'>PARKINGSUREL</div>
                <div>Sistema de Gestión de Parqueaderos &mdash; Reporte Oficial</div>
            </div>
            <div class='footer-right'>
                <div>Generado el " . now()->format('d/m/Y \a \l\a\s H:i:s') . "</div>
                <div>&copy; " . date('Y') . " Parking Surel &mdash; Todos los derechos reservados</div>
            </div>
        </div>
        </body></html>";

        return $html;
    }

    // =========================================================================
    // DESCARGAR EXCEL
    // =========================================================================

    public function descargarExcel($id)
    {
        $reporte = Reporte::findOrFail($id);

        $csv  = "\xEF\xBB\xBF"; // BOM UTF-8
        $csv .= "REPORTE PARKING SUREL\n";
        $csv .= "Nombre:;{$reporte->nombre_reporte}\n";
        $csv .= "Tipo:;{$reporte->tipo_reporte}\n";
        $csv .= "Fecha Inicio:;{$reporte->fecha_inicio}\n";
        $csv .= "Fecha Fin:;{$reporte->fecha_fin}\n";
        $csv .= "Total Recaudado:;{$reporte->total_recaudado}\n\n";

        $registros = $this->obtenerRegistrosDetallados($reporte->tipo_reporte, $reporte->fecha_inicio, $reporte->fecha_fin, null);

        if ($reporte->tipo_reporte === 'INGRESOS') {
            $csv .= "N° Factura;Fecha Emisión;Placa;Propietario;Tipo Vehículo;Servicio;Módulo;Estancia (min);Monto;Estado\n";
            foreach ($registros as $r) {
                $csv .= implode(';', [
                    $r->id_factura ?? '',
                    $r->fecha_emision ?? '',
                    $r->placa ?? '',
                    $r->nombre_propietario ?? '',
                    $r->tipo_vehiculo ?? '',
                    $r->nombre_tipo_servicio ?? '',
                    $r->modulo ?? '',
                    $r->minutos_estancia ?? 0,
                    $r->monto_total ?? 0,
                    $r->estado_pago ?? '',
                ]) . "\n";
            }
        } else {
            $csv .= "ID Entrada;Fecha Entrada;Fecha Salida;Placa;Propietario;Servicio;Módulo;Estancia (min);Estado\n";
            foreach ($registros as $r) {
                $csv .= implode(';', [
                    $r->id_entrada ?? '',
                    $r->fecha_hora_entrada ?? '',
                    $r->fecha_hora_salida ?? '',
                    $r->placa ?? '',
                    $r->nombre_propietario ?? '',
                    $r->nombre_tipo_servicio ?? '',
                    $r->modulo ?? '',
                    $r->minutos_estancia ?? 0,
                    $r->estado ?? '',
                ]) . "\n";
            }
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="reporte_' . $reporte->id_reporte . '.csv"');
    }

    // =========================================================================
    // ELIMINAR REPORTE
    // =========================================================================

    public function destroy($id)
    {
        try {
            Reporte::findOrFail($id)->delete();
            return redirect()->route('reportes')->with('success', 'Reporte eliminado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('reportes')->withErrors(['error' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    }

    // =========================================================================
    // TEST
    // =========================================================================

    public function test()
    {
        try {
            $fechaInicio = now()->subDays(30)->format('Y-m-d');
            $fechaFin    = now()->format('Y-m-d');
            $metricas    = $this->obtenerMetricasFiltradas($fechaInicio, $fechaFin);

            return response()->json(['status' => 'success', 'metricas' => $metricas]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }
    }
}
