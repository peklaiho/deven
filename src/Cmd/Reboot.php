<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\Config;
use PekLaiho\Deven\IHypervisor;
use PekLaiho\Deven\SshRunner;
use PekLaiho\Deven\Utils;

class Reboot implements ICommand
{
    public function execute(IHypervisor $hypervisor, Config $config, array $args): void
    {
        if (!$hypervisor->exists($config->getName())) {
            Utils::error('VM does not exist!');
        }

        $status = $hypervisor->status($config->getName());

        if ($status['VMState'] === 'poweroff') {
            Utils::outln('VM not running, starting...');
            $hypervisor->start($config->getName());
            $hypervisor->waitForStatus($config->getName(), 'running');
        } elseif ($status['VMState'] === 'running') {
            Utils::outln('Rebooting VM...');
            $sshRunner = new SshRunner($config->getSshPort());
            $sshRunner->run($config->getName(), ['sudo', 'reboot']);
        } else {
            Utils::error('VM in unknown state: ' . $status['VMState']);
        }
    }
}
