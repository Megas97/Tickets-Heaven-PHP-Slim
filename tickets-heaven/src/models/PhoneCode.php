<?php

namespace models;

use models\Country;
use Illuminate\Database\Eloquent\Model;

class PhoneCode extends Model {

    protected $table = 'phone_codes';

    protected $fillable = [
        'code',
        'country_id',
    ];

    public function country() {

        return $this->belongsTo(Country::class, 'country_id', 'id');
    }
}
