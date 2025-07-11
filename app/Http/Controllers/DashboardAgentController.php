<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardAgentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        //
        $agent = Auth::user();

        if (!$agent) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Récupérer les statistiques
        $today = Carbon::today();
        $scannedToday = Ticket::whereNotNull('date_utilisation')
            ->whereDate('date_utilisation', $today)
            ->whereHas('event', function ($query) use ($agent) {
                $query->where('organizer_id', $agent->organizer_id);
            })
            ->count();

        $totalEvents = Event::where('organizer_id', $agent->organizer_id)
            ->whereDate('date_debut', '<=', $today)
            ->whereDate('date_fin', '>=', $today)
            ->count();

        // Récupérer les 3 derniers événements terminés
        $recentEvents = Event::where('organizer_id', $agent->organizer_id)
            ->where('date_fin', '<', $today)
            ->orderBy('date_fin', 'desc')
            ->take(3)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'date' => Carbon::parse($event->date_fin)->format('d/m/Y'),
                    'scans' => Ticket::where('event_id', $event->id)
                        ->whereNotNull('date_utilisation')
                        ->count(),
                ];
            });

        return response()->json([
            'scannedToday' => $scannedToday,
            'totalEvents' => $totalEvents,
            'recentEvents' => $recentEvents,
        ]);
    }
}
