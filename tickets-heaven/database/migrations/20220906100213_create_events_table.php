<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventsTable extends Migration {

    public function up() {

        $this->schema->create('events', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('name');
            $table->text('description');
            $table->text('location')->nullable();
            $table->string('start_date');
            $table->string('start_time');
            $table->string('end_date');
            $table->string('end_time');
            $table->integer('host_id');
            $table->integer('venue_id');
            $table->string('event_picture')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        
        $this->schema->drop('events');
    }
}
