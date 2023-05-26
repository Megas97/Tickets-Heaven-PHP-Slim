<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePhoneCodesTable extends Migration {

    public function up() {

        $this->schema->create('phone_codes', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('code');
            $table->string('country');
            $table->string('continent');
            $table->timestamps();
        });
    }

    public function down() {
        
        $this->schema->drop('phone_codes');
    }
}
