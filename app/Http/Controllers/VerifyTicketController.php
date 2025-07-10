<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyTicketController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $token)
    {
        //
        Log::info("En cours");

        $ticket = Ticket::query()->where('token', $token)->first();
        if ($ticket) {
            Log::info("Existe");
            return response()->json(['success' => true]);
        } else {
            Log::info("Rien");
            return response()->json(['success' => false]);
        }
    }
}
