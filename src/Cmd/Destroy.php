<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\Config;
use PekLaiho\Deven\IHypervisor;
use PekLaiho\Deven\Utils;

class Destroy implements ICommand
{
    public function execute(IHypervisor $hypervisor, Config $config, array $args): void
    {
        if (!$hypervisor->exists($config->getName())) {
            Utils::error('VM does not exist!');
        }

        $status = $hypervisor->status($config->getName());

        if ($status['VMState'] !== 'poweroff') {
            Utils::error('VM must be shut down first!');
        } elseif (!in_array('--confirm', $args)) {
            Utils::error('Required argument --confirm missing');
        }

        $hypervisor->destroy($config->getName());

        Utils::outln('VM destroyed!');
    }
}
