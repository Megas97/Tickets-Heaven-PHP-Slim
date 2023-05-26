<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSettingsColumnToUsersTable extends Migration {
    
    public function up() {

      $this->schema->table('users', function (Blueprint $table) {
			  $table->text('settings')->after('profile_picture')->nullable();
		  });
    }

    public function down() {
        
      $this->schema->table('users', function (Blueprint $table) {
			  $table->dropColumn('settings');
		  });
    }
}
