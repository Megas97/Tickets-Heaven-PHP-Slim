<?php

namespace models;

use models\User;
use models\Venue;
use models\Currency;
use models\PromoCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model {

    use SoftDeletes;
    
    protected $table = 'events';

    protected $fillable = [
        'name',
        'description',
        'location',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'host_id',
        'venue_id',
        'event_picture',
        'owner_approved',
        'currency_id',
        'ticket_price',
    ];

    public function host() {

        return $this->belongsTo(User::class, 'host_id', 'id');
    }

    public function venue() {

        return $this->belongsTo(Venue::class, 'venue_id', 'id')->withTrashed();
    }

    public function participants() {

        return $this->belongsToMany(User::class, 'event_participants', 'event_id', 'user_id')->withPivot('artist_approved')->withTimestamps();
    }

    public function currency() {
        
        return $this->hasOne(Currency::class, 'id', 'currency_id');
    }

    // this relation returns only the existing users, if a guest has bought a ticket it won't count it as the user_id would be 0 (non-existent)!

    public function soldTickets() {

        return $this->belongsToMany(User::class, 'orders', 'event_id', 'user_id')->withPivot('id')->withPivot('ticket_price')->withPivot('currency_id')->withPivot('ticket_quantity')->withPivot('tickets')->withTimestamps();
    }

    public function promoCode() {

        return $this->hasOne(PromoCode::class);
    }
}
