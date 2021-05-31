<?php
/**
 * Thrown for errors with the queue container.
 */

namespace Geniem\Queue\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;

/**
 * Class QueueContainerException
 *
 * @package Geniem\Queue\Exception
 */
class QueueContainerException extends Exception implements ContainerExceptionInterface {}
