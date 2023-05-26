<?php

namespace models;

use models\User;
use models\Event;
use models\PhoneCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venue extends Model {

    use SoftDeletes;

    protected $table = 'venues';

    protected $fillable = [
        'name',
        'description',
        'address',
        'phone_code_id',
        'phone_number',
        'opens',
        'closes',
        'owner_id',
        'venue_picture',
    ];

    public function owner() {

        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    public function phoneCode() {

        return $this->hasOne(PhoneCode::class, 'id', 'phone_code_id');
    }

    public function hostedEvents() {
        
        return $this->hasMany(Event::class, 'venue_id', 'id');
    }
}
