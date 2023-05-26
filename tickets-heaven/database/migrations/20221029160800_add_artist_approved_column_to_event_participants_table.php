<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddArtistApprovedColumnToEventParticipantsTable extends Migration {
    
    public function up() {

      $this->schema->table('event_participants', function (Blueprint $table) {
			  $table->boolean('artist_approved')->after('user_id')->nullable();
		  });
    }

    public function down() {
        
      $this->schema->table('event_participants', function (Blueprint $table) {
			  $table->dropColumn('artist_approved');
		  });
    }
}
