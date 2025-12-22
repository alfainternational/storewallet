<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();
            $table->string('name');
            $table->string('name_ar');
            $table->string('symbol', 10);
            $table->decimal('exchange_rate', 12, 4)->default(1.0000);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('currencies');
    }
};
