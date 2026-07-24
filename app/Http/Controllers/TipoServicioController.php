<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipoServicio;

class TipoServicioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $servicios = TipoServicio::orderBy('id_tipo_servicio', 'desc')->get();
        return view('servicios', ['active' => 'servicios', 'servicios' => $servicios]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_tipo_servicio' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'tarifa' => 'required|numeric|min:0',
            'estado' => 'required|string|in:ACTIVO,INACTIVO',
        ]);

        try {
            TipoServicio::create([
                'nombre_tipo_servicio' => $request->nombre_tipo_servicio,
                'descripcion' => $request->descripcion,
                'tarifa' => $request->tarifa,
                'estado' => $request->estado,
            ]);

            return redirect()->route('servicios')->with('success', 'Servicio creado correctamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al crear el servicio: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $servicio = TipoServicio::findOrFail($id);
        return response()->json($servicio);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nombre_tipo_servicio' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'tarifa' => 'required|numeric|min:0',
            'estado' => 'required|string|in:ACTIVO,INACTIVO',
        ]);

        try {
            $servicio = TipoServicio::findOrFail($id);
            $servicio->update([
                'nombre_tipo_servicio' => $request->nombre_tipo_servicio,
                'descripcion' => $request->descripcion,
                'tarifa' => $request->tarifa,
                'estado' => $request->estado,
            ]);

            return redirect()->route('servicios')->with('success', 'Servicio actualizado correctamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar el servicio: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $servicio = TipoServicio::findOrFail($id);
            $servicio->delete();

            return redirect()->route('servicios')->with('success', 'Servicio eliminado correctamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el servicio: ' . $e->getMessage());
        }
    }
}
