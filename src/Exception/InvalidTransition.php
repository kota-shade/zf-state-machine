<?php
namespace KotaShade\StateMachine\Exception;

use Throwable;
use KotaShade\StateMachine\Entity\TransitionAInterface;

class InvalidTransition extends StateMachineException
{
    public function __construct(TransitionAInterface $trAE, $code = 500, Throwable $previous = null)
    {
        $name = get_class($trAE);
        $msg = sprintf("need default target in transitionB for %s(id=%s)", $name, $trAE->getId());

        parent::__construct($msg, $code, $previous);
    }
}