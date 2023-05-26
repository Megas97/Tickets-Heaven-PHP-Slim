<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateContinentsTable extends Migration {
    
    public function up() {

        $this->schema->create('continents', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down() {
        
        $this->schema->drop('continents');
    }
}
