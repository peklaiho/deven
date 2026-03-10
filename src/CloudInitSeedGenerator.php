<?php
namespace PekLaiho\Deven;

class CloudInitSeedGenerator
{
    public function make(string $name): string
    {
        $targetFile = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . "seed-$name.iso";

        // Get or create SSH keys
        $hostKey = (new SshKeyManager())->getHostKey();
        $userKey = (new SshKeyManager())->getUserKey();

        // Create metadata and userdata files
        $configGen = new CloudInitConfigGenerator();

        $metaDataFile = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . 'meta-data';
        $userDataFile = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . 'user-data';

        Utils::writeFile($metaDataFile, $configGen->makeMetaData("deven-$name", $name), true);
        Utils::writeFile($userDataFile, $configGen->makeUserData($name, $hostKey, $userKey), true);

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
            Utils::error("Unable to create ISO file $targetFile: " . $result->getStdErr());
        }

        // Delete temporary files
        Utils::deleteFile($metaDataFile);
        Utils::deleteFile($userDataFile);

        return $targetFile;
    }
}
