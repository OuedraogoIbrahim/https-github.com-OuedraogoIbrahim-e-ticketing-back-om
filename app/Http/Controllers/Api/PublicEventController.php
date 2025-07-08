<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicEventController extends Controller
{
    //
    /**
     * List public events with filters.
     */
    public function index(Request $request)
    {
        $events = Event::query()
            ->where('date_debut', '>=', now())
            ->with('type')
            ->orderBy('date_debut')
            ->get()
            ->map(function ($event) {
                if ($event->photo) {
                    $event->photo = Storage::url($event->photo);
                }
                return $event;
            });

        return response()->json($events);
    }

    /**
     * Get all event types.
     */
    public function eventTypes()
    {
        $eventTypes = EventType::all()->pluck('nom', 'id');
        return response()->json($eventTypes);
    }

    /**
     * Get distinct cities.
     */
    public function cities()
    {
        $villes = Event::distinct()->pluck('ville');
        return response()->json($villes);
    }

    public function show($id)
    {
        $event = Event::with(['type', 'organizer.user'])
            ->findOrFail($id);

        // Calculer le nombre de tickets disponibles
        $ticketsSold = Ticket::where('event_id', $event->id)->count();
        $event->tickets_disponibles = $event->nombre_tickets - $ticketsSold;

        if ($event->photo) {
            $event->photo = Storage::url($event->photo);
        }

        return response()->json($event);
    }
}
