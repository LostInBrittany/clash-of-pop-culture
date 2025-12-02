<?php

namespace App\Entity;

use App\Repository\BattleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BattleRepository::class)]
class Battle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $optionA = null;

    #[ORM\Column(length: 255)]
    private ?string $optionB = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOptionA(): ?string
    {
        return $this->optionA;
    }

    public function setOptionA(string $optionA): static
    {
        $this->optionA = $optionA;

        return $this;
    }

    public function getOptionB(): ?string
    {
        return $this->optionB;
    }

    public function setOptionB(string $optionB): static
    {
        $this->optionB = $optionB;

        return $this;
    }
}
