<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OrganizerEventController extends Controller
{
    /**
     * List all events for the authenticated organizer.
     */
    public function index()
    {

        $events = Event::query()
            ->where('organizer_id', Auth::user()->organizer->id)
            ->with('type')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($events);
    }


    /**
     * Create a new event.
     */
    public function store(Request $request)
    {
        $idTypeAutre = EventType::query()->where('nom', 'autre')->first();
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'nombre_tickets' => 'required|numeric|min:1',
            'event_type_id' => 'required|exists:event_types,id',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'ville' => 'required|string|max:255',
            'prix' => 'required|numeric|min:0',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'heure_debut' => 'required|string',
            'heure_fin' => 'required|string',
        ]);

        if ($validated['event_type_id'] == $idTypeAutre->id) {
            $validated['type_autre'] = $request->type_autre;
        }

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('events/' . Auth::user()->id, 'public');
        }

        $validated['organizer_id'] = Auth::user()->organizer->id;

        $event = Event::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Événement créé avec succès.',
            'event' => $event->fresh(['type']),
        ], 201);
    }


    /**
     * Show an event.
     */

    public function show(string $id)
    {
        $event = Event::query()->with('type')->where('organizer_id', Auth::user()->organizer->id)->findOrFail($id);

        // Calculer le nombre de tickets disponibles
        $ticketsSold = Ticket::where('event_id', $event->id)->count();
        $event->tickets_disponibles = $event->nombre_tickets - $ticketsSold;

        // Ajouter l'URL complète pour la photo
        if ($event->photo) {
            $event->photo = Storage::url($event->photo);
        }
        return response()->json($event);
    }


    /**
     * Update an event.
     */
    public function update(Request $request, $id)
    {
        $idTypeAutre = EventType::query()->where('nom', 'autre')->first();
        $event = Event::where('organizer_id', Auth::user()->organizer->id)->findOrFail($id);

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'nombre_tickets' => 'required|numeric|min:1',
            'event_type_id' => 'required|exists:event_types,id',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'ville' => 'required|string|max:255',
            'prix' => 'required|numeric|',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'heure_debut' => 'required|string',
            'heure_fin' => 'nullable|string',
        ]);


        if ($validated['event_type_id'] == $idTypeAutre->id) {
            $validated['type_autre'] = $request->type_autre;
        } else {
            $validated['type_autre']  = '';
        }
        //Logique pour upload
        if ($request->hasFile('photo')) {
            // Supprimer l'ancienne photo si elle existe
            if ($event->photo) {
                Storage::disk('public')->delete($event->photo);
            }
            $validated['photo'] = $request->file('photo')->store('events/' . Auth::user()->id, 'public');
        } else {
            unset($validated['photo']);
        }

        $event->update($validated);


        return response()->json([
            'success' => true,
            'message' => 'Événement mis à jour avec succès.',
            'event' => $event->fresh(['type']),
        ]);
    }


    /**
     * Delete an event.
     */
    public function destroy(string $id)
    {
        $event = Event::where('organizer_id', Auth::user()->organizer->id)->findOrFail($id);
        $event->delete();

        return response()->json(['success' => true, 'message' => 'Événement supprimé avec succès.']);
    }

    /**
     * Get event history.
     */
    public function history(Event $event)
    {
        // Retourne uniquement l'événement demandé avec les counts nécessaires
        return response()->json([
            'event' => $event,
            'stats' => [
                'total_tickets' => $event->tickets()->count(),
                'transferred_tickets' => $event->ticket_transfers()->count(),
                'used_tickets' => $event->tickets()->whereNotNull('date_utilisation')->count(),
            ]
        ]);
    }
}
