<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\Config;
use PekLaiho\Deven\IHypervisor;
use PekLaiho\Deven\SshRunner;
use PekLaiho\Deven\Utils;

class Stop implements ICommand
{
    public function execute(IHypervisor $hypervisor, Config $config, array $args): void
    {
        if (!$hypervisor->exists($config->getName())) {
            Utils::error('VM does not exist!');
        }

        $status = $hypervisor->status($config->getName());

        if ($status['VMState'] === 'poweroff') {
            Utils::error('VM is already stopped!');
        }

        Utils::outln('Shutting down VM...');

        $sshRunner = new SshRunner($config->getSshPort());
        $sshRunner->run($config->getName(), ['sudo', 'shutdown', 'now']);
        $hypervisor->waitForStatus($config->getName(), 'poweroff');
    }
}
