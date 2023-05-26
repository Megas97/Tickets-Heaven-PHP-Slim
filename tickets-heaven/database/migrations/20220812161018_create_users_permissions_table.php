<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersPermissionsTable extends Migration {

    public function up() {

        $this->schema->create('users_permissions', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('user_id');
            $table->boolean('admin');
            $table->boolean('owner');
            $table->boolean('host');
            $table->boolean('artist');
            $table->boolean('user');
            $table->timestamps();
        });
    }

    public function down() {
        
        $this->schema->drop('users_permissions');
    }
}
