<?php
/**
 * The logger.
 */

namespace Geniem\Queue;

use Psr\Log\LoggerInterface;

/**
 * Class Logger
 *
 * @package Geniem\Queue
 */
class Logger implements LoggerInterface {

    /**
     * The log level defines which entries are logged.
     * If log level is higher than the message level, the log entry is omitted.
     *
     * To change the log level, define the "LOG_LEVEL" constant or pass it as a constructor argument.
     *
     * Levels:
     *
     * DEBUG (100)
     * INFO (200)
     * NOTICE (250)
     * WARNING (300)
     * ERROR (400)
     * CRITICAL (500)
     * ALERT (550)
     * EMERGENCY (600)
     *
     * Defaults to DEBUG(100), meaning all entries are logged.
     *
     * @var int
     */
    private $log_level = 100;

    /**
     * The DEBUG log level.
     */
    const DEBUG = 100;

    /**
     * The INFO log level.
     */
    const INFO = 200;

    /**
     * The NOTICE log level.
     */
    const NOTICE = 250;

    /**
     * The WARNING log level.
     */
    const WARNING = 300;

    /**
     * The ERROR log level.
     */
    const ERROR = 400;

    /**
     * The CRITICAL log level.
     */
    const CRITICAL = 500;

    /**
     * The ALERT log level.
     */
    const ALERT = 550;

    /**
     * The EMERGENCY log level.
     */
    const EMERGENCY = 600;

    /**
     * Logger constructor.
     *
     * Sets the log level from the constant or the passed argument.
     *
     * @param int|null $log_level The log level to use.
     */
    public function __construct( ?int $log_level = 0 ) {
        // Set from the constant.
        if ( defined( 'GENIEM_LOG_LEVEL' ) ) {
            $this->log_level = GENIEM_LOG_LEVEL;
        }
        // Set from the argument.
        if ( $log_level ) {
            $this->log_level = $log_level;
        }
    }

    /**
     * Log a debug message.
     *
     * @param string $message The log message.
     * @param array  $context The error context data.
     */
    public function debug( $message, array $context = [] ) {
        if ( static::DEBUG >= $this->log_level ) {
            $this->log( 'DEBUG', $message, $context );
        }
    }

    /**
     * Log an info message.
     *
     * @param string $message The log message.
     * @param array  $context The error context data.
     */
    public function info( $message, array $context = [] ) {
        if ( static::INFO >= $this->log_level ) {
            $this->log( 'INFO', $message, $context );
        }
    }

    /**
     * Log a notice message.
     *
     * @param string $message The log message.
     * @param array  $context The error context data.
     */
    public function notice( $message, array $context = [] ) {
        if ( static::NOTICE >= $this->log_level ) {
            $this->log( 'NOTICE', $message, $context );
        }
    }

    /**
     * Log a warning message.
     *
     * @param string $message The log message.
     * @param array  $context The error context data.
     */
    public function warning( $message, array $context = [] ) {
        if ( static::WARNING >= $this->log_level ) {
            $this->log( 'WARNING', $message, $context );
        }
    }

    /**
     * Log an error message.
     *
     * @param string $message The log message.
     * @param array  $context The error context data.
     */
    public function error( $message, array $context = [] ) {
        if ( static::ERROR >= $this->log_level ) {
            $this->log( 'ERROR', $message, $context );
        }
    }

    /**
     * Log a critical message.
     *
     * @param string $message The log message.
     * @param array  $context The error context data.
     */
    public function critical( $message, array $context = [] ) {
        if ( static::CRITICAL >= $this->log_level ) {
            $this->log( 'CRITICAL', $message, $context );
        }
    }

    /**
     * Log an alert message.
     *
     * @param string $message The log message.
     * @param array  $context The error context data.
     */
    public function alert( $message, array $context = [] ) {
        if ( static::ALERT >= $this->log_level ) {
            $this->log( 'ALERT', $message, $context );
        }
    }

    /**
     * Log an emergency message.
     *
     * @param string $message The log message.
     * @param array  $context The error context data.
     */
    public function emergency( $message, array $context = [] ) {
        if ( static::EMERGENCY >= $this->log_level ) {
            $this->log( 'EMERGENCY', $message, $context );
        }
    }

    /**
     * The actual logging method.
     *
     * @param mixed  $level   The log level.
     * @param string $message The log message.
     * @param array  $context The error context data.
     */
    public function log( $level, $message, array $context = [] ) : void {
        $string_context = empty( $context ) ?
            '' :
            ' - Context: ' . addslashes( str_replace( PHP_EOL, '', print_r( $context, true ) ) ); // phpcs:ignore
        $string_context = preg_replace( '/(\s+)/', ' ', $string_context ); // Remove multiple consecutive spaces.
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            \WP_CLI::log( "Queue Logger - $level - $message$string_context" );
        }
        else {
            error_log( "Queue Logger - $level - $message$string_context" ); // phpcs:ignore
        }
    }
}
