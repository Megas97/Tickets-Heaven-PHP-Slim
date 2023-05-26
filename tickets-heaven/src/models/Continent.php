<?php

namespace models;

use Illuminate\Database\Eloquent\Model;

class Continent extends Model {

    protected $table = 'continents';

    protected $fillable = [
        'name',
    ];

    public function getContinentName() {

        return $this->name;
    }
}
