<?php

namespace Laraditz\Bayar\Enums;

enum PaymentStatus: int
{
    case Pending        = 1;
    case Processing     = 2;
    case Completed      = 3;
    case Failed         = 4;
    case Cancelled      = 5;
    case Refunded       = 6;
    case Others         = 99;
}
