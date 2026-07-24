<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        $query = Factura::with([
            'salida.entrada.vehiculo.cliente.user',
            'salida.entrada.tipoServicio',
            'salida.entrada.modulo'
        ]);

        // Filtro por Fecha Inicio y Fin
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_emision', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_emision', '<=', $request->fecha_fin);
        }

        // Filtro por Estado
        if ($request->filled('estado') && $request->estado !== 'Todos') {
            $query->where('estado_pago', strtoupper($request->estado));
        }

        $facturas = $query->orderBy('fecha_emision', 'desc')->get();

        // Totales de resumen financiero reales
        $pagosHoy = Factura::whereDate('fecha_emision', today())
            ->where('estado_pago', 'PAGADO')
            ->sum('monto_total');
        $transaccionesHoy = Factura::whereDate('fecha_emision', today())
            ->where('estado_pago', 'PAGADO')
            ->count();

        $pagosSemana = Factura::whereBetween('fecha_emision', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('estado_pago', 'PAGADO')
            ->sum('monto_total');
        $transaccionesSemana = Factura::whereBetween('fecha_emision', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('estado_pago', 'PAGADO')
            ->count();

        $pagosMes = Factura::whereMonth('fecha_emision', now()->month)
            ->whereYear('fecha_emision', now()->year)
            ->where('estado_pago', 'PAGADO')
            ->sum('monto_total');
        $transaccionesMes = Factura::whereMonth('fecha_emision', now()->month)
            ->whereYear('fecha_emision', now()->year)
            ->where('estado_pago', 'PAGADO')
            ->count();

        $pendientesMonto = Factura::where('estado_pago', 'PENDIENTE')->sum('monto_total');
        $pendientesCantidad = Factura::where('estado_pago', 'PENDIENTE')->count();

        // Lista de facturas pendientes para el modal de registrar pago
        $facturasPendientes = Factura::with('salida.entrada.vehiculo')
            ->where('estado_pago', 'PENDIENTE')
            ->get();

        return view('pagos', [
            'active' => 'pagos',
            'facturas' => $facturas,
            'pagosHoy' => $pagosHoy,
            'transaccionesHoy' => $transaccionesHoy,
            'pagosSemana' => $pagosSemana,
            'transaccionesSemana' => $transaccionesSemana,
            'pagosMes' => $pagosMes,
            'transaccionesMes' => $transaccionesMes,
            'pendientesMonto' => $pendientesMonto,
            'pendientesCantidad' => $pendientesCantidad,
            'facturasPendientes' => $facturasPendientes,
            'fechaInicio' => $request->fecha_inicio,
            'fechaFin' => $request->fecha_fin,
            'estadoFiltro' => $request->estado,
        ]);
    }

    public function procesarPago(Request $request)
    {
        $request->validate([
            'id_factura' => 'required|exists:factura,id_factura',
            'monto_total' => 'required|numeric|min:0',
            'metodo_pago' => 'required|string',
        ]);

        $factura = Factura::findOrFail($request->id_factura);
        $factura->update([
            'monto_total' => $request->monto_total,
            'metodo_pago' => $request->metodo_pago,
            'estado_pago' => 'PAGADO',
        ]);

        return redirect()->route('pagos')->with('success', "Pago de Factura #FAC-{$factura->id_factura} registrado con éxito.");
    }

    public function comprobante($id)
    {
        $factura = Factura::with([
            'salida.entrada.vehiculo.cliente.user',
            'salida.entrada.tipoServicio',
            'salida.entrada.modulo'
        ])->findOrFail($id);

        return response()->json($factura);
    }
}
