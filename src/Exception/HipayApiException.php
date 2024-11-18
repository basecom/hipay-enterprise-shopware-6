<?php

namespace HiPay\Payment\Exception;

class HipayApiException extends \Exception
{
    public function __construct(
        private string $message,
        private string $code = 0,
        private ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
