<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
