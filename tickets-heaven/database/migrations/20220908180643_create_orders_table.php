<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrdersTable extends Migration {
    
    public function up() {

        $this->schema->create('orders', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('event_id');
            $table->integer('user_id');
            $table->integer('ticket_quantity');
            $table->string('tickets');
            $table->timestamps();
        });
    }

    public function down() {
        
        $this->schema->drop('orders');
    }
}
