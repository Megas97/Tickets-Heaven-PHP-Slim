<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCountriesTable extends Migration {
    
    public function up() {

        $this->schema->create('countries', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('name');
            $table->integer('continent_id');
            $table->timestamps();
        });
    }

    public function down() {
        
        $this->schema->drop('countries');
    }
}
