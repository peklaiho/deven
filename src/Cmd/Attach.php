<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\Config;
use PekLaiho\Deven\IHypervisor;
use PekLaiho\Deven\Utils;

class Attach implements ICommand
{
    public function execute(IHypervisor $hypervisor, Config $config, array $args): void
    {
        if (!$hypervisor->exists($config->getName())) {
            Utils::error('VM does not exist!');
        }

        $status = $hypervisor->status($config->getName());

        if ($status['VMState'] !== 'poweroff') {
            Utils::error('VM must be shut down first!');
        } elseif ($status['SATA-1-0'] !== 'none') {
            Utils::error('Something is already attached! Detach it first.');
        }

        if (empty($args)) {
            Utils::error('Give ISO file to attach.');
        }

        $file = $args[0];

        if (!is_readable($file)) {
            Utils::error("File $file not found or not readable");
        } elseif (!str_ends_with($file, '.iso')) {
            Utils::error('Try attaching an .iso file instead');
        }

        $hypervisor->attachDvdDrive($config->getName(), $file);

        Utils::outln('Ok!');
    }
}
