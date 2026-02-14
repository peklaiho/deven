<?php
namespace PekLaiho\Deven;

class ShellRunner
{
    public function run(array $command): ShellResult
    {
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

        // Read stdout and stderr
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        // Get the exit status
        $status = proc_close($process);

        $duration = microtime(true) - $startTime;

        return new ShellResult($status, $stdout, $stderr, $duration);
    }
}
