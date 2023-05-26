<?php

namespace models;

use models\Event;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model {

    protected $table = 'promo_codes';

    protected $fillable = [
        'code',
        'event_id',
        'percent',
        'deadline',
    ];

    public function event() {

        return $this->belongsTo(Event::class, 'event_id', 'id')->withTrashed();
    }
}
