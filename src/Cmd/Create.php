<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\Config;
use PekLaiho\Deven\IHypervisor;

class Create implements ICommand
{
    public function execute(IHypervisor $hypervisor, Config $config, array $args): void
    {
        if ($hypervisor->exists($config->getName())) {
            fwrite(STDERR, 'VM already exists!' . PHP_EOL);
            exit(1);
        }

        $hypervisor->create($config);

        echo 'VM created successfully!' . PHP_EOL;
    }
}
