<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddDefaultCurrencyIdColumnToUsersTable extends Migration {
    
    public function up() {

      $this->schema->table('users', function (Blueprint $table) {
			  $table->integer('default_currency_id')->after('phone_number');
		  });
    }

    public function down() {
        
      $this->schema->table('users', function (Blueprint $table) {
			  $table->dropColumn('default_currency_id');
		  });
    }
}
