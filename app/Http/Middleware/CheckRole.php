<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();

        // Si el usuario no está autenticado
        if (!$user) {
            return redirect('login');
        }

        // Hacer un join directo con la tabla rol para obtener el nombre del rol
        $userWithRole = \App\Models\Personal::join('rol', 'personal.id_rol', '=', 'rol.id_rol')
            ->where('personal.id_personal', $user->id_personal)
            ->select('personal.*', 'rol.nombre_rol')
            ->first();

        // Obtener el rol del usuario
        $userRole = $userWithRole ? $userWithRole->nombre_rol : null;

        // Debug: Log para verificar el rol (puedes comentar esto después)
        \Log::info('CheckRole Middleware', [
            'user_id' => $user->id_personal,
            'user_rol' => $userRole,
            'required_roles' => $roles,
            'id_rol' => $userWithRole ? $userWithRole->id_rol : null,
        ]);

        // Verificar si el usuario tiene alguno de los roles permitidos
        if ($userRole && in_array($userRole, $roles)) {
            return $next($request);
        }

        // Si no tiene permisos, retornar error 403 con información del problema
        abort(403, 'No tienes permiso para acceder a esta sección. Tu rol es: ' . ($userRole ?? 'No asignado'));
    }
}
