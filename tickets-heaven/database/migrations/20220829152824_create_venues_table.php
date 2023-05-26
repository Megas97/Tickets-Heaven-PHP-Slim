<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVenuesTable extends Migration {

    public function up() {

        $this->schema->create('venues', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('name');
            $table->text('description');
            $table->text('address');
            $table->integer('phone_code_id');
            $table->string('phone_number');
            $table->string('opens');
            $table->string('closes');
            $table->integer('owner_id');
            $table->string('venue_picture')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        
        $this->schema->drop('venues');
    }
}
