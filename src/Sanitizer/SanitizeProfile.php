<?php

declare(strict_types=1);

namespace Atelier\Svg\Sanitizer;

enum SanitizeProfile
{
    case STRICT;
    case DEFAULT;
    case PERMISSIVE;
}
