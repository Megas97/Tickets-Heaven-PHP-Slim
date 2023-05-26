<?php

namespace models;

use models\User;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model {

    protected $table = 'support_tickets';

    protected $fillable = [
        'user_id',
        'guest_info',
        'subject',
        'message',
    ];

    public function user() {

        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
