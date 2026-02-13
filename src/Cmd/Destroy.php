<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\Config;
use PekLaiho\Deven\IHypervisor;

class Destroy implements ICommand
{
    public function execute(IHypervisor $hypervisor, Config $config, array $args): void
    {
        if (!$hypervisor->exists($config->getName())) {
            fwrite(STDERR, 'VM does not exist!' . PHP_EOL);
            exit(1);
        }

        $status = $hypervisor->status($config->getName());

        if ($status['VMState'] !== 'poweroff') {
            fwrite(STDERR, 'VM must be shut down first!' . PHP_EOL);
            exit(1);
        } elseif (!in_array('--confirm', $args)) {
            echo 'Please add --confirm as argument if you really want to destroy the VM.' . PHP_EOL;
            exit(1);
        }

        $hypervisor->destroy($config->getName());

        echo 'VM destroyed!' . PHP_EOL;
    }
}
