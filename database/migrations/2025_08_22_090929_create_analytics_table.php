<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('analytics', function (Blueprint $table) {
            $table->id();
            $table->integer('total_created')->default(0);
            $table->integer('total_completed')->default(0); 
            $table->integer('total_uncompleted')->default(0);
            $table->timestamps();
        });

        DB::table('analytics')->insert([
            'total_created' => 0,
            'total_completed' => 0,
            'total_uncompleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics');
    }
};
