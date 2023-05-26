<?php

namespace models;

use models\Continent;
use Illuminate\Database\Eloquent\Model;

class Country extends Model {

    protected $table = 'countries';

    protected $fillable = [
        'name',
        'continent_id',
    ];

    public function continent() {

        return $this->belongsTo(Continent::class, 'continent_id', 'id');
    }
}
