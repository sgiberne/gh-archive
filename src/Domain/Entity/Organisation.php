<?php

namespace App\Domain\Entity;

use App\Domain\Repository\OrganisationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=OrganisationRepository::class)
 * @UniqueEntity("id")
 */
class Organisation
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
    private string $login;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups("api")
     */
    private ?string $gravatarId;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups("api")
     */
    private ?string $url;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups("api")
     */
    private ?string $avatarUrl;

    /**
     * @ORM\OneToMany(targetEntity="App\Domain\Entity\Event", mappedBy="organisation")
     *
     * @var Collection<int, Event>
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

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getGravatarId(): ?string
    {
        return $this->gravatarId;
    }

    public function setGravatarId(?string $gravatarId): self
    {
        $this->gravatarId = $gravatarId;

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

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(?string $avatarUrl): self
    {
        $this->avatarUrl = $avatarUrl;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setOrganisation($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            if ($event->getOrganisation() === $this) {
                $event->setOrganisation(null);
            }
        }

        return $this;
    }
}
