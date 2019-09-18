<?php

namespace App\Entity;

use App\Helper\TimeCalc;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base
 * @ORM\MappedSuperclass
 */
abstract class Base
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $name;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $status;

    /**
     * @ORM\Column(type="text", length=500, nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="integer")
     */
    protected $eta;

    /**
     * @ORM\Column(type="integer")
     */
    protected $spend;

    protected $parentGroup;

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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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
     */
    public function setEta($eta): void
    {
        $this->eta = $eta;
    }

    /**
     * @return int
     */
    public function getSpend()
    {
        return $this->spend;
    }

    /**
     * @param string $spend
     */
    public function setSpend($spend): void
    {
        $this->spend = $spend;
    }

    /**
     * @return mixed
     */
    public function getParentGroup()
    {
        return $this->parentGroup;
    }

    /**
     * @param mixed $parentGroup
     */
    public function setParentGroup($parentGroup): void
    {
        $this->parentGroup = $parentGroup;
    }
}