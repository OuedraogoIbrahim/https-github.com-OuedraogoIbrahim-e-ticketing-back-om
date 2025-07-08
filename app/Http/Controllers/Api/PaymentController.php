<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentController extends Controller
{

    /**
     * Process payment and create tickets.
     */
    public function processPayment(Request $request, $eventId)
    {
        $request->validate([
            'nombre_tickets' => 'required|numeric|min:1',
            'telephone' => 'required|regex:/^\+?[1-9]\d{1,14}$/',
        ]);

        $event = Event::findOrFail($eventId);
        $user = Auth::user();

        if (!$user || !$user->client) {
            return response()->json(['success' => false, 'message' => 'Utilisateur non authentifié ou client non trouvé.'], 401);
        }

        // Vérifier si assez de tickets sont disponibles
        $ticketsSold = Ticket::where('event_id', $event->id)->count();
        $ticketsAvailable = $event->nombre_tickets - $ticketsSold;

        if ($request->nombre_tickets > $ticketsAvailable) {
            return response()->json(['success' => false, 'message' => 'Nombre de tickets demandé dépasse les tickets disponibles.'], 422);
        }

        // Simuler la logique de paiement Orange Money
        // Remplace ceci par une intégration réelle avec l'API Orange Money
        $paymentStatus = $this->processOrangeMoneyPayment($request->telephone, $event->prix * $request->nombre_tickets);

        if (!$paymentStatus['success']) {
            return response()->json(['success' => false, 'message' => $paymentStatus['message'] ?? 'Échec du paiement.'], 422);
        }

        // Créer les tickets
        for ($i = 1; $i <= $request->nombre_tickets; $i++) {
            Ticket::create([
                'client_id' => $user->client->id,
                'event_id' => $event->id,
                'date_achat' => now(),
                'token' => Str::random(32),
            ]);
        }

        // Enregistrer le paiement
        Payment::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'montant_total' => $event->prix * $request->nombre_tickets,
            'quantite_ticket' => $request->nombre_tickets,
            'numero_orange_money' => $request->telephone,
        ]);

        return response()->json(['success' => true, 'message' => 'Ticket(s) payé(s) avec succès']);
    }

    /**
     * Simuler le paiement Orange Money (à remplacer par une intégration réelle).
     */
    private function processOrangeMoneyPayment($telephone, $amount)
    {
        // Simuler une vérification de paiement
        // Remplace ceci par un appel réel à l'API Orange Money
        // Exemple : Vérifier si le numéro est valide et si le paiement est réussi
        if (!preg_match('/^\+?[0-9]\d{1,14}$/', $telephone)) {
            return ['success' => false, 'message' => 'Numéro de téléphone invalide.'];
        }

        // Simuler une réponse réussie
        return ['success' => true, 'message' => 'Paiement simulé avec succès.'];
    }
}
