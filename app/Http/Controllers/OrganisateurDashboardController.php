<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganisateurDashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $organizer = Auth::user()->organizer;

        // Événements du jour (avec nombre de tickets achetés + utilisés)
        $todayEvents = Event::where('organizer_id', $organizer->id)
            ->whereDate('date_debut', today())
            ->withCount([
                'tickets', // nombre de tickets achetés
                'tickets as used_tickets_count' => fn($q) =>
                $q->whereNotNull('date_utilisation') // tickets utilisés
            ])
            ->get();

        // Statistiques globales (tous les événements et tickets achetés)
        $totalEvents = Event::where('organizer_id', $organizer->id)->count();

        $totalTickets = Ticket::whereHas(
            'event',
            fn($q) =>
            $q->where('organizer_id', $organizer->id)
        )->count();

        // Statistiques du jour (calculées à partir des événements du jour)
        $todayStats = [
            'events_count' => $todayEvents->count(),
            'tickets_total' => $todayEvents->sum('tickets_count'),           // achetés
            'tickets_used' => $todayEvents->sum('used_tickets_count'),       // utilisés
            'tickets_unused' => $todayEvents->sum('tickets_count') -         // non utilisés
                $todayEvents->sum('used_tickets_count'),
        ];

        return response()->json([
            'today_stats' => $todayStats,
            'global_stats' => [
                'total_events' => $totalEvents,
                'total_tickets' => $totalTickets,
            ],
            'today_events' => $todayEvents->map(fn($event) => [
                'id' => $event->id,
                'titre' => $event->titre,
                'heure_debut' => $event->heure_debut,
                'nombre_ticket' => $event->nombre_tickets,         // capacité totale
                'tickets_achetes' => $event->tickets_count,       // achetés
                'tickets_utilises' => $event->used_tickets_count, // utilisés
            ]),
        ]);
    }
}
