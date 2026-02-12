<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\Config;
use PekLaiho\Deven\IHypervisor;

class ListVms implements ICommand
{
    public function execute(IHypervisor $hypervisor, Config $config, array $args): void
    {
        $list = $hypervisor->listVms();

        if (empty($list)) {
            echo 'No VMs found!' . PHP_EOL;
            exit(0);
        }

        foreach ($list as $vm) {
            echo $vm . PHP_EOL;
        }
    }
}
