<?php

namespace App\Core\Enums;

enum ReportContextConstant
{
    case PERIOD;
    case PERIOD_RAW;
    case PERIOD_YEAR;
    case PERIOD_MONTH;
    case PERIOD_MONTH_NAME;
    case COMPANY_ID;
    case COMPANY_CODE;
    case COMPANY_NAME;
    case COMPANY_NAME_FULL;
    case CONTRACT_NUMBER;
    case CONTRACT_DATE;
}
