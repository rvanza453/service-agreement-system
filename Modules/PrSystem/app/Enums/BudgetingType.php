<?php

namespace Modules\PrSystem\Enums;

enum BudgetingType: string
{
    case STATION = 'station';
    case JOB_COA = 'job_coa';

    public function label(): string
    {
        return match($this) {
            self::STATION => 'Station/Afdeling Based',
            self::JOB_COA => 'Job COA Based',
        };
    }
}
