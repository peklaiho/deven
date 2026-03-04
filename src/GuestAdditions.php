<?php
namespace PekLaiho\Deven;

// Install VirtualBox Guest Additions on the VM
class GuestAdditions
{
    // Location of VBoxGuestAdditions.iso
    // On Arch Linux this is installed by package virtualbox-guest-iso
    const GUEST_ADDITIONS_ISO = '/usr/lib/virtualbox/additions/VBoxGuestAdditions.iso';

    public function __construct(
        protected SshRunner $sshRunner
    ) {

    }

    public function install(string $vmName): void
    {
        Utils::outln('Installing VirtualBox Guest Additions');

        if (!is_readable(self::GUEST_ADDITIONS_ISO)) {
            Utils::error('Guest additions ISO is not readable: ' . self::GUEST_ADDITIONS_ISO);
        }

        // Copy the ISO to VM
        $tempFile = '/tmp/VBoxGuestAdditions.iso';
        $this->sshRunner->copyFile($vmName, self::GUEST_ADDITIONS_ISO, $tempFile);

        // Read the kernel version
        $result = $this->sshRunner->run($vmName, ['uname', '-r']);
        $kernel = trim($result->getStdOut());

        // Install the headers
        $this->sshRunner->run($vmName, ['sudo', 'apt-get', 'install', '-y', "linux-headers-$kernel"]);

        // Make mount directory
        $this->sshRunner->run($vmName, ['sudo', 'mkdir', '-p', '/mnt/vbga']);

        // Mount /mnt/vbga
        $this->sshRunner->run($vmName, ['sudo', 'mount', '-o', 'loop', $tempFile, '/mnt/vbga']);

        // Check that the installer is present and executable
        $installer = '/mnt/vbga/VBoxLinuxAdditions.run';
        $this->sshRunner->run($vmName, ['sudo', 'test', '-x', $installer]);

        // Write output to a file
        $outputFile = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . 'vbga-install-output.txt';
        Utils::outln("Running VBGA installer, saving output to $outputFile...");

        // Run the installer
        $result = $this->sshRunner->run($vmName, ['sudo', $installer, '--nox11'], true);
        Utils::writeFile($outputFile, $result->getStdOut(), true);

        // Do not kill the script on errors after this point.
        // The installer often fails due to this non-critical error:
        // Could not set up the VBoxClient desktop service.

        if ($result->getStatus() !== 0) {
            Utils::error('Error while installing VBGA, check the log!', -1);
        } else {
            Utils::outln('VirtualBox Guest Additions installed successfully!');
        }

        // Unmount
        $this->sshRunner->run($vmName, ['sudo', 'umount', '/mnt/vbga'], true);

        // Delete the ISO
        $this->sshRunner->run($vmName, ['sudo', 'rm', $tempFile], true);
    }
}
