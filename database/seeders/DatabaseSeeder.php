<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Organizer;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    $user = User::create([
      'username' => 'admin',
      'email' => 'organisateur@gmail.com',
      'password' => bcrypt('password01'),
      'telephone' => '50505050',
      'role' => 'organisateur',
    ]);

    $organisateur = new Organizer();
    $organisateur->user_id = $user->id;
    $organisateur->save();

    $user2 = User::create([
      'username' => 'client',
      'email' => 'client@gmail.com',
      'password' => bcrypt('password01'),
      'telephone' => '50505051',
      'role' => 'client',
    ]);

    $user3 = User::create([
      'username' => 'client2',
      'email' => 'client2@gmail.com',
      'password' => bcrypt('password01'),
      'telephone' => '50505053',
      'role' => 'client',
    ]);

    $client = new Client();
    $client->user_id = $user2->id;
    $client->save();

    $client2 = new Client();
    $client2->user_id = $user3->id;
    $client2->save();

    $eventType1 = new EventType();
    $eventType1->nom = 'cinema';
    $eventType1->save();

    $eventType2 = new EventType();
    $eventType2->nom = 'concert';
    $eventType2->save();

    $eventType3 = new EventType();
    $eventType3->nom = 'autre';
    $eventType3->save();

    for ($i = 1; $i <= 10; $i++) {
      $event = new Event();
      $event->titre = 'Événement ' . $i;
      $event->description = 'Description de l’événement ' . $i;
      $event->photo = null;
      $event->date_debut = Carbon::now()->addDays($i)->format('Y-m-d');
      $event->date_fin = Carbon::now()->addDays($i + 1)->format('Y-m-d');
      $event->ville = 'Ville ' . $i;
      $event->prix = rand(5000, 20000);
      $event->heure_debut = '18:00';
      $event->heure_fin = '22:00';
      $event->organizer_id = $organisateur->id;
      $event->event_type_id = $i % 2 == 0 ? $eventType1->id : $eventType2->id;
      $event->nombre_tickets = rand(200, 400);
      $event->save();

      // Créer entre 1 et 5 tickets individuels pour cet événement
      $numTickets = rand(1, 5); // Nombre aléatoire de tickets
      for ($j = 0; $j < $numTickets; $j++) {
        $ticket = new Ticket();
        $ticket->client_id = $client->id;
        $ticket->event_id = $event->id;
        $ticket->date_achat = Carbon::now()->format('Y-m-d');
        $ticket->token = Str::uuid(); // Générer un token unique
        $ticket->date_utilisation = null; // Non utilisé
        $ticket->save();
      }
    }
  }
}
