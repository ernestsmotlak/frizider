<?php

namespace App\Services;

use App\Enums\AiCreditTransactionType;
use App\Exceptions\InsufficientCreditsException;
use App\Models\AiCreditTransaction;
use App\Models\AiUserData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AiCreditService
{
    /**
     * Cached balance. Cheap read.
     */
    public function balance(int $userId): int
    {
        return (int)(AiUserData::where('user_id', $userId)->value('credit_balance') ?? 0);
    }

    /**
     * Add credits: purchase, plan grant, promo, admin adjustment.
     */
    public function grant(
        int                     $userId,
        int                     $amount,
        AiCreditTransactionType $type,
        ?Model                  $reference = null,
        ?string                 $idempotencyKey = null,
        array                   $metadata = [],
    ): AiCreditTransaction
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Grant amount must be positive.');
        }

        return $this->record($userId, $amount, $type, $reference, $idempotencyKey, $metadata);
    }

    /**
     * Deduct credits. Throws without touching the balance if there are not enough.
     */
    public function spend(
        int     $userId,
        int     $amount,
        ?Model  $reference = null,
        ?string $idempotencyKey = null,
        array   $metadata = [],
    ): AiCreditTransaction
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Spend amount must be positive.');
        }

        return $this->record(
            $userId,
            -$amount,
            AiCreditTransactionType::Consumption,
            $reference,
            $idempotencyKey,
            $metadata,
        );
    }

    /**
     * Give back a charge. Idempotent per charge — a charge can only be refunded once.
     */
    public function refund(AiCreditTransaction $charge, array $metadata = []): AiCreditTransaction
    {
        if ($charge->amount >= 0) {
            throw new InvalidArgumentException('Only a consumption can be refunded.');
        }

        return $this->record(
            $charge->user_id,
            abs($charge->amount),
            AiCreditTransactionType::Refund,
            $charge,
            'refund:' . $charge->id,
            $metadata,
        );
    }

    /**
     * The single write path for every balance change.
     */
    private function record(
        int                     $userId,
        int                     $delta,
        AiCreditTransactionType $type,
        ?Model                  $reference,
        ?string                 $idempotencyKey,
        array                   $metadata,
    ): AiCreditTransaction
    {
        if ($idempotencyKey !== null && $existing = $this->findByIdempotencyKey($userId, $idempotencyKey)) {
            return $existing;
        }

        try {
            return DB::transaction(function () use ($userId, $delta, $type, $reference, $idempotencyKey, $metadata) {
                $data = $this->lockRow($userId, createIfMissing: $delta > 0);

                if ($data === null) {
                    throw new InsufficientCreditsException(abs($delta), 0);
                }

                $balanceAfter = $data->credit_balance + $delta;

                if ($balanceAfter < 0) {
                    throw new InsufficientCreditsException(abs($delta), $data->credit_balance);
                }

                $data->credit_balance = $balanceAfter;
                $data->save();

                return AiCreditTransaction::forceCreate([
                    'user_id' => $userId,
                    'amount' => $delta,
                    'balance_after' => $balanceAfter,
                    'type' => $type,
                    'reference_type' => $reference?->getMorphClass(),
                    'reference_id' => $reference?->getKey(),
                    'idempotency_key' => $idempotencyKey,
                    'metadata' => $metadata ?: null,
                ]);
            });
        } catch (UniqueConstraintViolationException $error) {
            if ($idempotencyKey === null) {
                throw $error;
            }

            return $this->findByIdempotencyKey($userId, $idempotencyKey) ?? throw $error;
        }
    }

    private function lockRow(int $userId, bool $createIfMissing): ?AiUserData
    {
        $row = AiUserData::where('user_id', $userId)->lockForUpdate()->first();

        if ($row !== null || !$createIfMissing) {
            return $row;
        }

        try {
            AiUserData::forceCreate([
                'user_id' => $userId,
                'can_use_ai' => false,
                'credit_balance' => 0,
            ]);
        } catch (UniqueConstraintViolationException) {
            // Another request created it first; fall through and lock theirs.
        }

        return AiUserData::where('user_id', $userId)->lockForUpdate()->first();
    }

    private function findByIdempotencyKey(int $userId, string $key): ?AiCreditTransaction
    {
        return AiCreditTransaction::where('user_id', $userId)
            ->where('idempotency_key', $key)
            ->first();
    }
}
