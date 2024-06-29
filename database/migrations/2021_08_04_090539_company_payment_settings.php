<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HospitalPaymentSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'hospital_payment_settings', function (Blueprint $table){
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('value');
            $table->integer('created_by');
            $table->timestamps();
            $table->unique(
                [
                    'name',
                    'created_by',
                ]
            );
        }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hospital_payment_settings');
    }
}
