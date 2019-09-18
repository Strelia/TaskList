<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupRepository")
 * @ORM\Table(name="groups")
 */
class Group extends Base implements \JsonSerializable
{
    const STATUS_NEW = 1;
    const STATUS_DELETED = 20;

    /**
     * @ORM\Column(type="integer")
     */
    protected $leftEta;

    /**
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="groups")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=true)
     */
    protected $parentGroup;

    /**
     * @ORM\ManyToOne(targetEntity="TaskList", inversedBy="groups")
     * @ORM\JoinColumn(name="task_list_id", referencedColumnName="id", nullable=true)
     */
    protected $taskList;

    /**
     * @ORM\OneToMany(targetEntity="Group", mappedBy="parentGroup")
     */
    private $groups;

    /**
     * @ORM\OneToMany(targetEntity="Task", mappedBy="parentGroup")
     */
    private $tasks;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->setStatus(self::STATUS_NEW);
        $this->setSpend(0);
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
     * @return Group
     */
    public function setLeftEta($leftEta)
    {
        $this->leftEta = $leftEta;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTaskList()
    {
        return $this->taskList;
    }

    /**
     * @param TaskList $taskList
     * @return Group
     */
    public function setTaskList(TaskList $taskList): Group
    {
        $this->taskList = $taskList;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param mixed $tasks
     * @return Group
     */
    public function setTasks($tasks)
    {
        $this->tasks = $tasks;
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
     * @return Group
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
        return $this;
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
            "eta" =>  TimeCalc::normalizeTime($this->eta),
            "spend" => TimeCalc::normalizeTime($this->spend),
            "tasks" => $this->tasks->toArray(),
            "groups" => $this->groups->toArray()
        ];
    }
}
