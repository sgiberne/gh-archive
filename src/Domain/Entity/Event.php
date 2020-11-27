<?php

namespace App\Domain\Entity;

use App\Domain\Repository\EventRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=EventRepository::class)
 * @UniqueEntity("id")
 */
class Event
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="bigint", unique=true)
     * @Assert\NotBlank
     * @Groups("api")
     */
    private int $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @Groups("api")
     */
    private string $type;

    /**
     * @ORM\Column(type="json")
     * @Groups("api")
     */
    private array $payload = [];

    /**
     * @ORM\Column(type="boolean")
     * @Groups("api")
     */
    private bool $public = false;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank
     * @Groups("api")
     */
    private \DateTime $createdAt;


    /**
     * @ORM\ManyToOne(targetEntity="App\Domain\Entity\Repository", cascade={"persist"})
     * @Groups("api")
     */
    private Repository $repository;

    /**
     * @ORM\ManyToOne(targetEntity="App\Domain\Entity\Actor", cascade={"persist"})
     * @Groups("api")
     */
    private Actor $actor;

    /**
     * @ORM\ManyToOne(targetEntity="App\Domain\Entity\Organisation", cascade={"persist"})
     * @Groups("api")
     */
    private ?Organisation $organisation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): self
    {
        $this->public = $public;

        return $this;
    }

    public function getRepository(): Repository
    {
        return $this->repository;
    }

    public function setRepository(Repository $repository): self
    {
        $this->repository = $repository;

        return $this;
    }

    public function getActor(): Actor
    {
        return $this->actor;
    }

    public function setActor(Actor $actor): self
    {
        $this->actor = $actor;

        return $this;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?Organisation $organisation): self
    {
        $this->organisation = $organisation;

        return $this;
    }
}
