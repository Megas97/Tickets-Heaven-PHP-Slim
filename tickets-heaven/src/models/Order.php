<?php

namespace models;

use models\Event;
use models\User;
use models\Currency;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Order extends Pivot {
    
    protected $table = 'orders';

    public $incrementing = true;

    public function event() {
        
        return $this->hasOne(Event::class, 'id', 'event_id')->withTrashed();
    }

    public function user() {
        
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function currency() {
        
        return $this->hasOne(Currency::class, 'id', 'currency_id');
    }
}
