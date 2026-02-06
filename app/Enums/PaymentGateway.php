<?php

namespace App\Enums;

enum PaymentGateway: string
{
    case Manual = 'manual';
    case PayChangu = 'paychangu';
    case Stripe = 'stripe';
    case PayPal = 'paypal';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual Payment',
            self::PayChangu => 'PayChangu',
            self::Stripe => 'Stripe',
            self::PayPal => 'PayPal',
        };
    }

    public function isOnline(): bool
    {
        return $this !== self::Manual;
    }
}
