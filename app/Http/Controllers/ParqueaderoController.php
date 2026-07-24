<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Entrada;
use App\Models\Factura;
use App\Models\Modulo;
use App\Models\Personal;
use App\Models\Salida;
use App\Models\TipoServicio;
use App\Models\User;
use App\Models\Vehiculo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ParqueaderoController extends Controller
{
    public function index()
    {
        // 1. Inicialización automática de módulos si la tabla está vacía
        if (Modulo::count() === 0) {
            $niveles = ['A', 'B', 'C'];
            foreach ($niveles as $nivel) {
                for ($i = 1; $i <= 20; $i++) {
                    $codigo = $nivel . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                    Modulo::create([
                        'ubicacion' => $codigo,
                        'estado' => ($i % 7 == 0) ? 'MANTENIMIENTO' : 'DISPONIBLE',
                    ]);
                }
            }
        }

        // 2. Inicialización de tipos de servicio por defecto si está vacía
        if (TipoServicio::count() === 0) {
            TipoServicio::create(['nombre_tipo_servicio' => 'Tarifa Auto / Hora', 'tarifa' => 2.00, 'estado' => 'ACTIVO']);
            TipoServicio::create(['nombre_tipo_servicio' => 'Tarifa Moto / Hora', 'tarifa' => 1.00, 'estado' => 'ACTIVO']);
            TipoServicio::create(['nombre_tipo_servicio' => 'Tarifa Camioneta / Hora', 'tarifa' => 2.50, 'estado' => 'ACTIVO']);
        }

        // 3. Estadísticas generales de Módulos
        $totalDisponibles = Modulo::where('estado', 'DISPONIBLE')->count();
        $totalOcupados = Modulo::where('estado', 'OCUPADO')->count();
        $totalMantenimiento = Modulo::where('estado', 'MANTENIMIENTO')->count();

        // 4. Ocupación por Niveles de Módulos
        $nivelA_Ocupados = Modulo::where('ubicacion', 'LIKE', 'A-%')->where('estado', 'OCUPADO')->count();
        $nivelB_Ocupados = Modulo::where('ubicacion', 'LIKE', 'B-%')->where('estado', 'OCUPADO')->count();
        $nivelC_Ocupados = Modulo::where('ubicacion', 'LIKE', 'C-%')->where('estado', 'OCUPADO')->count();

        // 5. Carga de Módulos del Nivel A (principal) para el mapa interactivo
        $modulosNivelA = Modulo::where('ubicacion', 'LIKE', 'A-%')->orderBy('id_modulo')->get();

        // 6. Módulos disponibles y tipos de servicios para el modal de Entrada
        $modulosLibres = Modulo::where('estado', 'DISPONIBLE')->orderBy('ubicacion')->get();
        $tiposServicio = TipoServicio::where('estado', 'ACTIVO')->get();

        // 7. Entradas activas asociadas a cada módulo ocupado
        $entradasActivas = Entrada::with(['vehiculo.cliente.user', 'modulo', 'tipoServicio'])
            ->where('estado', 'ACTIVO')
            ->get()
            ->keyBy('id_modulo');

        return view('parqueadero', [
            'active' => 'parqueadero',
            'disponibles' => $totalDisponibles,
            'ocupados' => $totalOcupados,
            'mantenimiento' => $totalMantenimiento,
            'nivelA_Ocupados' => $nivelA_Ocupados,
            'nivelB_Ocupados' => $nivelB_Ocupados,
            'nivelC_Ocupados' => $nivelC_Ocupados,
            'modulosNivelA' => $modulosNivelA,
            'modulosLibres' => $modulosLibres,
            'tiposServicio' => $tiposServicio,
            'entradasActivas' => $entradasActivas,
        ]);
    }

    public function registrarEntrada(Request $request)
    {
        $request->validate([
            'placa' => 'required|string|max:8',
            'id_modulo' => 'required|exists:modulo,id_modulo',
            'id_tipo_servicio' => 'required|exists:tipo_servicio,id_tipo_servicio',
            'propietario' => 'nullable|string|max:100',
            'marca_modelo' => 'nullable|string|max:100',
            'foto_base64' => 'nullable|string',
            'foto_archivo' => 'nullable|image|max:5120',
        ]);

        $placa = strtoupper(trim($request->placa));

        // Verificar si el vehículo ya tiene un ingreso activo
        $ingresoExistente = Entrada::where('placa', $placa)->where('estado', 'ACTIVO')->first();
        if ($ingresoExistente) {
            return redirect()->back()->withErrors(['placa' => "El vehículo con placa {$placa} ya registra un ingreso activo en el Módulo {$ingresoExistente->modulo->ubicacion}."]);
        }

        // Procesar Captura de Foto de Entrada
        $fotoEntradaPath = null;
        if ($request->filled('foto_base64')) {
            try {
                $imgData = $request->foto_base64;
                $imgData = str_replace('data:image/jpeg;base64,', '', $imgData);
                $imgData = str_replace('data:image/png;base64,', '', $imgData);
                $imgData = str_replace(' ', '+', $imgData);
                $data = base64_decode($imgData);

                $filename = 'entrada_' . preg_replace('/[^A-Za-z0-9]/', '', $placa) . '_' . time() . '.jpg';
                $directory = public_path('uploads/entradas');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                file_put_contents($directory . '/' . $filename, $data);
                $fotoEntradaPath = 'uploads/entradas/' . $filename;
            } catch (\Exception $e) {
                // Si falla la conversión de base64, continuar sin detener la transacción
                $fotoEntradaPath = null;
            }
        } elseif ($request->hasFile('foto_archivo')) {
            $file = $request->file('foto_archivo');
            $filename = 'entrada_' . preg_replace('/[^A-Za-z0-9]/', '', $placa) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $directory = public_path('uploads/entradas');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            $file->move($directory, $filename);
            $fotoEntradaPath = 'uploads/entradas/' . $filename;
        }

        DB::transaction(function () use ($request, $placa, $fotoEntradaPath) {
            // 1. Obtener o crear Cliente por defecto si no existe
            $userAuth = auth()->user();
            $cliente = Cliente::first();
            if (!$cliente) {
                // La tabla users usa 'correo' y 'nombre' (no email/name)
                $user = User::firstOrCreate(
                    ['correo' => 'cliente.general@parkingsure.com'],
                    [
                        'cedula' => '9999999999',
                        'nombre' => 'Cliente General',
                        'telefono' => '0000000000',
                    ]
                );
                $cliente = Cliente::create(['cedula_users' => $user->cedula]);
            }

            // 2. Obtener o crear el Vehículo
            $vehiculo = Vehiculo::find($placa);
            if (!$vehiculo) {
                $parts = explode(' ', $request->marca_modelo ?? 'Generico Estandar');
                $marca = $parts[0] ?? 'Generico';
                $modelo = isset($parts[1]) ? implode(' ', array_slice($parts, 1)) : 'Estandar';

                $vehiculo = Vehiculo::create([
                    'placa' => $placa,
                    'id_cliente' => $cliente->id_cliente,
                    'marca' => $marca,
                    'modelo' => $modelo,
                ]);
            }

            // 3. Obtener el id_personal del operador actual
            $personal = Personal::where('cedula', $userAuth->cedula)->first();
            $idPersonal = $personal ? $personal->id_personal : 1;

            // 4. Registrar Entrada con foto_entrada
            Entrada::create([
                'placa' => $placa,
                'id_modulo' => $request->id_modulo,
                'id_personal' => $idPersonal,
                'id_tipo_servicio' => $request->id_tipo_servicio,
                'fecha_hora_entrada' => now(),
                'estado' => 'ACTIVO',
                'foto_entrada' => $fotoEntradaPath,
            ]);

            // 5. Cambiar el estado del Módulo a OCUPADO
            $modulo = Modulo::find($request->id_modulo);
            $modulo->update(['estado' => 'OCUPADO']);
        });

        return redirect()->route('parqueadero')->with('success', "Ingreso del vehículo {$placa} registrado correctamente con fotografía de entrada.");
    }

    public function registrarSalida(Request $request)
    {
        $request->validate([
            'id_entrada'  => 'required|exists:entrada,id_entrada',
            'metodo_pago' => 'required|string|in:EFECTIVO,TARJETA,TRANSFERENCIA',
            'foto_base64_salida'  => 'nullable|string',
            'foto_archivo_salida' => 'nullable|image|max:5120',
        ]);

        $entrada = Entrada::with(['tipoServicio', 'modulo'])->findOrFail($request->id_entrada);

        if ($entrada->estado !== 'ACTIVO') {
            return redirect()->back()->withErrors(['error' => 'El ingreso seleccionado ya se encuentra finalizado.']);
        }

        $fechaSalida = now();
        $horasTranscurridas = ceil(max(1, $fechaSalida->diffInMinutes($entrada->fecha_hora_entrada) / 60));
        $tarifaHora = $entrada->tipoServicio ? $entrada->tipoServicio->tarifa : 2.00;
        $montoTotal = $horasTranscurridas * $tarifaHora;

        // Procesar foto de salida
        $fotoSalidaPath = null;
        $placa = $entrada->placa;

        if ($request->filled('foto_base64_salida')) {
            try {
                $imgData = $request->foto_base64_salida;
                $imgData = str_replace(['data:image/jpeg;base64,', 'data:image/png;base64,'], '', $imgData);
                $imgData = str_replace(' ', '+', $imgData);
                $data = base64_decode($imgData);

                $filename = 'salida_' . preg_replace('/[^A-Za-z0-9]/', '', $placa) . '_' . time() . '.jpg';
                $directory = public_path('uploads/salidas');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                file_put_contents($directory . '/' . $filename, $data);
                $fotoSalidaPath = 'uploads/salidas/' . $filename;
            } catch (\Exception $e) {
                $fotoSalidaPath = null;
            }
        } elseif ($request->hasFile('foto_archivo_salida')) {
            $file = $request->file('foto_archivo_salida');
            $filename = 'salida_' . preg_replace('/[^A-Za-z0-9]/', '', $placa) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $directory = public_path('uploads/salidas');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            $file->move($directory, $filename);
            $fotoSalidaPath = 'uploads/salidas/' . $filename;
        }

        DB::transaction(function () use ($entrada, $fechaSalida, $montoTotal, $request, $fotoSalidaPath) {
            // 1. Registrar Salida con foto
            $salida = Salida::create([
                'id_entrada'       => $entrada->id_entrada,
                'fecha_hora_salida' => $fechaSalida,
                'foto_salida'      => $fotoSalidaPath,
            ]);

            // 2. Generar Factura
            Factura::create([
                'id_salida'    => $salida->id_salida,
                'fecha_emision' => $fechaSalida,
                'monto_total'  => $montoTotal,
                'metodo_pago'  => $request->metodo_pago,
                'estado_pago'  => 'PAGADO',
            ]);

            // 3. Actualizar estado de Entrada a FINALIZADO
            $entrada->update(['estado' => 'FINALIZADO']);

            // 4. Liberar el Módulo (DISPONIBLE)
            if ($entrada->modulo) {
                $entrada->modulo->update(['estado' => 'DISPONIBLE']);
            }
        });

        return redirect()->route('parqueadero')->with('success', "Salida procesada con éxito. Factura de $" . number_format($montoTotal, 2) . " generada y Módulo liberado.");
    }
}
