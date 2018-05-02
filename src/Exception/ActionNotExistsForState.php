<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 26.02.18
 * Time: 13:49
 */
namespace KotaShade\StateMachine\Exception;

use Throwable;

class ActionNotExistsForState extends StateMachineException
{
    public function __construct($objE, $action, $code = 500, Throwable $previous = null)
    {
        $objName = get_class($objE);
        $objId = method_exists($objE, 'getId') ? $objE->getId() : 'unknown';

        $msg = sprintf("action doesn't exists for object state, objName='%s' objId='%s' action='%s'",
            $objName, $objId, $action);
        parent::__construct($msg, $code, $previous);
    }
}