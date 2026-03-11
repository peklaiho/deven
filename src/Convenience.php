<?php
namespace PekLaiho\Deven;

class Convenience
{
    public function __construct(
        protected SshRunner $sshRunner
    ) {

    }

    public function install(string $vmName, array $files): void
    {
        foreach ($files as $source => $target) {
            $source = str_replace('~', HOME_DIR, $source);

            if (file_exists($source)) {
                Utils::outln("Copying file to VM: $source => $target");
                $this->sshRunner->copyFile($vmName, $source, $target);
            } else {
                Utils::outln("File $source not found, unable to copy to VM");
            }
        }
    }
}
