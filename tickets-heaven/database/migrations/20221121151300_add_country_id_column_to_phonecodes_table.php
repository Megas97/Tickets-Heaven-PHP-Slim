<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCountryIdColumnToPhoneCodesTable extends Migration {
    
    public function up() {

      $this->schema->table('phone_codes', function (Blueprint $table) {
			  $table->integer('country_id')->after('code');
		  });
    }

    public function down() {
        
      $this->schema->table('phone_codes', function (Blueprint $table) {
			  $table->dropColumn('country_id');
		  });
    }
}
