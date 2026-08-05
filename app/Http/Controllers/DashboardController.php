<?php

namespace App\Http\Controllers;

use App\Models\Entrada;
use App\Models\Factura;
use App\Models\Modulo;
use App\Models\Personal;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Módulos Disponibles
        $disponibles = Modulo::where('estado', 'DISPONIBLE')->count();

        // 2. Vehículos Estacionados Activos
        $estacionados = Entrada::where('estado', 'ACTIVO')->count();

        // 3. Ingresos Hoy
        $ingresosHoy = Factura::whereDate('fecha_emision', today())
            ->where('estado_pago', 'PAGADO')
            ->sum('monto_total');

        // 4. Personal Activo
        $personalActivo = Personal::count();

        // 5. Últimos Ingresos Registrados
        $ultimosIngresos = Entrada::with(['vehiculo.cliente.user', 'modulo', 'tipoServicio'])
            ->where('estado', 'ACTIVO')
            ->orderBy('fecha_hora_entrada', 'desc')
            ->take(5)
            ->get();

        // 6. Últimos Servicios Completados (Nuevos)
        $ultimosServicios = Entrada::with(['vehiculo.cliente.user', 'modulo', 'tipoServicio', 'salida.factura'])
            ->whereHas('salida')
            ->orderBy('updated_at', 'desc')
            ->take(8)
            ->get();

        return view('dashboard', [
            'active' => 'dashboard',
            'disponibles' => $disponibles,
            'estacionados' => $estacionados,
            'ingresosHoy' => $ingresosHoy,
            'personalActivo' => $personalActivo,
            'ultimosIngresos' => $ultimosIngresos,
            'ultimosServicios' => $ultimosServicios,
        ]);
    }
}
