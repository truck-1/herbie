<?php

/**
 * This file is part of Herbie.
 *
 * (c) Thomas Breuss <https://www.tebe.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Herbie;

/**
 * @see http://stackoverflow.com/questions/2561235/best-possible-php-error-class
 */
class ErrorHandler
{
    /**
     * Registers this error handler.
     * @param string $logDir
     * @throws \Exception
     */
    public function register(string $logDir)
    {
        if (!is_dir($logDir)) {
            throw new \Exception(sprintf('Log dir "%s" does not exist', $logDir));
        }

        if (!is_writable($logDir)) {
            throw new \Exception(sprintf('Log dir "%s" is not writable', $logDir));
        }

        ini_set("display_errors", 0);
        ini_set("log_errors", 1);
        ini_set("error_log", sprintf("%s/%s-error.log", $logDir, date('Y-m')));

        set_exception_handler([$this, 'handleUncaughtException']);
        set_error_handler([$this, 'handleError'], error_reporting());
        register_shutdown_function([$this, 'handleFatalError']);
    }

    /**
     * Unregisters this error handler.
     */
    public function unregister()
    {
        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * Handles a normal error
     * @param int $code
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     * @throws \ErrorException
     */
    public function handleError(int $code, string $message, string $file, int $line)
    {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }

        // disable error capturing to avoid recursive errors
        restore_error_handler();
        throw new \ErrorException($message, 500, $code, $file, $line);
    }

    /**
     * Handles an exception
     * @param \Exception $exception
     */
    public function handleUncaughtException($exception)
    {
        $this->sendHttpHeader();
        echo '<pre>'.$this->convertExceptionToString($exception).'</pre>';
        exit(1);
    }

    /**
     * Handles a fatal error
     */
    public function handleFatalError()
    {
        $error = error_get_last();
        if ($this->isFatalError($error)) {
            $this->sendHttpHeader();
            $exception = new \ErrorException(
                $error['message'],
                $error['type'],
                $error['type'],
                $error['file'],
                $error['line']
            );
            echo '<pre>'.$this->convertExceptionToString($exception).'</pre>';
            exit(1);
        }
    }

    /**
     * Converts an exception into a simple string.
     * @param \Exception $exception the exception being converted
     * @return string the string representation of the exception.
     */
    public function convertExceptionToString($exception)
    {
        if ($exception instanceof \Throwable && !HERBIE_DEBUG) {
            $message = get_class($exception) . ": {$exception->getMessage()}";
        } elseif (HERBIE_DEBUG) {
            $message = $exception->getMessage() . "\n\n"
                . get_class($exception) . ' [' . $exception->getCode() . '] in '
                . $exception->getFile() . '(' . $exception->getLine() . ")\n\n"
                . "Stack trace:\n" . $exception->getTraceAsString();
        } else {
            $message = 'Error: ' . $exception->getMessage();
        }
        // remove path
        $path = realpath(__DIR__ . '/../../');
        $message = str_replace($path, '', $message);
        return $message;
    }

    /**
     * @param $error
     * @return bool
     */
    public function isFatalError($error)
    {
        $errorTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING];
        return isset($error['type']) && in_array($error['type'], $errorTypes);
    }

    /**
     * @param int $code
     */
    protected function sendHttpHeader($code = 500)
    {
        if (!headers_sent()) {
            header("HTTP/1.1 $code");
        }
    }
}