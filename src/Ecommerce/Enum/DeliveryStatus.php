<?php

namespace App\Ecommerce\Enum;

enum DeliveryStatus: string
{
    case PENDING = 'pending';
    case PREPARING = 'preparing';
    case SHIPPED = 'shipped';
    case IN_TRANSIT = 'in_transit';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';

    /**
     * Get all possible values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::PREPARING => 'En préparation',
            self::SHIPPED => 'Expédiée',
            self::IN_TRANSIT => 'En transit',
            self::DELIVERED => 'Livrée',
            self::FAILED => 'Échec de livraison',
        };
    }
}
