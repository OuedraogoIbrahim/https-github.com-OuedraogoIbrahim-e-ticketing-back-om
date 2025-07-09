<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    /**
     * Display a listing of the resource with filters.
     */
    public function index(Request $request)
    {
        $query = Event::query()->where('organizer_id', Auth::user()->organizer->id);

        return response()->json($query);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'ville' => 'required|string|max:255',
            'prix' => 'required|numeric|min:0',
            'date_debut' => 'required|date',
            'event_type_id' => 'required|exists:event_types,id',
            'organizer_id' => 'required|exists:organizers,id',
        ]);

        $event = Event::create($validated);
        return response()->json($event, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        if ($event->organizer_id !== Auth::user()->organizer->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($event);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        if ($event->organizer_id !== Auth::user()->organizer->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'ville' => 'sometimes|string|max:255',
            'prix' => 'sometimes|numeric|min:0',
            'date_debut' => 'sometimes|date',
            'event_type_id' => 'sometimes|exists:event_types,id',
        ]);

        $event->update($validated);
        return response()->json($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        if ($event->organizer_id !== Auth::user()->organizer->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event->delete();
        return response()->json(null, 204);
    }

}
