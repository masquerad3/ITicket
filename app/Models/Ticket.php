<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    protected $table = 'TICKETS';

    protected $primaryKey = 'ticket_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'assigned_to',
        'assigned_at',
        'subject',
        'category',
        'priority',
        'department',
        'location',
        'description',
        'preferred_contact',
        'status',
        'resolved_at',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
        'assigned_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }
}
