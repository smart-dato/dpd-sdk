<?php

namespace SmartDato\Dpd\Enums;

enum ShipmentStatus: string
{
    case CREATED = 'created';
    case IN_TRANSIT = 'in_transit';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case EXCEPTION = 'exception';
    case RETURNED = 'returned';
}
