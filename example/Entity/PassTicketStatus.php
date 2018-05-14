<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 10.02.17
 * Time: 20:50
 */
namespace Test\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class PassTicketStatus
 * @package Test\Entity
 *
 * @ORM\Table(name="pass_ticket_status",  indexes={
 *      },
 *      uniqueConstraints={
 *      }
 * )
 * @ORM\Entity()
 */
class PassTicketStatus
{
    const STATUS_ACTIVE = 'active'; //действителен
    const STATUS_CANCELLED = 'cancelled'; // аннулирован
    const STATUS_DRAFT = 'draft'; //черновик
    const STATUS_EXPIRED = 'expired'; //истек
    const STATUS_ISSUED = 'issued'; // выдан
    const STATUS_RETURNED = 'returned'; // возвращен
    const STATUS_UNRESTORED = 'unrestored'; //не восстановлен

    /**
     * @var string
     *
     * @ORM\Column(name="id", type="string", length=32, nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", nullable=false,
     *      options={"comment": "название состояния" } )
     */
    private $name;

    /**
     * @var boolean
     * @ORM\Column(name="is_visible", type="boolean", nullable=false,
     *      options={"default":"1", "comment": "признак видимости" } )
     */
    private $isVisible = 1;

    public function __construct()
    {

    }

    public function __clone()
    {
        if ($this->getId()) {
            $this->setId(null);
        }
    }

    public function __toString()
    {
        return $this->getName();
    }

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
     * @return boolean
     */
    public function getIsVisible()
    {
        return $this->isVisible;
    }

    /**
     * @return bool
     */
    public function isIsVisible()
    {
        return $this->getIsVisible();
    }

    /**
     * @param boolean $isVisible
     * @return self
     */
    public function setIsVisible($isVisible)
    {
        $this->isVisible = $isVisible;
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
