<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->string("title");
            $table->string("email");
            $table->string("password");
            $table->string("frontend_website_link")->nullable();
            $table->string("address");
            $table->string("phone");
            $table->string("language")->nullable();
            $table->string("package_duration");
            $table->string("Country");
            $table->string("package");
            $table->string("patient_limit");
            $table->string("doctor_limit");
            $table->string("permitted_modules");
            $table->string("price");
            $table->string("deposit_type")->nullable();
            $table->string('do_you_want_trial_version')->nullable();
            $table->string('status');
            $table->string("logo")->nullable();
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
        Schema::dropIfExists('hospitals');
    }
};
