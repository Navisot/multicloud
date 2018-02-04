<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAwsvmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('awsvms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('instance_id');
            $table->string('vm');
            $table->string('ip_address')->nullable();
            $table->string('vm_size');
            $table->string('status')->nullable();
            $table->string('location');
            $table->string('vpc_id');
            $table->string('image_id');
            $table->string('security_group_id');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('awsvms');
    }
}
