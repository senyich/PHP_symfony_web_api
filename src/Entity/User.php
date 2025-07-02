<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Doctrine\DBAL\Types\Types;
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

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0, options: ['default' => '0'])]
    private ?string $walletBalance = '0';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $authToken = null;

    #[ORM\OneToMany(mappedBy: "owner", targetEntity: NFT::class)]
    private Collection $nfts;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->nfts = new ArrayCollection();
        $this->walletBalance = '330'; 
    }
    public function getNfts(): Collection
    {
        return $this->nfts;
    }
    public function getBalance(): string
    {
        return $this->walletBalance ?? '0'; 
    }

    public function setBalance(string $walletBalance): static
    {
        $this->walletBalance = $walletBalance;
        return $this;
    }
    public function addNft(NFT $nft): static
    {
        if (!$this->nfts->contains($nft)) {
            $this->nfts->add($nft);
            $nft->setOwner($this);
        }
        return $this;
    }
    public function removeNft(NFT $nft): static
    {
        if ($this->nfts->removeElement($nft)) {
            if ($nft->getOwner() === $this) {
                $nft->setOwner(null);
            }
        }
        return $this;
    }
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
