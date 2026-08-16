<?php

namespace Tests\Feature;

use App\Enums\AiCreditTransactionType;
use App\Models\AiUserData;
use App\Models\User;
use App\Services\AiCreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Asking what you have left.
 *
 * The number was only ever returned as a side effect of spending, so a session
 * that had not yet run a generation could not know it — and found out from a
 * 402 in the middle of a scan.
 */
class AiCreditBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_balance_is_readable_without_spending_anything(): void
    {
        $user = User::factory()->create();
        app(AiCreditService::class)->grant($user->id, 5, AiCreditTransactionType::PromoGrant);
        AiUserData::where('user_id', $user->id)->update(['can_use_ai' => true]);

        $this->actingAs($user, 'api')
            ->getJson('/api/ai/credits')
            ->assertOk()
            ->assertJsonPath('credits_remaining', 5)
            ->assertJsonPath('can_use_ai', true);
    }

    /**
     * Spent out and never switched on both read zero. Only one of them is
     * worth offering a way to top up.
     */
    public function test_a_user_without_ai_reads_zero_and_says_so(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/ai/credits')
            ->assertOk()
            ->assertJsonPath('credits_remaining', 0)
            ->assertJsonPath('can_use_ai', false);
    }

    public function test_the_balance_follows_a_spend(): void
    {
        $user = User::factory()->create();
        $credits = app(AiCreditService::class);
        $credits->grant($user->id, 2, AiCreditTransactionType::PromoGrant);

        $log = \App\Models\UserAiRecipeLog::create([
            'user_id' => $user->id,
            'action' => \App\Enums\AiOperation::PantryFromPhoto->value,
            'status' => \App\Enums\AiGenerationStatus::Pending,
        ]);

        $credits->spend($user->id, 1, $log, 'balance-key');

        $this->actingAs($user, 'api')
            ->getJson('/api/ai/credits')
            ->assertOk()
            ->assertJsonPath('credits_remaining', 1);
    }

    public function test_a_guest_gets_nothing(): void
    {
        $this->getJson('/api/ai/credits')->assertUnauthorized();
    }
}
