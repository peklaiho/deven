<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\Config;
use PekLaiho\Deven\IHypervisor;

interface ICommand
{
    public function execute(IHypervisor $hypervisor, Config $config, array $args): void;
}
