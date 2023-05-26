<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCurrencyIdColumnToOrdersTable extends Migration {
    
    public function up() {

      $this->schema->table('orders', function (Blueprint $table) {
			  $table->integer('currency_id')->after('ticket_price');
		  });
    }

    public function down() {
        
      $this->schema->table('orders', function (Blueprint $table) {
			  $table->dropColumn('currency_id');
		  });
    }
}
