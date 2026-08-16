<?php

namespace App\Enums;

enum AiCreditTransactionType: string
{
    case OpeningBalance = 'opening_balance';
    case Purchase = 'purchase';
    case PlanGrant = 'plan_grant';
    case PromoGrant = 'promo_grant';
    case AdminAdjustment = 'admin_adjustment';
    case Consumption = 'consumption';
    case Refund = 'refund';
    case Expiry = 'expiry';

    /**
     * What to call this on a ledger the user reads. Deliberately not the case
     * name: "promo_grant" is an accounting term, and the person looking at
     * their credits wants to know where five of them came from.
     */
    public function label(): string
    {
        return match ($this) {
            self::OpeningBalance => 'Opening balance',
            self::Purchase => 'Purchased',
            self::PlanGrant => 'Plan credits',
            self::PromoGrant => 'Welcome credits',
            self::AdminAdjustment => 'Adjustment',
            self::Consumption => 'Generation',
            self::Refund => 'Refunded',
            self::Expiry => 'Expired',
        };
    }
}
