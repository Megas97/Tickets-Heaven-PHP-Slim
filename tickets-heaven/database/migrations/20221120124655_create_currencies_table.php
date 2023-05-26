<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCurrenciesTable extends Migration {

    public function up() {

        $this->schema->create('currencies', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('code');
            $table->timestamps();
        });
    }

    public function down() {
        
        $this->schema->drop('currencies');
    }
}
