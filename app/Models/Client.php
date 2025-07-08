<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    // Transferts envoyés par le client
    public function sentTransfers()
    {
        return $this->hasMany(TicketTransfer::class, 'from_client_id');
    }

    // Transferts reçus par le client
    public function receivedTransfers()
    {
        return $this->hasMany(TicketTransfer::class, 'to_client_id');
    }
}
