<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('correction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correction_request_id')->constrained()->onDelete('cascade');
            $table->morphs('correctable'); // correctable_id, correctable_type
            $table->dateTime('corrected_start')->nullable();
            $table->dateTime('corrected_end')->nullable();
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
        Schema::dropIfExists('correction_details');
    }
}
