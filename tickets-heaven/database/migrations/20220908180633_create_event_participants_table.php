<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventParticipantsTable extends Migration {

    public function up() {

        $this->schema->create('event_participants', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('event_id');
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    public function down() {
        
        $this->schema->drop('event_participants');
    }
}
