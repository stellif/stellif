<?php

declare(strict_types=1);

namespace Stellif\Stellif;

/**
 * The Logger class is responsible for logging information
 * to log files.
 * 
 * @author Asko Nomm <asko@bien.ee>
 */
class Logger
{
    private string $logDir;

    public function __construct()
    {
        $this->logDir = STELLIF_ROOT . '/logs';
    }

    /**
     * Creates a log in a log file with a given `$identifier` and 
     * `$message`. There exists a log file per day, and so if a log
     * file for the current day already exists the message will be 
     * logged in that file, otherwise a new log file will be created
     * and the message will be logged there instead.
     *
     * @param string $identifier
     * @param string $message
     * @return void
     */
    public static function log(string $identifier, string $message): void
    {
        $logFileName = self::$logDir . '/' . date('d-m-Y') . '.json';
        $logItem = [
            'time' => date('H:i:s'),
            $identifier,
            $message,
        ];

        if (file_exists($logFileName)) {
            try {
                $log = json_decode(file_get_contents($logFileName));
                $log[] = $logItem;

                file_put_contents($logFileName, json_encode($log));
            } catch (\Exception $e) {
                error_log($e->getMessage(), 0);
            }
        } else {
            try {
                $log = [$logItem];
                file_put_contents($logFileName, json_encode($log));
            } catch (\Exception $e) {
                error_log($e->getMessage(), 0);
            }
        }
    }
}
