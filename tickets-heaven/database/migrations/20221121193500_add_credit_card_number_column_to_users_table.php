<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCreditCardNumberColumnToUsersTable extends Migration {
    
    public function up() {

      $this->schema->table('users', function (Blueprint $table) {
			  $table->string('credit_card_number')->after('phone_number')->nullable();
		  });
    }

    public function down() {
        
      $this->schema->table('users', function (Blueprint $table) {
			  $table->dropColumn('credit_card_number');
		  });
    }
}
