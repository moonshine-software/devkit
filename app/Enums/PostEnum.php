<?php

declare(strict_types=1);

namespace App\Enums;

enum PostEnum: string
{
    case RED = 'red';

    case GREEN = 'green';

    case BLUE = 'blue';

    public function toString(): string
    {
        return match ($this) {
          self::RED => 'Red',
          self::GREEN => 'Green',
          self::BLUE => 'Blue',
        };
    }
}
