<?php

namespace Test\Entity;

use Doctrine\ORM\Mapping as ORM;
use KotaShade\StateMachine\Entity\TransitionAInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Test\Entity as EntityNS;

/**
 * Class TransitionATicketCar
 * @package Test\Entity\StateMachine
 *
 * @ORM\Table(name="tr_a_ticket_car",  indexes={
 *
 *      },
 *      uniqueConstraints={
 *
 *      }
 * )
 * @ORM\Entity()
 *
 */
class TransitionATicketCar implements TransitionAInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var EntityNS\PassTicketStatus
     *
     * @ORM\ManyToOne(targetEntity="Test\Entity\PassTicketStatus")
     * @ORM\JoinColumn(name="src_id", referencedColumnName="id", nullable=false)
     */
    private $src;

    /**
     * @var EntityNS\PassTicketAction
     *
     * @ORM\ManyToOne(targetEntity="Test\Entity\PassTicketAction")
     * @ORM\JoinColumn(name="action_id", referencedColumnName="id", nullable=false)
     */
    private $action;

    /**
     * @var string
     * @ORM\Column(name="condition", type="string", nullable=true,
     *      options={ "comment": "Валидатор доступности данного действия"} )
     */
    private $condition;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="TransitionBTicketCar",
     *  mappedBy="transitionA")
     */
    private $transitionsB;

    //===========================
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return EntityNS\PassTicketAction
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param EntityNS\PassTicketAction $action
     * @return self
     */
    public function setAction(EntityNS\PassTicketAction $action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param string $condition
     * @return self
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * @return EntityNS\PassTicketStatus
     */
    public function getSrc()
    {
        return $this->src;
    }

    /**
     * @param EntityNS\PassTicketStatus $src
     * @return self
     */
    public function setSrc(EntityNS\PassTicketStatus $src)
    {
        $this->src = $src;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getTransitionsB()
    {
        return $this->transitionsB;
    }

    /**
     * @param ArrayCollection $transitionsB
     * @return self
     */
    public function setTransitionsB($transitionsB)
    {
        $this->transitionsB = $transitionsB;
        return $this;
    }
}
