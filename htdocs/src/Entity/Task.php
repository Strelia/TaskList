<?php

namespace App\Entity;

use App\Helper\TimeCalc;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TaskRepository")
 * @ORM\Table(name="tasks")
 * @ORM\HasLifecycleCallbacks
 */
class Task extends Base implements \JsonSerializable
{
    const STATUS_NEW = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_PENDING = 3;
    const STATUS_DONE = 4;
    const STATUS_DELETED = 20;

    const CHOICES_STATUS = [
        'new' => self::STATUS_NEW,
        'active' => self::STATUS_ACTIVE,
        'peding' => self::STATUS_PENDING,
        'done' => self::STATUS_DONE,
    ];

    /**
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="tasks")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=true)
     */
    protected $parentGroup;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="time")
     */
    protected $spend;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
        $this->setStatus(Task::STATUS_NEW);
        $this->setSpend(0);
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return Task
     */
    public function setCreatedAt(\DateTime $createdAt): Task
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return Task
     */
    public function setUpdatedAt(\DateTime $updatedAt): Task
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps(): void
    {
        $this->setUpdatedAt(new \DateTime('now'));
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTime('now'));
        }
    }

    static function getStatusString($status)
    {
        $arr = array_merge([], self::CHOICES_STATUS);
        array_flip($arr);
        return $arr[$status];
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            "name" => $this->name,
            "description" => $this->description,
            "eta" => TimeCalc::normalizeTime($this->eta),
            "spend" => TimeCalc::normalizeTime($this->spend),
            "status" => self::getStatusString($this->status)
        ];
    }
}
