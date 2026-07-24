<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personal;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Hacer JOIN entre personal, users y rol para obtener todos los datos
        $usuarios = Personal::join('users', 'personal.cedula_users', '=', 'users.cedula')
            ->join('rol', 'personal.id_rol', '=', 'rol.id_rol')
            ->select(
                'personal.id_personal',
                'personal.usuario',
                'personal.id_rol',
                'personal.created_at as personal_created_at',
                'users.cedula',
                'users.nombre as nombre_completo',
                'users.correo',
                'users.telefono',
                'rol.nombre_rol'
            )
            ->orderBy('personal.created_at', 'desc')
            ->get();

        return view('usuarios', ['active' => 'usuarios', 'usuarios' => $usuarios]);
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
            'cedula' => 'required|unique:users,cedula',
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,correo',
            'telefono' => 'nullable|string|max:20',
            'usuario' => 'required|string|max:50|unique:personal,usuario',
            'password' => 'required|string|min:8',
            'id_rol' => 'required|integer|exists:rol,id_rol',
        ]);

        try {
            DB::beginTransaction();

            // Crear registro en la tabla users
            $user = new User();
            $user->cedula = $request->cedula;
            $user->nombre = $request->nombre;
            $user->correo = $request->email;
            $user->telefono = $request->telefono;
            $user->save();

            // Crear registro en la tabla personal
            $personal = new Personal();
            $personal->cedula_users = $request->cedula;
            $personal->id_rol = $request->id_rol;
            $personal->usuario = $request->usuario;
            $personal->password_hash = $request->password; // Laravel lo hashearará automáticamente
            $personal->save();

            DB::commit();

            return redirect()->route('usuarios')->with('success', 'Usuario creado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear el usuario: ' . $e->getMessage())->withInput();
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
        // Obtener el usuario con JOIN para mostrar en el formulario
        $usuario = Personal::join('users', 'personal.cedula_users', '=', 'users.cedula')
            ->join('rol', 'personal.id_rol', '=', 'rol.id_rol')
            ->select(
                'personal.id_personal',
                'personal.usuario',
                'personal.id_rol',
                'users.cedula',
                'users.nombre as nombre_completo',
                'users.correo',
                'users.telefono',
                'rol.nombre_rol'
            )
            ->where('personal.id_personal', $id)
            ->first();

        if (!$usuario) {
            return back()->with('error', 'Usuario no encontrado');
        }

        return response()->json($usuario);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Obtener el personal actual para obtener la cédula actual
        $personal = Personal::findOrFail($id);
        $cedulaActual = $personal->cedula_users;

        $request->validate([
            'cedula' => 'required|unique:users,cedula,' . $cedulaActual . ',cedula',
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,correo,' . $cedulaActual . ',cedula',
            'telefono' => 'nullable|string|max:20',
            'usuario' => 'required|string|max:50|unique:personal,usuario,' . $id . ',id_personal',
            'id_rol' => 'required|integer|exists:rol,id_rol',
        ]);

        try {
            DB::beginTransaction();

            // Actualizar registro en la tabla users
            $user = User::findOrFail($cedulaActual);
            $user->cedula = $request->cedula;
            $user->nombre = $request->nombre;
            $user->telefono = $request->telefono;
            $user->correo = $request->email;
            $user->save();

            // Actualizar registro en la tabla personal
            $personal->cedula_users = $request->cedula;
            $personal->id_rol = $request->id_rol;
            $personal->usuario = $request->usuario;
            $personal->save();

            DB::commit();

            return redirect()->route('usuarios')->with('success', 'Usuario actualizado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar el usuario: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            // Obtener el personal
            $personal = Personal::findOrFail($id);
            $cedula = $personal->cedula_users;

            // Eliminar de la tabla personal
            $personal->delete();

            // Eliminar de la tabla users
            $user = User::findOrFail($cedula);
            $user->delete();

            DB::commit();

            return redirect()->route('usuarios')->with('success', 'Usuario eliminado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar el usuario: ' . $e->getMessage());
        }
    }
}
