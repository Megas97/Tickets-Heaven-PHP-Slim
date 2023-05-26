<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddDeletedAtColumnToVenuesTable extends Migration {
    
    public function up() {

      $this->schema->table('venues', function (Blueprint $table) {
			  $table->timestamp('deleted_at')->after('updated_at')->nullable();
		  });
    }

    public function down() {
        
      $this->schema->table('venues', function (Blueprint $table) {
			  $table->dropColumn('deleted_at');
		  });
    }
}
