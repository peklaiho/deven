<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\Config;
use PekLaiho\Deven\IHypervisor;
use PekLaiho\Deven\Utils;

class Image implements ICommand
{
    public const IMAGE_DIR = DEVEN_DIR . DIRECTORY_SEPARATOR . 'images';

    public function execute(IHypervisor $hypervisor, Config $config, array $args): void
    {
        $subcommands = [
            'download' => 'cmdDownload',
            'list' => 'cmdList',
        ];

        if (empty($args)) {
            Utils::outln('Available subcommands:');
            foreach (array_keys($subcommands) as $name) {
                Utils::outln($name);
            }
            return;
        }

        foreach ($subcommands as $name => $sub) {
            if (str_starts_with($name, $args[0])) {
                [$this, $sub]($hypervisor, $config, array_slice($args, 1));
                return;
            }
        }

        Utils::error('Unknown subcommand: ' . $args[0]);
    }

    public function cmdDownload(IHypervisor $hypervisor, Config $config, array $args): void
    {
        $infofile = 'https://cdimage.debian.org/images/cloud/trixie/latest/debian-13-generic-amd64.json';
        $imagefile = 'https://cdimage.debian.org/images/cloud/trixie/latest/debian-13-generic-arm64.tar.xz';

        echo 'download';
    }

    public function cmdList(IHypervisor $hypervisor, Config $config, array $args): void
    {
        echo 'list';
    }
}
