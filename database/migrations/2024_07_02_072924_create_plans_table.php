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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('card_label')->nullable();
            $table->string('card_title')->nullable();
            $table->string('title_description')->nullable();
            $table->string('price')->nullable();
            $table->string('type')->nullable();
            $table->text('exclusive_and_including_tax')->nullable();
            $table->string('tax_name')->nullable();
            $table->string('tax_percentage')->nullable();
            $table->string('text_area')->nullable();
            $table->string('button_name')->nullable();
            $table->string('button_link')->nullable();
            $table->string('feature_title')->nullable();
            $table->text('feature_list')->nullable();
            $table->string('user_permission')->nullable();
            $table->string('permission_by_module')->nullable();
            $table->string('images')->nullable();
            $table->string('status');
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
        Schema::dropIfExists('plans');
    }
};
