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

    // Die if not successful
    public static function deleteFile(string $file): void
    {
        if (!unlink($file)) {
            self::error("Unable to delete file $file");
        }
    }

    public static function downloadFile(string $url, string $file): void
    {
        self::outln("Downloading file $url");

        $runner = new ShellRunner();
        $result = $runner->run([
            'wget', '-q', '-O', $file, $url
        ]);

        if ($result->getStatus() !== 0) {
            self::error("Download of $url to $file failed: " . $result->getStderr());
        }
    }

    // Write error to stderr and exit
    public static function error(string $message, int $status = 1): void
    {
        fwrite(STDERR, $message . PHP_EOL);
        exit($status);
    }

    public static function extractFileFromArchive(string $archive, string $fileInArchive, string $outputFile): void
    {
        self::outln("Extracting $fileInArchive from $archive to $outputFile");

        $cmd = [
            'tar', '--extract',
            '--file', $archive,
            '--to-stdout',
        ];

        if (str_ends_with($archive, '.xz')) {
            $cmd[] = '--xz';
        } elseif (str_ends_with($archive, '.gz')) {
            $cmd[] = '--gzip';
        }

        $cmd[] = $fileInArchive;
        $cmd[] = '>';
        $cmd[] = $outputFile;

        $runner = new ShellRunner();
        $result = $runner->run($cmd);

        if ($result->getStatus() !== 0) {
            self::error("Unable to extract $fileInArchive from $archive: " . $result->getStderr());
        }
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

    public static function readHashFile(string $file): array
    {
        if (!is_readable($file)) {
            self::error("Unable to read hash file $file");
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $result = [];

        foreach ($lines as $line) {
            $parts = preg_split('/\s+/', $line, -1, PREG_SPLIT_NO_EMPTY);

            if (count($parts) === 2) {
                $result[$parts[1]] = $parts[0];
            }
        }

        return $result;
    }

    public static function removeQuotes(string $value): string
    {
        if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }

    public static function verifyHash(string $file, string $correctHash): void
    {
        self::outln("Verifying hash for file $file");

        $runner = new ShellRunner();
        $result = $runner->run(['sha512sum', $file]);

        if ($result->getStatus() !== 0) {
            self::error("Unable to calculate hash for file $file: " . $result->getStderr());
        }

        $parts = preg_split('/\s+/', trim($result->getStdout()), -1, PREG_SPLIT_NO_EMPTY);

        if ($parts[0] !== $correctHash) {
            self::error("Hash for file $file does not match");
        }
    }
}
