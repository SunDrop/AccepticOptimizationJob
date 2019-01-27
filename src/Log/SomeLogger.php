<?php

namespace Acceptic\Log;

/**
 * Class SomeLogger
 * @package Log
 *
 * Logger stub
 */
class SomeLogger implements \Psr\Log\LoggerInterface
{
    public function emergency($message, array $context = [])
    {
        // TODO: Implement emergency() method.
    }

    public function alert($message, array $context = [])
    {
        // TODO: Implement alert() method.
    }

    public function critical($message, array $context = [])
    {
        // TODO: Implement critical() method.
    }

    public function error($message, array $context = [])
    {
        // TODO: Implement error() method.
    }

    public function warning($message, array $context = [])
    {
        // TODO: Implement warning() method.
    }

    public function notice($message, array $context = [])
    {
        // TODO: Implement notice() method.
    }

    public function info($message, array $context = [])
    {
        // TODO: Implement info() method.
    }

    public function debug($message, array $context = [])
    {
        // TODO: Implement debug() method.
    }

    public function log($level, $message, array $context = [])
    {
        // TODO: Implement log() method.
    }

    private function interpolate($message, array $context = [])
    {
        // build a replacement array with braces around the context keys
        $replace = [];
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }
}
