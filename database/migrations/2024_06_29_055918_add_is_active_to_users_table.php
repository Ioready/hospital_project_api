<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->integer('is_active')->default(0)->after('images');
            $table->string('plan')->default(0)->after('is_active');
            $table->string('created_by')->default(0)->after('plan');
            $table->string('is_enable_login')->default(0)->after('created_by');
            // $table->string('address')->default(0)->after('phone');
            
            

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
