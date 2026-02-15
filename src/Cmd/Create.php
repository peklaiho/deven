<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\CloudInitSeedGenerator;
use PekLaiho\Deven\Config;
use PekLaiho\Deven\IHypervisor;
use PekLaiho\Deven\Utils;

class Create implements ICommand
{
    public function execute(IHypervisor $hypervisor, Config $config, array $args): void
    {
        $name = $config->getName();

        if ($hypervisor->exists($name)) {
            Utils::error('VM already exists!');
        }

        $image = DEVEN_IMAGE_DIR . DIRECTORY_SEPARATOR . $config->getImage() . '.vdi';
        if (!is_readable($image)) {
            Utils::error("Image $image does not exist");
        }

        // Create and apply basic settings
        $hypervisor->create($name);
        $hypervisor->setCpusAndMemory($name, $config->getCpus(), $config->getRam() * 1024);
        $hypervisor->setupStorageController($name);

        // Configure NAT port forwarding
        $hypervisor->forwardPort($name, 'ssh', 2222, 22);
        $hypervisor->forwardPort($name, 'http', 8080, 80);

        // Copy the image for hard disk, resize and attach
        $hardDiskFile = DEVEN_VBOX_DIR . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . "$name.vdi";
        Utils::copyFile($image, $hardDiskFile);
        $hypervisor->resizeDisk($hardDiskFile, $config->getDisk() * 1024);
        $hypervisor->attachHardDisk($name, $hardDiskFile);

        // Create cloud-init seed and attach it
        $seedGen = new CloudInitSeedGenerator();
        $seedFile = $seedGen->make($name);
        $hypervisor->attachDvdDrive($name, $seedFile);

        Utils::outln('VM created successfully!');
    }
}
