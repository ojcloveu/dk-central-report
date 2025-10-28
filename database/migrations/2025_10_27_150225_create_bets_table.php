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
        Schema::create('bets', function (Blueprint $table) {
            $table->id();
            
            $table->string('account');
            $table->string('channel');
            $table->date('trandate');
            $table->string('master');
            $table->integer('min');
            $table->integer('max');
            $table->integer('count');
            $table->decimal('turnover', 12, 2);
            $table->decimal('winlose', 12, 2);
            $table->decimal('lp', 5, 2);
            
         
            $table->timestamps();
            

            $table->index(['trandate']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bets');
    }
};
