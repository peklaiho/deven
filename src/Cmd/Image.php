<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\Config;
use PekLaiho\Deven\IHypervisor;
use PekLaiho\Deven\SubCommandHandler;
use PekLaiho\Deven\Utils;

class Image implements ICommand
{
    use SubCommandHandler;

    public function execute(IHypervisor $hypervisor, Config $config, array $args): void
    {
        $subCommands = [
            'download' => 'cmdDownload',
            'list' => 'cmdList',
        ];

        $this->handleSubCommand($subCommands, $hypervisor, $config, $args);
    }

    protected function cmdDownload(IHypervisor $hypervisor, Config $config, array $args): void
    {
        // Base name of the image file
        $base = $config->getImage();

        // Filenames
        $targetFile = DEVEN_IMAGE_DIR . DIRECTORY_SEPARATOR . "$base.vdi";
        $archiveFile = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . "$base.tar.xz";
        $rawFile = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . "$base.raw";
        $hashFile = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . 'SHA512SUMS';

        // Check if target file already exists
        if (file_exists($targetFile)) {
            Utils::error("Image $base already exists");
        }

        // Download the archive
        $archiveUrl = "https://cdimage.debian.org/images/cloud/trixie/latest/$base.tar.xz";
        $hashUrl = 'https://cloud.debian.org/images/cloud/trixie/latest/SHA512SUMS';

        Utils::downloadFile($archiveUrl, $archiveFile);
        Utils::downloadFile($hashUrl, $hashFile);

        // Verify the hash
        $hashes = Utils::readHashFile($hashFile);
        Utils::verifyHash($archiveFile, $hashes["$base.tar.xz"]);

        // Extract the raw file from archive
        Utils::extractFileFromArchive($archiveFile, 'disk.raw', $rawFile);

        // Convert the raw file into VDI format
        $hypervisor->convertRawImage($rawFile, $targetFile);

        // Delete the temporary files
        Utils::deleteFile($rawFile);
        Utils::deleteFile($archiveFile);
        Utils::deleteFile($hashFile);

        Utils::outln("Image $base was downloaded successfully!");
    }

    protected function cmdList(IHypervisor $hypervisor, Config $config, array $args): void
    {
        $images = glob(DEVEN_IMAGE_DIR . DIRECTORY_SEPARATOR . '*.vdi');

        if (empty($images)) {
            Utils::outln("No images!");
            return;
        }

        foreach ($images as $name) {
            Utils::outln(substr(basename($name), 0, -4));
        }
    }
}
