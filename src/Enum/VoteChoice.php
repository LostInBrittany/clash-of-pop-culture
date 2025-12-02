<?php

declare(strict_types=1);

namespace App\Enum;

enum VoteChoice: string
{
    case A = 'A';
    case B = 'B';

    public function getColor(): string
    {
        return match($this) {
            self::A => 'text-cyan-400 border-cyan-400 shadow-[0_0_20px_rgba(34,211,238,0.5)]',
            self::B => 'text-fuchsia-400 border-fuchsia-400 shadow-[0_0_20px_rgba(232,121,249,0.5)]',
        };
    }
}