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
use App\Services\PlacaOCRService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ParqueaderoController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    //  Vista principal unificada: mapa de módulos + CRUD de módulos
    // ─────────────────────────────────────────────────────────────────────────
    public function index()
    {
        // Inicialización de tipos de servicio por defecto
        if (TipoServicio::count() === 0) {
            TipoServicio::create(['nombre_tipo_servicio' => 'Tarifa Auto / Hora',      'tarifa' => 2.00, 'estado' => 'ACTIVO']);
            TipoServicio::create(['nombre_tipo_servicio' => 'Tarifa Moto / Hora',      'tarifa' => 1.00, 'estado' => 'ACTIVO']);
            TipoServicio::create(['nombre_tipo_servicio' => 'Tarifa Camioneta / Hora', 'tarifa' => 2.50, 'estado' => 'ACTIVO']);
        }

        // Todos los módulos ordenados por ubicación
        $modulos = Modulo::orderBy('ubicacion')->get();

        // Estadísticas
        $disponibles   = $modulos->where('estado', 'DISPONIBLE')->count();
        $ocupados      = $modulos->where('estado', 'OCUPADO')->count();
        $mantenimiento = $modulos->where('estado', 'MANTENIMIENTO')->count();

        // Entradas activas indexadas por id_modulo
        $entradasActivas = Entrada::with(['vehiculo.cliente.user', 'modulo', 'tipoServicio'])
            ->where('estado', 'ACTIVO')
            ->get()
            ->keyBy('id_modulo');

        // Módulos libres y tipos de servicio para el modal de entrada
        $modulosLibres = $modulos->where('estado', 'DISPONIBLE')->values();
        $tiposServicio = TipoServicio::where('estado', 'ACTIVO')->get();

        // Vehículos registrados con datos del propietario (para select manual)
        $vehiculosRegistrados = Vehiculo::with(['cliente.user'])
            ->orderBy('placa')
            ->get()
            ->map(function ($v) {
                $user = $v->cliente?->user;

                return [
                    'placa'       => $v->placa,
                    'marca'       => $v->marca ?? '',
                    'modelo'      => $v->modelo ?? '',
                    'cedula'      => $user?->cedula ?? '',
                    'propietario' => $user?->nombre ?? 'Cliente General',
                ];
            });

        return view('parqueadero', [
            'active'               => 'parqueadero',
            'modulos'              => $modulos,
            'disponibles'          => $disponibles,
            'ocupados'             => $ocupados,
            'mantenimiento'        => $mantenimiento,
            'entradasActivas'      => $entradasActivas,
            'modulosLibres'        => $modulosLibres,
            'tiposServicio'        => $tiposServicio,
            'vehiculosRegistrados' => $vehiculosRegistrados,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  CRUD de Módulos (integrado en la misma vista)
    // ─────────────────────────────────────────────────────────────────────────
    public function storeModulo(Request $request)
    {
        $request->validate([
            'ubicacion' => 'required|string|max:20|unique:modulo,ubicacion',
            'estado'    => 'required|in:DISPONIBLE,OCUPADO,MANTENIMIENTO',
        ]);

        Modulo::create([
            'ubicacion' => strtoupper(trim($request->ubicacion)),
            'estado'    => $request->estado,
        ]);

        return redirect()->route('parqueadero')->with('success', 'Módulo creado exitosamente.');
    }

    public function updateModulo(Request $request, $id)
    {
        $request->validate([
            'ubicacion' => 'required|string|max:20|unique:modulo,ubicacion,' . $id . ',id_modulo',
            'estado'    => 'required|in:DISPONIBLE,MANTENIMIENTO',
        ]);

        $modulo = Modulo::findOrFail($id);

        // No se puede editar un módulo con vehículo activo
        if ($modulo->estado === 'OCUPADO') {
            $entradaActiva = $modulo->entradas()->where('estado', 'ACTIVO')->first();
            if ($entradaActiva) {
                return redirect()->back()->withErrors(['error' => 'No se puede modificar el módulo porque tiene un vehículo estacionado actualmente.']);
            }
        }

        $modulo->update([
            'ubicacion' => strtoupper(trim($request->ubicacion)),
            'estado'    => $request->estado,
        ]);

        return redirect()->route('parqueadero')->with('success', 'Módulo actualizado exitosamente.');
    }

    public function destroyModulo($id)
    {
        $modulo = Modulo::findOrFail($id);

        if ($modulo->entradas()->where('estado', 'ACTIVO')->exists()) {
            return redirect()->back()->withErrors(['error' => 'No se puede eliminar el módulo porque tiene un vehículo estacionado actualmente.']);
        }

        $modulo->delete();

        return redirect()->route('parqueadero')->with('success', 'Módulo eliminado exitosamente.');
    }

    public function cambiarEstadoModulo(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:DISPONIBLE,MANTENIMIENTO',
        ]);

        $modulo = Modulo::findOrFail($id);

        if ($modulo->estado === 'OCUPADO') {
            return response()->json([
                'success' => false,
                'message' => 'El módulo está ocupado. El estado se libera automáticamente al registrar la salida del vehículo.'
            ], 400);
        }

        $modulo->update(['estado' => $request->estado]);

        return response()->json([
            'success' => true,
            'message' => 'Estado del módulo actualizado exitosamente.',
            'estado'  => $modulo->estado,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Operaciones de Entrada y Salida
    // ─────────────────────────────────────────────────────────────────────────
    public function registrarEntrada(Request $request)
    {
        $request->validate([
            'placa'            => 'required|string|max:8',
            'cedula'           => 'nullable|string|max:20',
            'id_modulo'        => 'required|exists:modulo,id_modulo',
            'id_tipo_servicio' => 'required|exists:tipo_servicio,id_tipo_servicio',
            'propietario'      => 'nullable|string|max:100',
            'marca_modelo'     => 'nullable|string|max:100',
            'foto_base64'      => 'nullable|string',
            'foto_archivo'     => 'nullable|image|max:5120',
        ]);

        $placa = strtoupper(trim($request->placa));

        // Verificar si el módulo está disponible
        $modulo = Modulo::findOrFail($request->id_modulo);
        if ($modulo->estado !== 'DISPONIBLE') {
            return redirect()->back()->withErrors(['error' => "El módulo {$modulo->ubicacion} no está disponible para registrar entradas."]);
        }

        // Verificar si el vehículo ya tiene un ingreso activo
        $ingresoExistente = Entrada::where('placa', $placa)->where('estado', 'ACTIVO')->first();
        if ($ingresoExistente) {
            return redirect()->back()->withErrors(['placa' => "El vehículo con placa {$placa} ya registra un ingreso activo en el Módulo {$ingresoExistente->modulo->ubicacion}."]);
        }

        $vehiculoExistente = Vehiculo::with(['cliente.user'])->where('placa', $placa)->first();
        if ($vehiculoExistente) {
            $request->merge([
                'cedula' => $vehiculoExistente->cliente?->user?->cedula ?? $request->cedula,
                'propietario' => $vehiculoExistente->cliente?->user?->nombre ?? $request->propietario ?? 'Cliente General',
                'marca_modelo' => trim(($vehiculoExistente->marca ?? '') . ' ' . ($vehiculoExistente->modelo ?? '')) ?: ($request->marca_modelo ?? 'Generico Estandar'),
            ]);
        }

        // Procesar foto de entrada
        $fotoEntradaPath = $this->procesarFoto($request, 'foto_base64', 'foto_archivo', 'entradas', $placa);

        DB::transaction(function () use ($request, $placa, $fotoEntradaPath) {
            $userAuth = Auth::user();

            $cliente = null;
            if ($request->filled('cedula')) {
                $userCliente = User::updateOrCreate(
                    ['cedula' => strtoupper(trim($request->cedula))],
                    [
                        'cedula' => strtoupper(trim($request->cedula)),
                        'nombre' => trim((string) ($request->propietario ?? 'Cliente General')),
                        'telefono' => trim((string) ($request->telefono ?? '')) ?: null,
                        'correo' => trim((string) ($request->correo ?? '')) ?: strtolower(trim((string) ($request->cedula))) . '@parkingsure.com',
                    ]
                );

                $cliente = Cliente::firstOrCreate(['cedula_users' => $userCliente->cedula]);
            } else {
                $cliente = Cliente::first();
            }

            if (!$cliente) {
                $user = User::firstOrCreate(
                    ['correo' => 'cliente.general@parkingsure.com'],
                    ['cedula' => '9999999999', 'nombre' => 'Cliente General', 'telefono' => '0000000000']
                );
                $cliente = Cliente::create(['cedula_users' => $user->cedula]);
            }

            $vehiculo = Vehiculo::where('placa', $placa)->first();
            if (!$vehiculo) {
                $marcaModelo = trim((string) ($request->marca_modelo ?? 'Generico Estandar'));
                $partes = preg_split('/\s+/', $marcaModelo, 2);
                $marca = $partes[0] ?? 'Generico';
                $modelo = count($partes) > 1 ? $partes[1] : 'Estandar';

                $vehiculo = Vehiculo::create([
                    'placa'      => $placa,
                    'id_cliente' => $cliente->id_cliente,
                    'marca'      => $marca,
                    'modelo'     => $modelo,
                    'color'      => $request->tipo ?? 'Estandar',
                ]);
            }

            $idBuscado = $userAuth?->id_personal ?? $userAuth?->cedula ?? $userAuth?->id ?? 1;
            $personal  = Personal::find($idBuscado);
            $idPersonal = $personal ? $personal->id_personal : 1;

            Entrada::create([
                'placa'              => $placa,
                'id_modulo'          => $request->id_modulo,
                'id_personal'        => $idPersonal,
                'id_tipo_servicio'   => $request->id_tipo_servicio,
                'fecha_hora_entrada' => now(),
                'estado'             => 'ACTIVO',
                'foto_entrada'       => $fotoEntradaPath,
            ]);

            Modulo::find($request->id_modulo)->update(['estado' => 'OCUPADO']);
        });

        return redirect()->route('parqueadero')->with('success', "Ingreso del vehículo {$placa} registrado correctamente.");
    }

    public function registrarSalida(Request $request)
    {
        $request->validate([
            'id_entrada'          => 'required|exists:entrada,id_entrada',
            'metodo_pago'         => 'required|string|in:EFECTIVO,TARJETA,TRANSFERENCIA',
            'foto_base64_salida'  => 'nullable|string',
            'foto_archivo_salida' => 'nullable|image|max:5120',
        ]);

        $entrada = Entrada::with(['tipoServicio', 'modulo'])->findOrFail($request->id_entrada);

        if ($entrada->estado !== 'ACTIVO') {
            return redirect()->back()->withErrors(['error' => 'El ingreso seleccionado ya se encuentra finalizado.']);
        }

        $fechaSalida      = now();
        $minutos          = max(1, $fechaSalida->diffInMinutes($entrada->fecha_hora_entrada));
        $horasTranscurridas = ceil($minutos / 60);
        $tarifaHora       = $entrada->tipoServicio ? $entrada->tipoServicio->tarifa : 2.00;
        $montoTotal       = $horasTranscurridas * $tarifaHora;

        $placa           = $entrada->placa;
        $fotoSalidaPath  = $this->procesarFoto($request, 'foto_base64_salida', 'foto_archivo_salida', 'salidas', $placa);

        DB::transaction(function () use ($entrada, $fechaSalida, $montoTotal, $request, $fotoSalidaPath) {
            $salida = Salida::create([
                'id_entrada'        => $entrada->id_entrada,
                'fecha_hora_salida' => $fechaSalida,
                'foto_salida'       => $fotoSalidaPath,
            ]);

            Factura::create([
                'id_salida'     => $salida->id_salida,
                'fecha_emision' => $fechaSalida,
                'monto_total'   => $montoTotal,
                'metodo_pago'   => $request->metodo_pago,
                'estado_pago'   => 'PAGADO',
            ]);

            $entrada->update(['estado' => 'FINALIZADO']);

            if ($entrada->modulo) {
                $entrada->modulo->update(['estado' => 'DISPONIBLE']);
            }
        });

        return redirect()->route('parqueadero')->with('success', "Salida procesada. Factura de \$" . number_format($montoTotal, 2) . " generada y módulo liberado.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  OCR de Placa
    // ─────────────────────────────────────────────────────────────────────────
    public function procesarFotoOCR(Request $request)
    {
        $request->validate(['foto' => 'required|string']);

        $ocrService     = new PlacaOCRService();
        $placaDetectada = $ocrService->extraerPlacaDesdeImagen($request->foto);

        if (!$placaDetectada) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo detectar la placa en la imagen. Intenta nuevamente o ingresa la placa manualmente.'
            ], 400);
        }

        $vehiculo = Vehiculo::with(['cliente.user'])->find($placaDetectada);

        if ($vehiculo) {
            return response()->json([
                'success'        => true,
                'placa'          => $placaDetectada,
                'vehiculo_existe' => true,
                'vehiculo'       => [
                    'placa'       => $vehiculo->placa,
                    'marca'       => $vehiculo->marca,
                    'modelo'      => $vehiculo->modelo,
                    'propietario' => $vehiculo->cliente->user->nombre ?? 'Cliente General',
                ],
            ]);
        }

        return response()->json([
            'success'        => true,
            'placa'          => $placaDetectada,
            'vehiculo_existe' => false,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Helpers privados
    // ─────────────────────────────────────────────────────────────────────────
    private function procesarFoto(Request $request, string $base64Field, string $fileField, string $carpeta, string $placa): ?string
    {
        $prefix    = ($carpeta === 'entradas') ? 'entrada' : 'salida';
        $directory = public_path("uploads/{$carpeta}");

        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        if ($request->filled($base64Field)) {
            try {
                $imgData = $request->input($base64Field);
                $imgData = preg_replace('/^data:image\/[a-z]+;base64,/', '', $imgData);
                $imgData = str_replace(' ', '+', $imgData);
                $data    = base64_decode($imgData);

                $filename = "{$prefix}_" . preg_replace('/[^A-Za-z0-9]/', '', $placa) . '_' . time() . '.jpg';
                file_put_contents("{$directory}/{$filename}", $data);
                return "uploads/{$carpeta}/{$filename}";
            } catch (\Exception $e) {
                return null;
            }
        }

        if ($request->hasFile($fileField)) {
            $file     = $request->file($fileField);
            $filename = "{$prefix}_" . preg_replace('/[^A-Za-z0-9]/', '', $placa) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $filename);
            return "uploads/{$carpeta}/{$filename}";
        }

        return null;
    }
}
