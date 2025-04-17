<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestBreakTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_break_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('break_time_id')->constrained()->onDelete('cascade');
            $table->foreignId('correction_request_id')->constrained()->onDelete('cascade');
            $table->dateTime('corrected_break_start')->nullable();
            $table->dateTime('corrected_break_end')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_break_times');
    }
}
