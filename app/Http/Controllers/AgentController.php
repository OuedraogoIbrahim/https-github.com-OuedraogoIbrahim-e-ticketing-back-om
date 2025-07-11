<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AgentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $agents = Agent::where('organizer_id', Auth::user()->organizer->id)->get();
        return response()->json($agents, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'password' => 'required|string|min:8',
        ]);

        $agent = Agent::create([
            'nom' => $validated['nom'],
            'password' => Hash::make($validated['password']),
            'organizer_id' => Auth::user()->organizer->id,
        ]);

        return response()->json([
            'message' => 'Agent créé avec succès',
            'agent' => $agent
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Agent $agent)
    {
        if ($agent->organizer_id !== Auth::user()->organizer->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($agent, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Agent $agent)
    {
        if ($agent->organizer_id !== Auth::user()->organizer->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'password' => 'sometimes|required|string|min:8',
        ]);

        $updateData = [];
        if (isset($validated['nom'])) {
            $updateData['nom'] = $validated['nom'];
        }
        if (isset($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $agent->update($updateData);

        return response()->json([
            'message' => 'Agent modifié avec succès',
            'agent' => $agent
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agent $agent)
    {
        if ($agent->organizer_id !== Auth::user()->organizer->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $agent->delete();

        return response()->json(['message' => 'Agent supprimé avec succès'], 200);
    }
}
