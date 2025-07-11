<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketTransfer;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ClientDashboardController extends Controller
{
    /**
     * Get client statistics.
     */
    public function stats(Request $request)
    {
        $client = Auth::user()->client;

        $ticketsCount = $client->tickets()->with('event')->get();
        $ticketsNonUtilises = $ticketsCount->whereNull('date_utilisation')->count();
        $ticketsTransferes = TicketTransfer::where('from_client_id', $client->id)->sum('quantity');

        return response()->json([
            'tickets_total' => $ticketsCount->count(),
            'tickets_non_utilises' => $ticketsNonUtilises,
            'tickets_transferes' => $ticketsTransferes,
        ]);
    }

    /**
     * List events with tickets for the authenticated client.
     */
    public function index(Request $request)
    {
        $client = Auth::user()->client;

        $events = Event::whereHas('tickets', function ($q) use ($client) {
            $q->where('client_id', $client->id)
                ->whereNull('date_utilisation');
        })
            ->where('date_debut', '>=', now()->toDateString())
            ->with(['tickets' => fn($q) => $q->where('client_id', $client->id)->whereNull('date_utilisation')])
            ->paginate(6);

        return response()->json([
            'data' => $events->items(),
            'links' => [
                'first' => $events->url(1),
                'last' => $events->url($events->lastPage()),
                'prev' => $events->previousPageUrl(),
                'next' => $events->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $events->currentPage(),
                'from' => $events->firstItem(),
                'last_page' => $events->lastPage(),
                'path' => $events->path(),
                'per_page' => $events->perPage(),
                'to' => $events->lastItem(),
                'total' => $events->total(),
            ]
        ]);
    }
    /**
     * Transfer tickets to another client.
     */
    public function transferTicket(Request $request, $ticketId)
    {
        try {
            $request->validate([
                'phone' => 'required|regex:/^\+?[1-9]\d{1,14}$/',
                'quantity' => 'required|integer|min:1',
            ]);

            $ticket = Auth::user()->client->tickets()->with('event')->findOrFail($ticketId);

            // Compter les tickets disponibles pour l'événement
            $totalTickets = Auth::user()->client->tickets()
                ->where('event_id', $ticket->event_id)
                ->whereNull('date_utilisation')
                ->count();

            if ($request->quantity > $totalTickets) {
                return response()->json(['success' => false, 'message' => 'Quantité insuffisante pour cet événement.'], 422);
            }

            // Vérifier le destinataire par numéro de téléphone
            $destinataire = User::where('telephone', $request->phone)->first();

            if (!$destinataire) {
                return response()->json(['success' => false, 'message' => 'Aucun client trouvé avec ce numéro de téléphone.'], 422);
            }

            if ($destinataire->id === Auth::user()->id) {
                return response()->json(['success' => false, 'message' => 'Vous ne pouvez pas vous transférer vos propres tickets.'], 422);
            }

            // Transférer les tickets
            $ticketsToTransfer = Auth::user()->client->tickets()
                ->where('event_id', $ticket->event_id)
                ->whereNull('date_utilisation')
                ->take($request->quantity)
                ->get();

            foreach ($ticketsToTransfer as $ticketToTransfer) {
                $ticketToTransfer->update([
                    'client_id' => $destinataire->client->id,
                    'date_achat' => $ticket->date_achat,
                    'token' => Str::uuid(),
                ]);
            }

            // Enregistrer le transfert
            TicketTransfer::create([
                'event_id' => $ticket->event_id,
                'from_client_id' => Auth::user()->client->id,
                'to_client_id' => $destinataire->client->id,
                'quantity' => $request->quantity,
                'transferred_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Ticket transféré avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Une erreur est survenue lors du transfert.'], 500);
        }
    }

    /**
     * Download tickets as PDF for a specific event.
     */
    public function downloadTicket($eventId)
    {
        $tickets = Auth::user()->client->tickets()
            ->with('event')
            ->where('event_id', $eventId)
            ->whereNull('date_utilisation')
            ->get();

        if ($tickets->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Aucun ticket disponible pour cet événement.'], 422);
        }

        $pdf = Pdf::loadView('pdf.ticket', [
            'tickets' => $tickets,
            'event' => $tickets[0]->event,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'ticket-event-' . $eventId . '.pdf');
    }


    public function showTicket($ticketId)
    {
        $ticket = Auth::user()->client->tickets()->with('event')->findOrFail($ticketId);
        $ticket->nombre = Auth::user()->client->tickets()
            ->where('event_id', $ticket->event_id)
            ->whereNull('date_utilisation')
            ->count();
        return response()->json($ticket);
    }
}
