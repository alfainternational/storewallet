<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEscrowFieldsToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('escrow_trx')->nullable()->after('status');
            $table->enum('escrow_status', ['pending', 'held', 'released', 'refunded'])->default('pending')->after('escrow_trx');
            $table->decimal('escrow_amount', 10, 2)->default(0)->after('escrow_status');
            $table->string('release_trx')->nullable()->after('escrow_amount');
            $table->timestamp('released_at')->nullable()->after('release_trx');
            $table->string('refund_trx')->nullable()->after('released_at');
            $table->timestamp('refunded_at')->nullable()->after('refund_trx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'escrow_trx',
                'escrow_status',
                'escrow_amount',
                'release_trx',
                'released_at',
                'refund_trx',
                'refunded_at'
            ]);
        });
    }
}
