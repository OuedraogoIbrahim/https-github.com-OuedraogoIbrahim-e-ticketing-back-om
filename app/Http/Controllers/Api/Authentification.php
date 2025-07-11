<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Authentification extends Controller
{
    //
    public function register(Request $request)
    {
        $fields = $request->validate([
            'username' => 'required|string|max:255',
            'telephone' => 'required|',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed'
        ]);

        $user = User::create([
            'username' => $fields['username'],
            'telephone' => $fields['telephone'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
            'role' => 'client'
        ]);

        $client = new Client();
        $client->user_id = $user->id;
        $client->save();

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les informations de connexion sont incorrectes.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function loginClient(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where(['email' => $request->email, 'role' => 'client'])->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les informations de connexion sont incorrectes.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function loginOrganisateur(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where(['email' => $request->email, 'role' => 'organisateur'])->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les informations de connexion sont incorrectes.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function loginAgent(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string',
            'password' => 'required|string',
        ]);

        $agent = Agent::where('nom', $validated['nom'])->first();

        if (!$agent || !Hash::check($validated['password'], $agent->password)) {
            return response()->json(['error' => 'Identifiants invalides'], 401);
        }

        $token = $agent->createToken('agent-token')->plainTextToken;

        return response()->json([
            'agent' => $agent,
            'token' => $token,
        ], 200);
    }

    public function deconnexion(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Déconnexion réussie']);
    }
}
