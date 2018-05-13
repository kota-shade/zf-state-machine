<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 13.05.18
 * Time: 19:17
 */

namespace KotaShade\StateMachine\Service;

use KotaShade\StateMachine\Exception\LoopException;

/**
 * stack of object and it state to prevent looping of state machine
 * Class CallStack
 * @package KotaShade\StateMachine\Service
 */
class CallStack
{
    static protected $stack=[];

    public function __construct($objE, $state)
    {
        /** @var StackItem $item */
        foreach (self::$stack as $item) {
            if ($item->getObjE() === $objE && $item->getState() === $state) {
                throw new LoopException('Infinity loop', self::$stack);
            }
        }

        array_push(self::$stack, new StackItem($objE, $state));
    }

    public function __destruct()
    {
        array_pop(self::$stack);
    }
}