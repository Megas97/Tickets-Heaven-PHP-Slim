<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCommentsTable extends Migration {
    
    public function up() {

        $this->schema->create('comments', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('user_id');
            $table->integer('event_id');
            $table->text('comment');
            $table->timestamps();
        });
    }

    public function down() {
        
        $this->schema->drop('comments');
    }
}
