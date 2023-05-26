<?php

namespace models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EventParticipant extends Pivot {
    
    protected $table = 'event_participants';

    public $incrementing = true;
}
