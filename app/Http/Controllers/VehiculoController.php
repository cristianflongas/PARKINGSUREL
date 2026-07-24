<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VehiculoController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehiculo::with(['cliente.user', 'entradas' => function ($q) {
            $q->orderBy('fecha_hora_entrada', 'desc');
        }]);

        // Buscador por placa o propietario
        if ($request->filled('buscar')) {
            $buscar = trim($request->buscar);
            $query->where(function ($q) use ($buscar) {
                $q->where('placa', 'LIKE', "%{$buscar}%")
                  ->orWhere('marca', 'LIKE', "%{$buscar}%")
                  ->orWhere('modelo', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('cliente.user', function ($qu) use ($buscar) {
                      $qu->where('name', 'LIKE', "%{$buscar}%");
                  });
            });
        }

        // Filtro por Estado (Adentro / Afuera)
        if ($request->filled('estado') && $request->estado !== 'Todos los estados') {
            if ($request->estado === 'Adentro') {
                $query->whereHas('entradas', function ($q) {
                    $q->where('estado', 'ACTIVO');
                });
            } elseif ($request->estado === 'Afuera') {
                $query->whereDoesntHave('entradas', function ($q) {
                    $q->where('estado', 'ACTIVO');
                });
            }
        }

        $vehiculos = $query->orderBy('updated_at', 'desc')->get();

        return view('vehiculos', [
            'active' => 'vehiculos',
            'vehiculos' => $vehiculos,
            'buscar' => $request->buscar,
            'estadoFiltro' => $request->estado,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'placa' => 'required|string|max:8|unique:vehiculo,placa',
            'propietario' => 'required|string|max:100',
            'marca_modelo' => 'required|string|max:100',
            'tipo' => 'nullable|string|max:30',
        ]);

        $placa = strtoupper(trim($request->placa));

        DB::transaction(function () use ($request, $placa) {
            // Obtener o crear Cliente
            $user = User::firstOrCreate(
                ['email' => strtolower(str_replace(' ', '', $request->propietario)) . '@parkingsure.com'],
                [
                    'name' => $request->propietario,
                    'cedula' => rand(1000000000, 9999999999),
                    'password' => Hash::make('password'),
                ]
            );

            $cliente = Cliente::firstOrCreate(['cedula_users' => $user->cedula]);

            $parts = explode(' ', $request->marca_modelo);
            $marca = $parts[0] ?? 'Generico';
            $modelo = isset($parts[1]) ? implode(' ', array_slice($parts, 1)) : 'Estandar';

            Vehiculo::create([
                'placa' => $placa,
                'id_cliente' => $cliente->id_cliente,
                'marca' => $marca,
                'modelo' => $modelo,
                'color' => $request->tipo ?? 'Estandar',
            ]);
        });

        return redirect()->route('vehiculos')->with('success', "Vehículo {$placa} registrado exitosamente.");
    }

    public function update(Request $request, $placa)
    {
        $request->validate([
            'propietario' => 'required|string|max:100',
            'marca' => 'required|string|max:30',
            'modelo' => 'required|string|max:30',
            'color' => 'nullable|string|max:20',
        ]);

        $vehiculo = Vehiculo::findOrFail($placa);

        DB::transaction(function () use ($vehiculo, $request) {
            $vehiculo->update([
                'marca' => $request->marca,
                'modelo' => $request->modelo,
                'color' => $request->color,
            ]);

            if ($vehiculo->cliente && $vehiculo->cliente->user) {
                $vehiculo->cliente->user->update(['name' => $request->propietario]);
            }
        });

        return redirect()->route('vehiculos')->with('success', "Datos del vehículo {$placa} actualizados.");
    }

    public function destroy($placa)
    {
        $vehiculo = Vehiculo::findOrFail($placa);

        if ($vehiculo->entradas()->where('estado', 'ACTIVO')->exists()) {
            return redirect()->back()->withErrors(['error' => "No se puede eliminar el vehículo {$placa} porque se encuentra estacionado actualmente."]);
        }

        $vehiculo->delete();

        return redirect()->route('vehiculos')->with('success', "Vehículo {$placa} eliminado del registro.");
    }
}
