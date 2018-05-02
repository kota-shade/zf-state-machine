<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 26.02.18
 * Time: 14:16
 */

namespace KotaShade\StateMachine\Exception;


use Throwable;

class ActionNotAllowed extends StateMachineException
{
    protected $msgList = [];

    public function __construct($objE, $action, $msgList=[], $message = "action is not allowed", $code = 500, Throwable $previous = null)
    {
        $objName = get_class($objE);
        $objId = method_exists($objE, 'getId') ? $objE->getId() : 'unknown';
        $msg = sprintf("action is not allowed for object state, objName='%s' objId='%s' action='%s'",
            $objName, $objId, $action);
        parent::__construct($message, $code, $previous);

        $this->msgList = $msgList;
    }

    /**
     * @return array
     */
    public function getMessages() {
        return $this->msgList;
    }
}