<?php
namespace KotaShade\StateMachine\Exception;

use Throwable;

class LoopException extends StateMachineException
{
    protected $stack = [];

    public function __construct($message = "State machine infinity loop", $stack = [], $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->stack = $stack;
    }

    public function getStack()
    {
        return $this->stack;
    }
}
