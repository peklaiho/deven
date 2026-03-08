<?php
namespace PekLaiho\Deven;

class ShellRunner
{
    // If $streaming is true, output is echoed out as it comes
    public function run(array $command, bool $streaming = false): ShellResult
    {
        Utils::debugLog('Run: ' . implode(' ', $command));

        $startTime = microtime(true);

        $descriptorspec = [
            0 => ["pipe", "r"],  // stdin
            1 => ["pipe", "w"],  // stdout
            2 => ["pipe", "w"],  // stderr
        ];

        $process = proc_open($command, $descriptorspec, $pipes);

        if (!is_resource($process)) {
            Utils::error('Unable to run shell command');
        }

        // Close stdin (we are not providing any input)
        fclose($pipes[0]);

        // Make stdout and stderr non-blocking
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        // Buffers for output
        $stdout = '';
        $stderr = '';

        // Read output in a loop
        while (true) {
            $read = [];
            if (!feof($pipes[1])) {
                $read[] = $pipes[1];
            }
            if (!feof($pipes[2])) {
                $read[] = $pipes[2];
            }

            // Both pipes reached EOF: child process has no more output to read.
            if (!$read) {
                break;
            }

            $write = null;
            $except = null;

            // Wait until at least one stream is readable (or timeout after 1s)
            $changed = @stream_select($read, $write, $except, 1);
            if ($changed === false) {
                break;
            }

            if ($changed === 0) {
                continue;
            }

            foreach ($read as $stream) {
                $chunk = fread($stream, 8192);
                if ($chunk === false || $chunk === '') {
                    continue;
                }

                if ($stream === $pipes[1]) {
                    $stdout .= $chunk;
                    if ($streaming) {
                        fwrite(STDOUT, $chunk);
                        fflush(STDOUT);
                    }
                } else {
                    $stderr .= $chunk;
                    if ($streaming) {
                        fwrite(STDERR, $chunk);
                        fflush(STDERR);
                    }
                }
            }
        }

        // Close stdout/stderr
        fclose($pipes[1]);
        fclose($pipes[2]);

        // Get the exit status
        $status = proc_close($process);

        $duration = microtime(true) - $startTime;

        return new ShellResult($status, $stdout, $stderr, $duration);
    }
}
