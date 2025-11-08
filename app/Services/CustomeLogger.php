<?php

namespace App\Services;

use Illuminate\Log\Logger as BaseLogger;

class CustomeLogger extends BaseLogger
{
    /**
     * Write a message to the log.
     *
     * @param  string  $level
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function write($level, $message, array $context = [])
    {
        $path = $this->getLogFilePath();

        if (!file_exists($path)) {
            $this->createLogFile($path);
        }

        $formatted = $this->formatMessage($level, $message, $context);

        file_put_contents($path, $formatted, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get the path to the log file.
     *
     * @return string
     */
    protected function getLogFilePath()
    {
        return storage_path('app/public/settingslogs/' . date('Y-m-d') . '.log');
    }

    /**
     * Create a new log file.
     *
     * @param  string  $path
     * @return void
     */
    protected function createLogFile($path)
    {
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, '');
    }
}
