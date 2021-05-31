<?php
/**
 * Thrown if a queue is not found.
 */

namespace Geniem\Queue\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class QueueNotFoundException
 *
 * @package Geniem\Queue\Exception
 */
class QueueNotFoundException extends Exception implements NotFoundExceptionInterface {}
