<?php
namespace PekLaiho\Deven;

class Utils
{
    // Create directory if it does not exist
    public static function createDir(string $dir, int $permissions = 0755): void
    {
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755)) {
                self::error("Unable to create directory: $dir");
            }
        }
    }

    // Write error to stderr and exit
    public static function error(string $message, int $status = 1): void
    {
        fwrite(STDERR, $message . PHP_EOL);
        exit($status);
    }

    // Write to stdout
    public static function out(string $message): void
    {
        fwrite(STDOUT, $message);
    }

    // Write to stdout and add linebreak
    public static function outln(string $message): void
    {
        self::out($message . PHP_EOL);
    }

    public static function removeQuotes(string $value): string
    {
        if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
