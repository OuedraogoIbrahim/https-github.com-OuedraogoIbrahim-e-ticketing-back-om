<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Agent extends Model
{
    //
    use HasApiTokens;

    protected $fillable = [
        'nom',
        'password',
        'organizer_id',
    ];
}
