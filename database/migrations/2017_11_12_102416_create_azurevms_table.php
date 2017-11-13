<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAzurevmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('azurevms', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('user_id');
            $table->string('vm');
            $table->string('admin_username');
            $table->string('ip_address');
            $table->string('virtual_network');
            $table->string('virtual_network_interface');
            $table->string('os_type');
            $table->string('os_disk');
            $table->integer('os_disk_size');
            $table->string('vm_size');
            $table->string('status')->nullable();
            $table->string('location');
            $table->string('ip_label');
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
        Schema::dropIfExists('azurevms');
    }
}
