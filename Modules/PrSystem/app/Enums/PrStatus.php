<?php

namespace Modules\PrSystem\Enums;

enum PrStatus: string
{
    case DRAFT = 'Draft';
    case PENDING = 'Pending';

    case ON_HOLD = 'On Hold';
    case APPROVED = 'Approved'; // Fully approved
    case REJECTED = 'Rejected';
    case PO_CREATED = 'PO Created';
    case COMPLETED = 'Completed';
    case CANCELLED = 'Cancelled';

    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'gray',
            self::PENDING => 'yellow',
            self::ON_HOLD => 'orange',
            self::APPROVED => 'green',
            self::REJECTED => 'red',
            self::PO_CREATED => 'blue',
            self::COMPLETED => 'green',
            self::CANCELLED => 'red',
        };
    }
}
