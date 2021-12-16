<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_contents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('course_id');
            $table->unsignedBigInteger('section_id');
            $table->string('title');
            $table->longText('text')->nullable();
            // $table->unsignedBigInteger('duration')->nullable();
            $table->boolean('required')->default(false);
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('course_sections')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_contents');
    }
}
