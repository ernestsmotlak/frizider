<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ai_user_data', function (Blueprint $table) {
            $table->renameColumn('can_generate_ai_calls', 'can_use_ai');
            $table->renameColumn('ai_calls_remaining', 'credit_balance');
        });

        // Opening-balance rows so SUM(ledger) == credit_balance holds from day one.
        DB::table('ai_user_data')
            ->where('credit_balance', '>', 0)
            ->orderBy('id')
            ->each(function ($row) {
                DB::table('ai_credit_transactions')->insert([
                    'user_id' => $row->user_id,
                    'amount' => $row->credit_balance,
                    'balance_after' => $row->credit_balance,
                    'type' => 'opening_balance',
                    'idempotency_key' => 'opening_balance',
                    'created_at' => now(),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('ai_credit_transactions')->where('type', 'opening_balance')->delete();

        Schema::table('ai_user_data', function (Blueprint $table) {
            $table->renameColumn('can_use_ai', 'can_generate_ai_calls');
            $table->renameColumn('credit_balance', 'ai_calls_remaining');
        });
    }
};
