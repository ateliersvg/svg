<?php

declare(strict_types=1);

namespace Atelier\Svg\Validation;

/**
 * Validation severity levels.
 */
enum ValidationSeverity: string
{
    case ERROR = 'error';
    case WARNING = 'warning';
    case INFO = 'info';
}
