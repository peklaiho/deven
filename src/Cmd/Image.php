<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\Config;
use PekLaiho\Deven\IHypervisor;
use PekLaiho\Deven\Utils;

class Image implements ICommand
{
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
        $targetFile = DEVEN_IMAGE_DIR . DIRECTORY_SEPARATOR . 'debian-13-generic-amd64.vdi';

        if (file_exists($targetFile)) {
            Utils::error("File $targetFile already exists, no need to download");
        }

        // First download the raw image if we don't have it yet

        $imageUrl = 'https://cdimage.debian.org/images/cloud/trixie/latest/debian-13-generic-amd64.tar.xz';
        $hashUrl = 'https://cloud.debian.org/images/cloud/trixie/latest/SHA512SUMS';

        $imageFile = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . 'debian-13-generic-amd64.tar.xz';
        $hashFile = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . 'SHA512SUMS';

        if (!file_exists($imageFile)) {
            Utils::downloadFile($imageUrl, $imageFile);
        }
        if (!file_exists($hashFile)) {
            Utils::downloadFile($hashUrl, $hashFile);
        }

        // Then verify the hash

        $hashes = Utils::readHashFile($hashFile);
        Utils::verifyHash($imageFile, $hashes['debian-13-generic-amd64.tar.xz']);

        // Extract the archive if needed

        $rawFile = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . 'debian-13-generic-amd64.raw';

        if (!file_exists($rawFile)) {
            Utils::extractFileFromArchive($imageFile, 'disk.raw', $rawFile);
        }

    }

    public function cmdList(IHypervisor $hypervisor, Config $config, array $args): void
    {
        // TODO
    }
}
