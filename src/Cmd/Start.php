<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\Config;
use PekLaiho\Deven\IHypervisor;
use PekLaiho\Deven\Utils;

class Start implements ICommand
{
    public function execute(IHypervisor $hypervisor, Config $config, array $args): void
    {
        if (!$hypervisor->exists($config->getName())) {
            Utils::error('VM does not exist!');
        }

        $status = $hypervisor->status($config->getName());

        if ($status['VMState'] !== 'poweroff') {
            Utils::error('VM is already running!');
        }

        $hypervisor->start($config->getName());

        Utils::outln('VM started!');
    }
}
