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
        $this->getJson('/api/ai/credits/ledger')->assertUnauthorized();
    }

    /**
     * The ledger names what a credit bought. "Generation" is true of every
     * spend and useful about none of them, so a charge reads back as the
     * operation it paid for.
     */
    public function test_the_ledger_reads_back_newest_first_and_says_what_each_one_was(): void
    {
        $user = User::factory()->create();
        $credits = app(AiCreditService::class);
        $credits->grant($user->id, 5, AiCreditTransactionType::PromoGrant);

        $log = \App\Models\UserAiRecipeLog::create([
            'user_id' => $user->id,
            'action' => \App\Enums\AiOperation::PantryFromPhoto->value,
            'status' => \App\Enums\AiGenerationStatus::Pending,
        ]);

        $charge = $credits->spend($user->id, 1, $log, 'ledger-key', ['operation' => 'pantry_from_photo']);
        $credits->refund($charge, ['reason' => 'generation_failed']);

        $rows = $this->actingAs($user, 'api')
            ->getJson('/api/ai/credits/ledger')
            ->assertOk()
            ->assertJsonPath('has_more', false)
            // Rides along so the tab does not cost a second request to keep
            // the balance strip above it honest.
            ->assertJsonPath('credits_remaining', 5)
            ->json('transactions');

        $this->assertCount(3, $rows);

        // Newest first, and ordered by id so the refund cannot sort above the
        // charge it undoes when both land in the same second.
        $this->assertSame(['Refunded', 'Shelf scan', 'Welcome credits'], array_column($rows, 'label'));
        $this->assertSame([1, -1, 5], array_column($rows, 'amount'));
        $this->assertSame([5, 4, 5], array_column($rows, 'balance_after'));
    }

    public function test_another_users_ledger_is_not_yours(): void
    {
        $user = User::factory()->create();
        app(AiCreditService::class)->grant($user->id, 3, AiCreditTransactionType::PromoGrant);

        $stranger = User::factory()->create();
        app(AiCreditService::class)->grant($stranger->id, 9, AiCreditTransactionType::PromoGrant);

        $rows = $this->actingAs($user, 'api')
            ->getJson('/api/ai/credits/ledger')
            ->assertOk()
            ->json('transactions');

        $this->assertSame([3], array_column($rows, 'amount'));
    }
}
