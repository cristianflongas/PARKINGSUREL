<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Entrada;
use App\Models\Modulo;
use App\Models\Personal;
use App\Models\TipoServicio;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VehiculoRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_user_client_and_vehicle_when_registering_a_vehicle(): void
    {
        $response = $this->actingAs(
            User::factory()->create([
                'cedula' => '9000000000',
                'nombre' => 'Administrador',
                'correo' => 'admin@parkingsure.com',
            ])
        )->post('/vehiculos', [
            'placa' => 'ABC-123',
            'cedula' => '1234567890',
            'propietario' => 'Maria Lopez',
            'telefono' => '0999999999',
            'correo' => 'maria@correo.com',
            'marca_modelo' => 'Toyota Corolla',
            'tipo' => 'Sedan',
        ]);

        $response->assertRedirect(route('vehiculos'));

        $user = User::find('1234567890');
        $this->assertNotNull($user);
        $this->assertSame('Maria Lopez', $user->nombre);
        $this->assertSame('0999999999', $user->telefono);
        $this->assertSame('maria@correo.com', $user->correo);

        $cliente = Cliente::where('cedula_users', '1234567890')->first();
        $this->assertNotNull($cliente);

        $vehiculo = Vehiculo::find('ABC-123');
        $this->assertNotNull($vehiculo);
        $this->assertSame($cliente->id_cliente, $vehiculo->id_cliente);
        $this->assertSame('Toyota', $vehiculo->marca);
        $this->assertSame('Corolla', $vehiculo->modelo);
        $this->assertSame('Sedan', $vehiculo->color);
    }

    public function test_parqueadero_view_handles_missing_user_data_gracefully(): void
    {
        DB::table('rol')->insert([
            'id_rol' => 1,
            'nombre_rol' => 'admin',
        ]);

        $user = User::create([
            'cedula' => '9000000001',
            'nombre' => 'Administrador',
            'telefono' => '04140000000',
            'correo' => 'admin2@parkingsure.com',
        ]);

        $personal = Personal::create([
            'cedula_users' => $user->cedula,
            'id_rol' => 1,
            'usuario' => 'admin2',
            'password_hash' => Hash::make('password123'),
        ]);

        $clienteUser = User::create([
            'cedula' => '9999999999',
            'nombre' => 'Cliente General',
            'telefono' => '04149999999',
            'correo' => 'general@parkingsure.com',
        ]);

        $cliente = Cliente::create([
            'cedula_users' => $clienteUser->cedula,
        ]);

        $vehiculo = Vehiculo::create([
            'placa' => 'XYZ-999',
            'id_cliente' => $cliente->id_cliente,
            'marca' => 'Toyota',
            'modelo' => 'Corolla',
            'color' => 'Blanco',
        ]);

        $tipoServicio = TipoServicio::create([
            'nombre_tipo_servicio' => 'Tarifa Auto / Hora',
            'tarifa' => 2.00,
            'estado' => 'ACTIVO',
        ]);

        $modulo = Modulo::create([
            'ubicacion' => 'A-01',
            'estado' => 'OCUPADO',
        ]);

        Entrada::create([
            'placa' => $vehiculo->placa,
            'id_modulo' => $modulo->id_modulo,
            'id_personal' => $personal->id_personal,
            'id_tipo_servicio' => $tipoServicio->id_tipo_servicio,
            'fecha_hora_entrada' => now(),
            'estado' => 'ACTIVO',
        ]);

        $response = $this->actingAs($personal)->get('/parqueadero');

        $response->assertOk();
        $response->assertSee('A-01');
        $response->assertSee('Cliente General');
    }
}
