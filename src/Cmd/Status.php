<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\Config;
use PekLaiho\Deven\IHypervisor;
use PekLaiho\Deven\Utils;

class Status implements ICommand
{
    public function execute(IHypervisor $hypervisor, Config $config, array $args): void
    {
        if (!$hypervisor->exists($config->getName())) {
            Utils::error('No VM found.');
        }

        $status = $hypervisor->status($config->getName());

        // Special formats
        if (in_array('--json', $args)) {
            Utils::outln(json_encode($status, JSON_PRETTY_PRINT));
            exit(0);
        }

        // Default: Just show if it is running or not
        if ($status['VMState'] === 'poweroff') {
            Utils::outln('VM is powered off.');
        } elseif ($status['VMState'] === 'running') {
            Utils::outln('VM is running.');
        } else {
            Utils::outln('VM state: ' . $status['VMState']);
        }
    }
}
