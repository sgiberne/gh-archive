<?php

namespace App\Domain\Entity;

use App\Domain\Repository\RepositoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=RepositoryRepository::class)
 * @UniqueEntity("id")
 */
class Repository
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
    private string $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups("api")
     */
    private ?string $url;

    /**
     * @ORM\OneToMany(targetEntity="App\Domain\Entity\Event", mappedBy="repository")
     */
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setRepository($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            if ($event->getRepository() === $this) {
                $event->setRepository(null);
            }
        }

        return $this;
    }
}
