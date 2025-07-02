<?php

namespace App\Entity;

use App\Repository\NFTRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
#[ORM\Entity(repositoryClass: NFTRepository::class)]
class NFT
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $pattern = null;

    #[ORM\Column(length: 255)]
    private ?string $collection = null;

    #[ORM\OneToOne(mappedBy: "nft", targetEntity: Order::class)]
    private ?Order $order = null;
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "nfts")]
    private ?User $owner = null;
    public function getOwner(): ?User
    {
        return $this->owner;
    }
    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;
        return $this;
    }
    public function getOrder(): ?Order
    {
        return $this->order;
    }
    public function setOrder(?Order $order): static
    {
        $this->order = $order;
        return $this;
    }
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function setPattern(string $pattern): static
    {
        $this->pattern = $pattern;
        return $this;
    }
    public function setCollection(string $collection): static
    {
        $this->collection = $collection;
        return $this;
    }
    public function getCollection(): string
    {
        return $this->collection ;
    }
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getName(): ?string
    {
        return $this->name;
    }
    public function getPattern(): ?string
    {
        return $this->pattern;
    }

}
