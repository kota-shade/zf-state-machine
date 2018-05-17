<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 06.05.18
 * Time: 21:03
 */

namespace Test\StateMachine;

use KotaShade\StateMachine\Service\StateMachine;
use Test\Entity\TransitionATicketCar;
use Test\Entity\PassTicketAction;

class TicketCar extends StateMachine
{
    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository
     */
    protected function getTransitionARepository()
    {
        $ret = $this->em->getRepository(TransitionATicketCar::class);
        return $ret;
    }

    protected function getActionEntity($action)
    {
        $repo = $this->em->getRepository(PassTicketAction::class);
        $ret = $repo->find($action);
        return $ret;
    }

    /**
     * @param \Test\Entity\PassTicketCar $objE
     * @return mixed
     */
    protected function getObjectState($objE)
    {
        return $objE->getPassTicketStatus();
    }

    /**
     * @param \Test\Entity\PassTicketCar $objE
     * @param $stateE
     * @return $this
     */
    protected function setObjectState($objE, $stateE)
    {
        $objE->setPassTicketStatus($stateE);
        return $this;
    }
}