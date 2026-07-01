<?php

namespace Tests\Feature\Auth;

use App\Models\Personal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PersonalAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_personal_can_authenticate_using_usuario_and_password(): void
    {
        DB::table('rol')->insert([
            'id_rol' => 1,
            'nombre_rol' => 'admin',
        ]);

        DB::table('users')->insert([
            'cedula' => '12345678',
            'nombre' => 'Juan Perez',
            'telefono' => '04121234567',
            'correo' => 'juan@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $personal = Personal::create([
            'cedula_users' => '12345678',
            'id_rol' => 1,
            'usuario' => 'juan.perez',
            'password_hash' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'usuario' => $personal->usuario,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($personal);
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
