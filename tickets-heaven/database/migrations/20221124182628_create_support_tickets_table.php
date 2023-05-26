<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSupportTicketsTable extends Migration {

    public function up() {

        $this->schema->create('support_tickets', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('user_id');
            $table->string('guest_info')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down() {
        
        $this->schema->drop('support_tickets');
    }
}
