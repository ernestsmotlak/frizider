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
}
