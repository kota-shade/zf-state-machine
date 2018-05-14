<?php
namespace Test\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
// use Rn5Core\Utils\GuidGenerator;

/**
 * Class PassTicketCar
 * @package Kpp\Entity
 *
 * @ORM\Table(name="pass_ticket_car",  indexes={
 *          @ORM\Index(name="del", columns={"del"}),
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="ticketId_idx", columns={"ticket_id"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="Kpp\Entity\Repository\PassTicketCar")
 */
class PassTicketCar
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
     * @var string
     * @ORM\Column(name="ticket_id", type="string", nullable=true,
     *      options={"comment": "идентификатор пропуска - напечатанный номер" } )
     */
    private $ticketId;

//    /**
//     * @var \Kpp\Entity\Employee
//     *
//     * @ORM\ManyToOne(targetEntity="Employee", inversedBy="carTickets")
//     * @ORM\JoinColumn(name="employee_id", referencedColumnName="id", nullable=true)
//     */
//    private $employee;

    /**
     * @var boolean
     * @ORM\Column(name="del", type="boolean", nullable=false,
     *      options={"default":"0", "comment": "признак удаления" } )
     */
    private $del = 0;

    /**
     * @var \DateTime
     * @ORM\Column(name="del_date", type="datetime", nullable=true,
     *      options={"comment": "дата удаления" } )
     */
    private $delDate;

    /**
     * @var \DateTime
     * @ORM\Column(name="create_date", type="datetime", nullable=false,
     *      options={"comment": "дата создания" } )
     */
    private $createDate;

    /**
     * @var \DateTime
     * @ORM\Column(name="edit_date", type="datetime", nullable=true,
     *      options={"comment": "дата изменения" } )
     */
    private $editDate;

    /**
     * @var \DateTime
     * @ORM\Column(name="issue_date", type="datetime", nullable=true,
     *      options={"comment": "дата выдачи" } )
     */
    private $issueDate;

//    /**
//     * @var User
//     *
//     * @ORM\ManyToOne(targetEntity="User")
//     * @ORM\JoinColumn(name="issue_user_id", referencedColumnName="id", nullable=true)
//     */
//    private $issueUser;

    /**
     * @var string|null
     *
     * @ORM\Column(name="comment", type="string", length=255, nullable=true)
     */
    private $comment;

    /**
     * @var \DateTime
     * @ORM\Column(name="start_date", type="datetime", nullable=false,
     *      options={"comment": "дата начала действия" } )
     */
    private $startDate;
    /**
     * @var \DateTime
     * @ORM\Column(name="end_date", type="datetime", nullable=false,
     *      options={"comment": "дата окончанияания действия" } )
     */
    private $endDate;

    /**
     * @var PassTicketStatus
     *
     * @ORM\ManyToOne(targetEntity="PassTicketStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pass_ticket_status_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $passTicketStatus;

//    /**
//     * @var Car
//     *
//     * @ORM\ManyToOne(targetEntity="Car", inversedBy="carTickets")
//     * @ORM\JoinColumn(name="car_id", referencedColumnName="id", nullable=false)
//     *
//     */
//    private $car;

//    /**
//     * @var ArrayCollection
//     * @ORM\OneToMany(targetEntity="PassTicketCar2GuardSite", mappedBy="passTicketCar", cascade={"persist"})
//     *
//     */
//    private $guardSites;

//    /**
//     * @var Orders
//     * @ORM\ManyToOne(targetEntity="Orders", inversedBy="carTickets")
//     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false)
//     */
//    private $order;

    public function __construct()
    {
        //$this->guardSites = new ArrayCollection();
    }

    public function __clone()
    {
        if ($this->getId()) {
            $this->setId(null);
        }
    }

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
     * @return bool
     */
    public function getDel()
    {
        return $this->isDel();
    }
    /**
     * @return boolean
     */
    public function isDel()
    {
        return $this->del;
    }

    /**
     * @param boolean $del
     * @return self
     */
    public function setDel($del)
    {
        $this->del = $del;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param \DateTime $createDate
     * @return self
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDelDate()
    {
        return $this->delDate;
    }

    /**
     * @param \DateTime $delDate
     * @return self
     */
    public function setDelDate($delDate)
    {
        $this->delDate = $delDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEditDate()
    {
        return $this->editDate;
    }

    /**
     * @param \DateTime $editDate
     * @return self
     */
    public function setEditDate($editDate)
    {
        $this->editDate = $editDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     * @return self
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getIssueDate()
    {
        return $this->issueDate;
    }

    /**
     * @param \DateTime $issueDate
     * @return self
     */
    public function setIssueDate($issueDate)
    {
        $this->issueDate = $issueDate;
        return $this;
    }

    /**
     * @return User
     */
    public function getIssueUser()
    {
        return $this->issueUser;
    }

    /**
     * @param User $issueUser
     * @return self
     */
    public function setIssueUser($issueUser)
    {
        $this->issueUser = $issueUser;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     * @return self
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return PassTicketStatus
     */
    public function getPassTicketStatus()
    {
        return $this->passTicketStatus;
    }

    /**
     * @param PassTicketStatus $passTicketStatus
     * @return self
     */
    public function setPassTicketStatus($passTicketStatus)
    {
        $this->passTicketStatus = $passTicketStatus;
        return $this;
    }

    /**
     * @return string
     */
    public function getTicketId()
    {
        return $this->ticketId;
    }

    /**
     * @param string $ticketId
     * @return self
     */
    public function setTicketId($ticketId)
    {
        $this->ticketId = $ticketId;
        return $this;
    }

    /**
     * @return \Kpp\Entity\Employee
     */
    public function getEmployee()
    {
        return $this->employee;
    }

    /**
     * @param \Kpp\Entity\Employee $employee
     * @return self
     */
    public function setEmployee($employee)
    {
        $this->employee = $employee;
        return $this;
    }

    /**
     * @return Car
     */
    public function getCar()
    {
        return $this->car;
    }

    /**
     * @param Car $car
     * @return self
     */
    public function setCar($car)
    {
        $this->car = $car;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getGuardSites()
    {
        return $this->guardSites;
    }

    /**
     * @param ArrayCollection $guardSites
     * @return self
     */
    public function setGuardSites($guardSites)
    {
        $this->guardSites = $guardSites;
        return $this;
    }

    /**
     * @return Orders
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Orders $order
     * @return self
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }
}
