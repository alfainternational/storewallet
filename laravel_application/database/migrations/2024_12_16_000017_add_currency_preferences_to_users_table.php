<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCurrencyPreferencesToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('preferred_currency', 3)->default('SDG')->after('email');
            $table->string('country_code', 2)->nullable()->after('preferred_currency'); // ISO 3166-1 alpha-2
            $table->boolean('is_expatriate')->default(false)->after('country_code');
            $table->string('expatriate_location', 2)->nullable()->after('is_expatriate'); // Country code where they live

            $table->foreign('preferred_currency')->references('code')->on('currencies')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['preferred_currency']);
            $table->dropColumn(['preferred_currency', 'country_code', 'is_expatriate', 'expatriate_location']);
        });
    }
}
