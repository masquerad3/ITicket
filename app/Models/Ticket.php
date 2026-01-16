<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'TICKETS';

    protected $primaryKey = 'ticket_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'subject',
        'category',
        'priority',
        'department',
        'location',
        'description',
        'preferred_contact',
        'status',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];
}
