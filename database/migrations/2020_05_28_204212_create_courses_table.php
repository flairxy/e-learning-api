<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('courses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->longText('description')->nullable();
            $table->string('language')->nullable();
            $table->smallInteger('status')->nullable();
            $table->unsignedBigInteger('level_id')->nullable();
            $table->string('pricing')->default(0);
            $table->longText('welcome_message')->nullable();
            $table->longText('congratulation_message')->nullable();
            $table->longText('overview')->nullable();
            $table->longText('prerequisites')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->string('image_url')->nullable();
            $table->uuid('created_by');
            $table->longText('instructors')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign(('category_id'))->references('id')->on('categories')->onDelete('cascade');
            $table->foreign(('level_id'))->references('id')->on('levels')->onDelete('cascade');
            $table->foreign(('created_by'))->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courses');
    }
}
