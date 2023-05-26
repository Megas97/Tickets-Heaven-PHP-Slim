<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePromoCodesTable extends Migration {

    public function up() {

        $this->schema->create('promo_codes', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('event_id');
            $table->string('code');
            $table->float('percent');
            $table->timestamp('deadline')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        
        $this->schema->drop('promo_codes');
    }
}
