<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enum\VoteChoice;
use Symfony\Component\Validator\Constraints as Assert;

class VoteDto
{
    public function __construct(
        #[Assert\NotNull]
        public VoteChoice $choice,
    ) {}
}