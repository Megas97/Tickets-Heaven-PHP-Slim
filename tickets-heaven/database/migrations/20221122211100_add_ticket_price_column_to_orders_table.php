<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTicketPriceColumnToOrdersTable extends Migration {
    
    public function up() {

      $this->schema->table('orders', function (Blueprint $table) {
			  $table->float('ticket_price')->after('user_id');
		  });
    }

    public function down() {
        
      $this->schema->table('orders', function (Blueprint $table) {
			  $table->dropColumn('ticket_price');
		  });
    }
}
