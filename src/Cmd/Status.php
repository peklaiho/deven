<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\Config;
use PekLaiho\Deven\IHypervisor;

class Status implements ICommand
{
    public function execute(IHypervisor $hypervisor, Config $config, array $args): void
    {
        $status = $hypervisor->status($config->getName());

        if (!$status) {
            echo 'No VM found.' . PHP_EOL;
            exit(1);
        }

        // Special formats
        if (in_array('--json', $args)) {
            echo json_encode($status, JSON_PRETTY_PRINT);
            exit(0);
        }

        // Default: Just show if it is running or not
        if ($status['VMState'] === 'poweroff') {
            echo 'VM is powered off.' . PHP_EOL;
        } elseif ($status['VMState'] === 'running') {
            echo 'VM is running.' . PHP_EOL;
        } else {
            echo 'VM state: ' . $status['VMState'] . PHP_EOL;
        }
    }
}
