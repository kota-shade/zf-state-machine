<?php
namespace KotaShade\StateMachine\Exception;

use Throwable;

class StateMachineException extends \RuntimeException
{
    public function __construct($message = "State machine runtime error", $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
