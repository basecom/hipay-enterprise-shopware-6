<?php

namespace HiPay\Payment\Enum;

interface CaptureStatus
{
    public const OPEN = 'OPEN';
    public const IN_PROGRESS = 'IN_PROGRESS';
    public const FAILED = 'FAILED';
    public const COMPLETED = 'COMPLETED';
    public const CANCEL = 'CANCEL';
}
