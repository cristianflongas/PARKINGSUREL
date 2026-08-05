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
            'cedula' => 'required|string|max:20|unique:users,cedula',
            'propietario' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:100|unique:users,correo',
            'marca_modelo' => 'required|string|max:100',
            'tipo' => 'nullable|string|max:30',
        ]);

        $placa = strtoupper(trim($request->placa));
        $cedula = strtoupper(trim($request->cedula));
        $correo = trim((string) $request->correo ?: $cedula . '@parkingsure.com');

        DB::transaction(function () use ($request, $placa, $cedula, $correo) {
            $user = User::updateOrCreate(
                ['cedula' => $cedula],
                [
                    'cedula' => $cedula,
                    'nombre' => trim($request->propietario),
                    'telefono' => trim((string) $request->telefono),
                    'correo' => $correo,
                ]
            );

            $cliente = Cliente::firstOrCreate(['cedula_users' => $user->cedula]);

            $marcaModelo = trim((string) $request->marca_modelo);
            $partes = preg_split('/\s+/', $marcaModelo, 2);
            $marca = $partes[0] ?? 'Generico';
            $modelo = count($partes) > 1 ? $partes[1] : 'Estandar';

            Vehiculo::updateOrCreate(
                ['placa' => $placa],
                [
                    'placa' => $placa,
                    'id_cliente' => $cliente->id_cliente,
                    'marca' => $marca,
                    'modelo' => $modelo,
                    'color' => $request->tipo ?? 'Estandar',
                ]
            );
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
            'cedula' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:100',
        ]);

        $vehiculo = Vehiculo::findOrFail($placa);

        DB::transaction(function () use ($vehiculo, $request) {
            $vehiculo->update([
                'marca' => $request->marca,
                'modelo' => $request->modelo,
                'color' => $request->color,
            ]);

            if ($vehiculo->cliente && $vehiculo->cliente->user) {
                $user = $vehiculo->cliente->user;
                $user->update([
                    'nombre' => $request->propietario,
                    'telefono' => $request->telefono ?? $user->telefono,
                    'correo' => $request->correo ?? $user->correo,
                ]);

                if ($request->filled('cedula') && $request->cedula !== $user->cedula) {
                    $user->cedula = $request->cedula;
                    $user->save();
                }
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

    /**
     * API: Obtener información de un vehículo específico
     */
    public function obtenerVehiculo($placa)
    {
        $vehiculo = Vehiculo::with(['cliente.user', 'entradas' => function ($q) {
            $q->where('estado', 'ACTIVO')->first();
        }])->find($placa);

        if (!$vehiculo) {
            return response()->json(['error' => 'Vehículo no encontrado'], 404);
        }

        return response()->json([
            'placa' => $vehiculo->placa,
            'marca' => $vehiculo->marca,
            'modelo' => $vehiculo->modelo,
            'color' => $vehiculo->color,
            'propietario' => [
                'nombre' => $vehiculo->cliente->user->nombre ?? 'Cliente General',
                'cedula' => $vehiculo->cliente->user->cedula ?? '',
                'telefono' => $vehiculo->cliente->user->telefono ?? '',
                'correo' => $vehiculo->cliente->user->correo ?? ''
            ],
            'esta_adentro' => $vehiculo->entradas()->where('estado', 'ACTIVO')->exists()
        ]);
    }

    /**
     * API: Listar vehículos para select/autocomplete
     */
    public function listarVehiculos(Request $request)
    {
        $query = Vehiculo::with(['cliente.user'])
            ->select('placa', 'marca', 'modelo', 'color', 'id_cliente');

        // Filtrar solo vehículos que estén afuera para registro de entrada
        if ($request->get('solo_afuera')) {
            $query->whereDoesntHave('entradas', function ($q) {
                $q->where('estado', 'ACTIVO');
            });
        }

        // Filtrar solo vehículos que estén adentro para registro de salida
        if ($request->get('solo_adentro')) {
            $query->whereHas('entradas', function ($q) {
                $q->where('estado', 'ACTIVO');
            });
        }

        // Buscar por placa
        if ($request->filled('buscar')) {
            $query->where('placa', 'LIKE', '%' . $request->buscar . '%');
        }

        $vehiculos = $query->get()->map(function ($vehiculo) {
            $nombrePropietario = $vehiculo->cliente->user->nombre ?? 'Cliente General';
            return [
                'placa' => $vehiculo->placa,
                'marca' => $vehiculo->marca,
                'modelo' => $vehiculo->modelo,
                'color' => $vehiculo->color,
                'propietario' => $nombrePropietario,
                'display' => "{$vehiculo->placa} - {$vehiculo->marca} {$vehiculo->modelo} ({$nombrePropietario})"
            ];
        });

        return response()->json($vehiculos);
    }
}
