<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketTransfer extends Model
{
    /** @use HasFactory<\Database\Factories\TicketTransferFactory> */
    use HasFactory;

    protected $table = 'ticket_transfers';
    protected $fillable = [
        'event_id',
        'from_client_id',
        'to_client_id',
        'quantity',
        'note',
        'transferred_at',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function fromClient()
    {
        return $this->belongsTo(Client::class, 'from_client_id');
    }

    public function toClient()
    {
        return $this->belongsTo(Client::class, 'to_client_id');
    }
}
