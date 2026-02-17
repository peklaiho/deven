<?php
namespace PekLaiho\Deven;

class CloudInitSeedGenerator
{
    // Location of VBoxGuestAdditions.iso
    // On Arch Linux this is installed by package virtualbox-guest-iso
    const GUEST_ADDITIONS_ISO = '/usr/lib/virtualbox/additions/VBoxGuestAdditions.iso';

    public function make(string $name): string
    {
        $targetFile = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . "seed-$name.iso";

        // Check if target file already exists
        if (file_exists($targetFile)) {
            Utils::error("File $targetFile already exists");
        } elseif (!is_readable(self::GUEST_ADDITIONS_ISO)) {
            Utils::error('Guest additions ISO is not readable: ' . self::GUEST_ADDITIONS_ISO);
        }

        // Get or create SSH keys
        $hostKey = (new SshKeyManager())->getHostKey();
        $userKey = (new SshKeyManager())->getUserKey();

        // Create metadata and userdata files
        $configGen = new CloudInitConfigGenerator();

        $metaDataFile = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . 'meta-data';
        $userDataFile = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . 'user-data';

        Utils::writeFile($metaDataFile, $configGen->makeMetaData("deven-$name", $name));
        Utils::writeFile($userDataFile, $configGen->makeUserData($name, $hostKey, $userKey));

        // Create the ISO
        $result = (new ShellRunner())->run([
            'mkisofs',
            '-output', $targetFile,
            '-volid', 'cidata',
            '-joliet',
            '-rock',
            $metaDataFile,
            $userDataFile,
            self::GUEST_ADDITIONS_ISO,
        ]);

        if ($result->getStatus() !== 0) {
            Utils::error("Unable to create ISO file $targetFile: " . $result->getStderr());
        }

        // Delete temporary files
        Utils::deleteFile($metaDataFile);
        Utils::deleteFile($userDataFile);

        return $targetFile;
    }
}
