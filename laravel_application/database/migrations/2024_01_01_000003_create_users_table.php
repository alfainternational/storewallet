<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone', 20)->unique();
            $table->string('password');
            $table->enum('role', ['buyer', 'merchant', 'shipping_company', 'admin'])->default('buyer');
            $table->foreignId('city_id')->nullable()->constrained('sudanese_cities')->onDelete('set null');
            $table->text('address')->nullable();
            $table->string('avatar')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_expatriate')->default(false);
            $table->string('country_of_residence')->nullable();
            $table->decimal('wallet_balance', 12, 2)->default(0);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['email', 'phone', 'role']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
