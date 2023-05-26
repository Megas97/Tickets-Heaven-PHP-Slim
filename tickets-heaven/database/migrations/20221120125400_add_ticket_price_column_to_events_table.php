<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTicketPriceColumnToEventsTable extends Migration {
    
    public function up() {

      $this->schema->table('events', function (Blueprint $table) {
			  $table->float('ticket_price')->after('currency_id');
		  });
    }

    public function down() {
        
      $this->schema->table('events', function (Blueprint $table) {
			  $table->dropColumn('ticket_price');
		  });
    }
}
