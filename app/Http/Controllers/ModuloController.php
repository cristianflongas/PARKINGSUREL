<?php

namespace App\Http\Controllers;

use App\Models\Entrada;
use App\Models\Modulo;
use App\Models\TipoServicio;
use Illuminate\Http\Request;

class ModuloController extends Controller
{
    public function index()
    {
        $modulos = Modulo::with(['entradas' => function ($q) {
            $q->where('estado', 'ACTIVO')->latest();
        }])->orderBy('ubicacion')->get();

        $tiposServicio = TipoServicio::all();

        return view('modulos', [
            'active' => 'modulos',
            'modulos' => $modulos,
            'tiposServicio' => $tiposServicio,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ubicacion' => 'required|string|max:20|unique:modulo,ubicacion',
            'estado' => 'required|in:DISPONIBLE,OCUPADO,MANTENIMIENTO',
        ]);

        Modulo::create([
            'ubicacion' => strtoupper(trim($request->ubicacion)),
            'estado' => $request->estado,
        ]);

        return redirect()->route('modulos')->with('success', 'Módulo creado exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'ubicacion' => 'required|string|max:20|unique:modulo,ubicacion,' . $id . ',id_modulo',
            'estado' => 'required|in:DISPONIBLE,OCUPADO,MANTENIMIENTO',
        ]);

        $modulo = Modulo::findOrFail($id);

        // Verificar si el módulo está ocupado antes de cambiar estado
        if ($request->estado !== 'OCUPADO' && $modulo->estado === 'OCUPADO') {
            $entradaActiva = $modulo->entradas()->where('estado', 'ACTIVO')->first();
            if ($entradaActiva) {
                return redirect()->back()->withErrors(['error' => 'No se puede cambiar el estado del módulo porque tiene un vehículo estacionado actualmente.']);
            }
        }

        $modulo->update([
            'ubicacion' => strtoupper(trim($request->ubicacion)),
            'estado' => $request->estado,
        ]);

        return redirect()->route('modulos')->with('success', 'Módulo actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $modulo = Modulo::findOrFail($id);

        // Verificar si el módulo tiene entradas activas
        if ($modulo->entradas()->where('estado', 'ACTIVO')->exists()) {
            return redirect()->back()->withErrors(['error' => 'No se puede eliminar el módulo porque tiene un vehículo estacionado actualmente.']);
        }

        $modulo->delete();

        return redirect()->route('modulos')->with('success', 'Módulo eliminado exitosamente.');
    }

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:DISPONIBLE,OCUPADO,MANTENIMIENTO',
        ]);

        $modulo = Modulo::findOrFail($id);

        // Verificar si el módulo está ocupado antes de cambiar estado
        if ($request->estado !== 'OCUPADO' && $modulo->estado === 'OCUPADO') {
            $entradaActiva = $modulo->entradas()->where('estado', 'ACTIVO')->first();
            if ($entradaActiva) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede cambiar el estado del módulo porque tiene un vehículo estacionado actualmente.'
                ], 400);
            }
        }

        $modulo->update(['estado' => $request->estado]);

        return response()->json([
            'success' => true,
            'message' => 'Estado del módulo actualizado exitosamente.',
            'estado' => $modulo->estado
        ]);
    }
}
