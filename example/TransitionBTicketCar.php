<?php

namespace Test\Entity;

use Doctrine\ORM\Mapping as ORM;
use KotaShade\StateMachine\Entity\TransitionBInterface;
use Test\Entity as EntityNS;

/**
 * Class TransitionB
 * @package Test\Entity\StateMachine
 *
 * @ORM\Table(name="kpp_tr_b_ticket_car",  indexes={
 *
 *      },
 *      uniqueConstraints={
 *
 *      }
 * )
 * @ORM\Entity()
 *
 */
class TransitionBTicketCar implements TransitionBInterface
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
     * @var TransitionATicketCar
     * @ORM\ManyToOne(targetEntity="TransitionATicketCar", inversedBy="transitionsB")
     * @ORM\JoinColumn(name="transition_a_id", referencedColumnName="id", nullable=false)
     */
    private $transitionA;

    /**
     * @var int
     * @ORM\Column(name="weight", type="integer", nullable=true,
     *      options={"comment": "задает порядок проверки,больше-раньше проверяется, null-переход по умолчанию"})
     */
    private $weight;

    /**
     * @var EntityNS\PassTicketStatus
     *
     * @ORM\ManyToOne(targetEntity="Test\Entity\PassTicketStatus")
     * @ORM\JoinColumn(name="dst_id", referencedColumnName="id", nullable=false)
     */
    private $dst;

    /**
     * @var string
     * @ORM\Column(name="condition", type="string", nullable=true,
     *      options={ "comment": "Валидатор доступности данного перехода"} )
     */
    private $condition;

    /**
     * @var string
     * @ORM\Column(name="pre_functor", type="string", nullable=true,
     *      options={"comment": "имя функтора, содержащего действия, выполняемые до перехода"})
     */
    private $preFunctor;

    /**
     * @var string
     * @ORM\Column(name="post_functor", type="string", nullable=true,
     *      options={"comment": "имя функтора, содержащего действия, выполняемые после перехода"})
     */
    private $postFunctor;

    //====================================
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
    public function getDst()
    {
        return $this->dst;
    }

    /**
     * @param EntityNS\PassTicketStatus $dst
     * @return self
     */
    public function setDst(EntityNS\PassTicketStatus $dst)
    {
        $this->dst = $dst;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostFunctor()
    {
        return $this->postFunctor;
    }

    /**
     * @param string $postFunctor
     * @return self
     */
    public function setPostFunctor($postFunctor)
    {
        $this->postFunctor = $postFunctor;
        return $this;
    }

    /**
     * @return string
     */
    public function getPreFunctor()
    {
        return $this->preFunctor;
    }

    /**
     * @param string $preFunctor
     * @return self
     */
    public function setPreFunctor($preFunctor)
    {
        $this->preFunctor = $preFunctor;
        return $this;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     * @return self
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return TransitionATicketCar
     */
    public function getTransitionA()
    {
        return $this->transitionA;
    }

    /**
     * @param TransitionATicketCar $transitionA
     * @return self
     */
    public function setTransitionA($transitionA)
    {
        $this->transitionA = $transitionA;
        return $this;
    }
}
