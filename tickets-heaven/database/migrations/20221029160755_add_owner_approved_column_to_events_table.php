<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddOwnerApprovedColumnToEventsTable extends Migration {
    
    public function up() {

      $this->schema->table('events', function (Blueprint $table) {
			  $table->boolean('owner_approved')->after('event_picture')->nullable();
		  });
    }

    public function down() {
        
      $this->schema->table('events', function (Blueprint $table) {
			  $table->dropColumn('owner_approved');
		  });
    }
}
