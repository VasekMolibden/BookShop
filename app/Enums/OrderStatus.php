<?php

namespace App\Enums;

/**
 * Order lifecycle states for the bookshop.
 */
enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Cancelled = 'cancelled';
}
