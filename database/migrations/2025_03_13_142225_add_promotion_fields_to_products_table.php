<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('promotion_price', 10, 2)->nullable();
            $table->date('promotion_start_date')->nullable();
            $table->date('promotion_end_date')->nullable();
            $table->json('promotion_history')->nullable();
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('promotion_price');
            $table->dropColumn('promotion_start_date');
            $table->dropColumn('promotion_end_date');
            $table->dropColumn('promotion_history');
        });
    }
};