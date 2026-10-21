<?php

declare(strict_types=1);

namespace Daris\Exception;

use RuntimeException;

/**
 * Exception de base de l'application : permet d'attraper « tout Daris » d'un coup.
 */
class DarisException extends RuntimeException
{
}
