<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('other_names')->nullable();
            $table->boolean('admin')->default(false);
            $table->boolean('tutor')->default(false);
            $table->boolean('student')->default(false);
            $table->smallInteger('status')->default(0);
            $table->string('push_id')->nullable();
            $table->string('push_os')->nullable();
            $table->text('api_token')->nullable();
            $table->timestamp('email_verified')->nullable();
            $table->string('email_verify_code')->nullable();
            $table->timestamp('phone_verified')->nullable();
            $table->string('phone_verify_code')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
