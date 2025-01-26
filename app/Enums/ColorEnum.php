<?php

declare(strict_types=1);

namespace App\Enums;

enum ColorEnum: int
{
    case RED = 1;

    case GREEN = 2;

    case BLUE = 3;

    public function toString(): string
    {
        return match ($this) {
          self::RED => 'Red',
          self::GREEN => 'Green',
          self::BLUE => 'Blue',
        };
    }
}
