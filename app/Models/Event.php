<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;
    protected $fillable = [
        'titre',
        'description',
        'photo',
        'date_debut',
        'date_fin',
        'ville',
        'prix',
        'heure_debut',
        'heure_fin',
        'organizer_id',
        'event_type_id',
        'nombre_tickets',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }

    public function ticket_transfers()
    {
        return $this->hasMany(TicketTransfer::class);
    }

    public function organizer()
    {
        return $this->belongsTo(Organizer::class);
    }
}
