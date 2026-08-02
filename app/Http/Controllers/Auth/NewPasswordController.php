<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Personal;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $user = User::where('correo', $request->email)->first();

        /** @var \Illuminate\Auth\Passwords\PasswordBroker $broker */
        $broker = Password::broker('users');

        if (!$user || !$broker->tokenExists($user, $request->token)) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'El enlace de recuperación es inválido o ha expirado.']);
        }

        $personal = Personal::where('cedula_users', $user->cedula)->first();

        if ($personal) {
            $personal->password_hash = Hash::make($request->password);
            $personal->save();
        }

        $broker->deleteToken($user);

        event(new PasswordReset($user));

        return redirect()->route('login')->with('status', 'Tu contraseña ha sido actualizada correctamente.');
    }
}
