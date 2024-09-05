<?php
declare(strict_types=1);
namespace ApiVacations\Helpers;

class Logger
{
    public static string $logfile = '/Log/log.txt';

    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        $result = date("Y-m-d H:i:s") . ' - Log level: ' . $level . "\n";
        if ($message) {
            $result .= date("Y-m-d H:i:s") . ' - Message: ' . $message . "\n";
        }
        if (!empty($context)) {
            $result .= self::asString($context);
        }
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . self::$logfile, $result, FILE_APPEND);
    }

    public static function asString(array $context = []): string
    {
        $result = '';
        foreach ($context as $key => $value) {
            $result .= date("Y-m-d H:i:s") . ' - ' . $key . ' : ' . $value . "\n";
        }
        return $result;
    }
}