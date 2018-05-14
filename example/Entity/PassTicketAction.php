<?php

namespace Test\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PassTicketAction
 *
 * @ORM\Table(name="pass_ticket_action")
 * @ORM\Entity
 */
class PassTicketAction
{

    /**
     * @var string
     *
     * @ORM\Column(name="id", type="string", length=64, nullable=false,
     *  options={"comment":"идентификатор действия"})
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
}
