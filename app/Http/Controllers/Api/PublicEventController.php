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
        $query = Event::query()
            ->where('date_debut', '>=', now())
            ->with('type');

        // Filtres
        if ($request->type) {
            $query->where('event_type_id', $request->type);
        }

        if ($request->ville) {
            $query->where('ville', $request->ville);
        }

        if ($request->date) {
            $today = now();
            switch ($request->date) {
                case 'today':
                    $query->whereDate('date_debut', $today);
                    break;
                case 'week':
                    $query->whereBetween('date_debut', [$today, $today->copy()->addWeek()]);
                    break;
                case 'month':
                    $query->whereBetween('date_debut', [$today, $today->copy()->addMonth()]);
                    break;
            }
        }

        if ($request->search) {
            $query->where('titre', 'like', '%' . $request->search . '%');
        }

        // Pagination
        $perPage = $request->per_page ?? 10;
        $events = $query->paginate($perPage);

        // Transformation des données avec gestion des photos
        $transformedData = $events->getCollection()->map(function ($event) {
            return [
                'id' => $event->id,
                'titre' => $event->titre,
                'description' => $event->description,
                'date_debut' => $event->date_debut,
                'date_fin' => $event->date_fin,
                'heure_debut' => $event->heure_debut,
                'heure_fin' => $event->heure_fin,
                'ville' => $event->ville,
                'prix' => $event->prix,
                'photo' => $event->photo ? Storage::url($event->photo) : null,
                'type' => [
                    'id' => $event->type->id,
                    'nom' => $event->type->nom
                ],
                // Ajoutez d'autres champs si nécessaire
            ];
        });

        return response()->json([
            'data' => $transformedData,
            'meta' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
            ],
            'links' => [
                'first' => $events->url(1),
                'last' => $events->url($events->lastPage()),
                'prev' => $events->previousPageUrl(),
                'next' => $events->nextPageUrl(),
            ]
        ]);
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
