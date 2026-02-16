<?php
namespace PekLaiho\Deven;

class CloudInitSeedGenerator
{
    public function make(string $name): string
    {
        $targetFile = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . "seed-$name.iso";

        // Check if target file already exists
        if (file_exists($targetFile)) {
            Utils::error("File $targetFile already exists");
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
