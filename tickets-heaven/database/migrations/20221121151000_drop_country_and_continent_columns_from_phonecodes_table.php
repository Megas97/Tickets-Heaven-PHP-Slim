<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropCountryAndContinentColumnsFromPhoneCodesTable extends Migration {
    
    public function up() {

      $this->schema->table('phone_codes', function (Blueprint $table) {
        $table->dropColumn('country');
        $table->dropColumn('continent');
		  });
    }

    public function down() {
        
      $this->schema->table('phone_codes', function (Blueprint $table) {
			  $table->string('country')->after('code');
        $table->string('continent')->after('country');
		  });
    }
}
