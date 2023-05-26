<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCurrencyIdColumnToEventsTable extends Migration {
    
    public function up() {

      $this->schema->table('events', function (Blueprint $table) {
			  $table->integer('currency_id')->after('owner_approved');
		  });
    }

    public function down() {
        
      $this->schema->table('events', function (Blueprint $table) {
			  $table->dropColumn('currency_id');
		  });
    }
}
