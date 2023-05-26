<?php

namespace models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model {

    protected $table = 'currencies';

    protected $fillable = [
        'name',
    ];

    public function getCurrency() {

        return $this->name;
    }
}
