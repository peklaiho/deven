<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\CloudInitSeedGenerator;
use PekLaiho\Deven\CloudInitStatus;
use PekLaiho\Deven\Config;
use PekLaiho\Deven\IHypervisor;
use PekLaiho\Deven\NetworkConfig;
use PekLaiho\Deven\SharedFolders;
use PekLaiho\Deven\SshRunner;
use PekLaiho\Deven\TermInfoInstaller;
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

        // Used to run SSH commands
        $sshRunner = new SshRunner($config->getSshPort());

        // Create and apply basic settings
        $hypervisor->create($name);
        $hypervisor->setCpusAndMemory($name, $config->getCpus(), $config->getRam() * 1024);
        $hypervisor->setupStorageController($name);

        // Configure NAT port forwarding
        $networkConfig = new NetworkConfig($hypervisor);
        $networkConfig->configure($name, $config->getPorts());

        // Copy the image for hard disk, resize and attach
        $hardDiskFile = DEVEN_VBOX_DIR . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . "$name.vdi";
        Utils::copyFile($image, $hardDiskFile);
        $hypervisor->resizeDisk($hardDiskFile, $config->getDisk() * 1024);
        $hypervisor->attachHardDisk($name, $hardDiskFile);

        // Create cloud-init seed and attach it
        $seedGen = new CloudInitSeedGenerator();
        $seedFile = $seedGen->make($name);
        $hypervisor->attachDvdDrive($name, $seedFile);

        // Create the shared folder in VM
        $sharedFolders = new SharedFolders($hypervisor, $sshRunner);
        $sharedFolders->create($name, $config->getDir());

        // Start her up
        $hypervisor->start($name);
        $hypervisor->waitForStatus($name, 'running');

        // Wait for SSH connection
        $sshRunner->waitForSshConnection($name);

        // Wait for cloud-init to complete
        $cloudInitStatus = new CloudInitStatus($sshRunner);
        $cloudInitStatus->waitForCompletion($name);

        // Install terminfo if needed
        $termInfo = new TermInfoInstaller($sshRunner);
        $termInfo->install($name);

        // Configure the shared folder
        $sharedFolders->configure($name);

        // Shutdown the machine
        $sshRunner->run($name, ['sudo', 'shutdown', 'now']);
        $hypervisor->waitForStatus($name, 'poweroff');

        // Detach the seed ISO and delete it
        $hypervisor->detachDvdDrive($name);
        Utils::deleteFile($seedFile);

        // Start the machine again
        $hypervisor->start($name);
        $hypervisor->waitForStatus($name, 'running');

        // We are done!
        Utils::outln('VM created successfully!');
        Utils::outln("You can now run 'deven init' to initialize the VM.");
    }
}
