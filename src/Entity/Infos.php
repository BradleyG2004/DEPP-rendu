<?php

namespace App\Entity;

use App\Repository\InfosRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InfosRepository::class)]
class Infos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $rank = null;

    #[ORM\Column(length: 255)]
    private ?string $victoire = null;

    #[ORM\Column(length: 255)]
    private ?string $defaite = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?user $user_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRank(): ?string
    {
        return $this->rank;
    }

    public function setRank(string $rank): static
    {
        $this->rank = $rank;

        return $this;
    }

    public function getVictoire(): ?string
    {
        return $this->victoire;
    }

    public function setVictoire(string $victoire): static
    {
        $this->victoire = $victoire;

        return $this;
    }

    public function getDefaite(): ?string
    {
        return $this->defaite;
    }

    public function setDefaite(string $defaite): static
    {
        $this->defaite = $defaite;

        return $this;
    }

    public function getUserId(): ?user
    {
        return $this->user_id;
    }

    public function setUserId(user $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }
}
