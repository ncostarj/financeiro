<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class RegisterController extends Controller
{
    // Exibe a tela de Registro
    public function create()
    {
        return Inertia::render('Auth/Register');
    }

    // Processa os dados e cria o usuário
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'], // Exige o campo password_confirmation igual
        ]);

        // Cria o usuário no banco
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Faz o login automático do novo usuário
        Auth::login($user);

        // Redireciona para a página principal (Index.vue)
        return redirect()->intended('/');
    }
}