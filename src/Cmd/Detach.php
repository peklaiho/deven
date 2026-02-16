<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\Config;
use PekLaiho\Deven\IHypervisor;
use PekLaiho\Deven\Utils;

class Detach implements ICommand
{
    public function execute(IHypervisor $hypervisor, Config $config, array $args): void
    {
        if (!$hypervisor->exists($config->getName())) {
            Utils::error('VM does not exist!');
        }

        $status = $hypervisor->status($config->getName());

        if ($status['VMState'] !== 'poweroff') {
            Utils::error('VM must be shut down first!');
        } elseif ($status['SATA-1-0'] === 'none') {
            Utils::error('Nothing to detach!');
        }

        $hypervisor->detachDvdDrive($config->getName());

        Utils::outln('Ok!');
    }
}
