<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VerifyTicketController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $token)
    {
        $ticket = Ticket::query()->where('token', $token)->with('event')->first();
        if (!$ticket || !$ticket->event || $ticket->date_utilisation != null) {
            return response()->json(['success' => false, 'error' => 'Ticket or event not found']);
        }

        $agent = Auth::user();

        if (!$agent || $agent->organizer_id !== $ticket->event->organizer_id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized: Agent not associated with the event organizer']);
        }

        try {
            $today = Carbon::today();
            $startDate = Carbon::parse($ticket->event->date_debut);
            $endDate = Carbon::parse($ticket->event->date_fin);

            if ($today->between($startDate, $endDate) || $today->isSameDay($startDate) || $today->isSameDay($endDate)) {
                $ticket->update([
                    'date_utilisation' => Carbon::now()->toDateString(),
                ]);
                return response()->json(['success' => true]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Invalid date format']);
        }

        return response()->json(['success' => false, 'error' => 'Event date is not valid']);
    }
}
