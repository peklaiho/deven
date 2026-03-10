<?php
namespace PekLaiho\Deven;

class Convenience
{
    public function __construct(
        protected SshRunner $sshRunner
    ) {

    }

    public function install(string $vmName): void
    {
        $configFiles = [
            '.bash_aliases',
            '.tmux.conf',
        ];

        foreach ($configFiles as $file) {
            $source = HOME_DIR . DIRECTORY_SEPARATOR . $file;
            $target = '~/' . $file;

            if (file_exists($source)) {
                Utils::outln("Copying config file $file to VM");
                $this->sshRunner->copyFile($vmName, $source, $target);
            } else {
                Utils::outln("Config file $file not found, unable to copy to VM");
            }
        }
    }
}
