<?php

namespace App\Entity;

use App\Helper\TimeCalc;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TaskListRepository")
 * @ORM\Table(name="tasks_lists")
 */
class TaskList implements \JsonSerializable
{
    const STATUS_NEW = 1;
    const STATUS_DELETED = 20;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $eta;

    /**
     * @ORM\Column(type="integer")
     */
    protected $leftEta;

    /**
     * @ORM\Column(type="integer")
     */
    protected $spend;

    /**
     * @ORM\OneToMany(targetEntity="Group", mappedBy="taskList")
     */
    private $groups;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->setStatus(self::STATUS_NEW);
        $this->setSpend(0);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEta()
    {
        return $this->eta;
    }

    /**
     * @param mixed $eta
     * @return TaskList
     */
    public function setEta($eta)
    {
        $this->eta = $eta;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLeftEta()
    {
        return $this->leftEta;
    }

    /**
     * @param mixed $leftEta
     * @return TaskList
     */
    public function setLeftEta($leftEta)
    {
        $this->leftEta = $leftEta;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSpend()
    {
        return $this->spend;
    }

    /**
     * @param mixed $spend
     * @return TaskList
     */
    public function setSpend($spend)
    {
        $this->spend = $spend;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param mixed $groups
     * @return TaskList
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
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
            "eta" =>  TimeCalc::normalizeTime($this->eta),
            "spend" => TimeCalc::normalizeTime($this->spend),
            "groups" => $this->groups->toArray()
        ];
    }
}
