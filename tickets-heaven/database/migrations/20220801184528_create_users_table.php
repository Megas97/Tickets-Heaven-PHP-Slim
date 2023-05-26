<?php

use migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

    public function up() {

        $this->schema->create('users', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('username');
            $table->string('email');
            $table->string('first_name');
            $table->string('last_name');
            $table->integer('phone_code_id')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('address')->nullable();
            $table->text('description')->nullable();
            $table->string('password');
            $table->string('profile_picture')->nullable();
            $table->boolean('active');
            $table->string('active_hash')->nullable();
            $table->string('recover_hash')->nullable();
            $table->string('remember_identifier')->nullable();
            $table->string('remember_token')->nullable();
            $table->string('github_id')->nullable();
            $table->string('facebook_id')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        
        $this->schema->drop('users');
    }
}
