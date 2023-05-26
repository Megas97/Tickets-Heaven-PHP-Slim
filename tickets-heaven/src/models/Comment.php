<?php

namespace models;

use models\User;
use models\Event;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model {

    protected $table = 'comments';

    protected $fillable = [
        'user_id',
        'event_id',
        'comment',
    ];

    public function user() {

        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function event() {
        
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }
}
