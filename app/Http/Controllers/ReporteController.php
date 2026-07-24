<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reporte;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subDays(30)->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));
        $tipoReporte = $request->input('tipo_reporte', 'INGRESOS');

        // Datos para gráficas
        $datosGrafica = $this->obtenerDatosGrafica($tipoReporte, $fechaInicio, $fechaFin);
        
        // Resumen de datos
        $resumen = $this->obtenerResumen($tipoReporte, $fechaInicio, $fechaFin);

        // Historial de reportes guardados
        $reportesGuardados = Reporte::orderBy('created_at', 'desc')->take(10)->get();

        return view('reportes', [
            'active' => 'reportes',
            'datosGrafica' => $datosGrafica,
            'resumen' => $resumen,
            'reportesGuardados' => $reportesGuardados,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'tipoReporte' => $tipoReporte
        ]);
    }

    /**
     * Obtener datos para la gráfica según el tipo de reporte
     */
    private function obtenerDatosGrafica($tipoReporte, $fechaInicio, $fechaFin)
    {
        switch ($tipoReporte) {
            case 'INGRESOS':
                return $this->obtenerDatosIngresos($fechaInicio, $fechaFin);
            case 'OCUPACION':
                return $this->obtenerDatosOcupacion($fechaInicio, $fechaFin);
            case 'SERVICIOS':
                return $this->obtenerDatosServicios($fechaInicio, $fechaFin);
            default:
                return $this->obtenerDatosIngresos($fechaInicio, $fechaFin);
        }
    }

    /**
     * Datos de ingresos por día
     */
    private function obtenerDatosIngresos($fechaInicio, $fechaFin)
    {
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

    /**
     * Datos de ocupación por día
     */
    private function obtenerDatosOcupacion($fechaInicio, $fechaFin)
    {
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

    /**
     * Datos de servicios más utilizados
     */
    private function obtenerDatosServicios($fechaInicio, $fechaFin)
    {
        $datos = DB::table('entrada')
            ->join('tipo_servicio', 'entrada.id_tipo_servicio', '=', 'tipo_servicio.id_tipo_servicio')
            ->select(
                'tipo_servicio.nombre_tipo_servicio as nombre',
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('entrada.fecha_hora_entrada', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->groupBy('tipo_servicio.nombre_tipo_servicio')
            ->orderBy('total', 'desc')
            ->get();

        return [
            'labels' => $datos->pluck('nombre'),
            'data' => $datos->pluck('total'),
            'titulo' => 'Servicios Más Utilizados'
        ];
    }

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
            case 'SERVICIOS':
                return $this->obtenerResumenServicios($fechaInicio, $fechaFin);
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
     */
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

        $datosGrafica = $this->obtenerDatosGrafica($request->tipo_reporte, $request->fecha_inicio, $request->fecha_fin);
        $resumen = $this->obtenerResumen($request->tipo_reporte, $request->fecha_inicio, $request->fecha_fin);

        $reporte = Reporte::create([
            'nombre_reporte' => $request->nombre_reporte,
            'tipo_reporte' => $request->tipo_reporte,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'total_recaudado' => $resumen['total'] ?? 0,
            'contenido' => [
                'grafica' => $datosGrafica,
                'resumen' => $resumen
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
        
        // Generar HTML para el PDF
        $html = view('reportes.pdf', compact('reporte'))->render();
        
        // Usar dompdf o similar para generar PDF
        // Por ahora retornamos el HTML
        return response($html)->header('Content-Type', 'text/html');
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
     * Eliminar reporte
     */
    public function destroy($id)
    {
        $reporte = Reporte::findOrFail($id);
        $reporte->delete();
        
        return redirect()->route('reportes')->with('success', 'Reporte eliminado correctamente');
    }

    /**
     * Descargar reporte temporal (sin guardar en BD)
     */
    public function descargarTemporal(Request $request)
    {
        $tipoReporte = $request->tipo_reporte;
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $datosGrafica = $this->obtenerDatosGrafica($tipoReporte, $fechaInicio, $fechaFin);
        $resumen = $this->obtenerResumen($tipoReporte, $fechaInicio, $fechaFin);

        // Generar CSV
        $csv = "Reporte de $tipoReporte\n";
        $csv .= "Fecha Inicio: $fechaInicio\n";
        $csv .= "Fecha Fin: $fechaFin\n\n";
        
        $csv .= "Resumen:\n";
        foreach ($resumen as $key => $value) {
            if ($key !== 'titulo') {
                $csv .= "$key: $value\n";
            }
        }
        
        $csv .= "\nDatos de Gráfica:\n";
        $csv .= "Labels: " . implode(', ', $datosGrafica['labels']) . "\n";
        $csv .= "Data: " . implode(', ', $datosGrafica['data']) . "\n";

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="reporte_' . $tipoReporte . '_' . $fechaInicio . '_' . $fechaFin . '.csv"');
    }
}
