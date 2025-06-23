<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    private ?string $price = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $createdTime = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "orders")]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\OneToOne(targetEntity: NFT::class, inversedBy: "order")]
    #[ORM\JoinColumn(nullable: false)]
    private ?NFT $nft = null;

    public function getNft(): ?NFT
    {
        return $this->nft;
    }
    public function setNft(NFT $nft): static
    {
        $this->nft = $nft;
        return $this;
    }
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;
        return $this;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedTime(): ?\DateTime
    {
        return $this->createdTime;
    }

    public function setCreatedTime(\DateTime $createdTime): static
    {
        $this->createdTime = $createdTime;

        return $this;
    }
}
