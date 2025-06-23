<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 30, nullable: false)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $passwordHash = null;

    #[ORM\OneToMany(mappedBy: "owner", targetEntity: Order::class, cascade: ["persist", "remove"])]
    private Collection $orders;
     #[ORM\Column(length: 255, nullable: true)]
    private ?string $authToken = null;

    public function getAuthToken(): ?string
    {
        return $this->authToken;
    }

    public function setAuthToken(?string $authToken): static
    {
        $this->authToken = $authToken;
        return $this;
    }
    public function getPassword(): ?string
    {
        return $this->passwordHash;
    }
    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getName(): ?string
    {
        return $this->name;
    }
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }
    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }
    public function setPasswordHash(string $passwordHash): static
    {
        $this->passwordHash = $passwordHash;

        return $this;
    }
    public function getOrders(): Collection
    {
        return $this->orders;
    }
    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setOwner($this);
        }

        return $this;
    }
    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            if ($order->getOwner() === $this) {
                $order->setOwner(null);
            }
        }

        return $this;
    }
}
