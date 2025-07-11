<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\Api\Authentification;
use App\Http\Controllers\Api\ClientDashboardController;
use App\Http\Controllers\Api\OrganizerEventController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PublicEventController;
use App\Http\Controllers\VerifyTicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    //Gestion events cote organisateur
    Route::apiResource('events', OrganizerEventController::class)->except('update');
    Route::post('events/{event}', [OrganizerEventController::class, 'update'])->name('events.update');
    Route::get('/history/{event}/tickets', [OrganizerEventController::class, 'history']);

    Route::get('client/events', [ClientDashboardController::class, 'index']);

    // Routes du dashboard client
    Route::get('client/tickets/{ticketId}', [ClientDashboardController::class, 'showTicket']);
    Route::post('client/tickets/{ticketId}/transfer', [ClientDashboardController::class, 'transferTicket']);
    Route::get('client/events/{eventId}/download-ticket', [ClientDashboardController::class, 'downloadTicket']);
    Route::get('client/stats', [ClientDashboardController::class, 'stats']);

    //paiement
    Route::post('payment/{eventId}/process', [PaymentController::class, 'processPayment']);

    //Verification d'un ticket
    // Route::get('verify-ticket/{token}', VerifyTicketController::class);

    //Gestiion des agents
    Route::apiResource('agents', AgentController::class);

    //Deconnexion 
    Route::post('logout', [Authentification::class, 'deconnexion'])->name('logout');
});

Route::get('verify-ticket/{token}', VerifyTicketController::class);

Route::post('/login/client', [App\Http\Controllers\Api\Authentification::class, 'loginClient']);
Route::post('/login/organisateur', [App\Http\Controllers\Api\Authentification::class, 'loginOrganisateur']);
Route::post('/login/agent', [App\Http\Controllers\Api\Authentification::class, 'loginAgent']);


Route::post('/register', [App\Http\Controllers\Api\Authentification::class, 'register'])->name('register');

//Recuperation des events pour le public
Route::get('public/events', [PublicEventController::class, 'index']);
Route::get('public/events/{id}', [PublicEventController::class, 'show']);

//Recuperation des types et villes
Route::get('public/event-types', [PublicEventController::class, 'eventTypes']);
Route::get('public/cities', [PublicEventController::class, 'cities']);
