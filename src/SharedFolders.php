<?php
namespace PekLaiho\Deven;

class SharedFolders
{
    public function __construct(
        protected IHypervisor $hypervisor,
        protected SshRunner $sshRunner
    ) {

    }

    public function create(string $vmName, string $hostDir): void
    {
        Utils::outln("Creating shared directory");

        $this->hypervisor->addSharedFolder($vmName, 'deven', $hostDir);
    }

    public function configure(string $vmName): void
    {
        Utils::outln("Configuring shared directory");

        // Add user to vboxsf group
        $result = $this->sshRunner->run($vmName, ['sudo', 'usermod', '-aG', 'vboxsf', 'deven']);
        if ($result->getStatus() !== 0) {
            Utils::error('Unable to add user to vboxsf group: ' . $result->getStdErr());
        }

        // Create the mount dir
        $result = $this->sshRunner->run($vmName, ['sudo', 'mkdir', '-p', '/deven']);
        if ($result->getStatus() !== 0) {
            Utils::error('Unable to create shared directory: ' . $result->getStdErr());
        }

        // Install the systemd mount file
        $mountFile = $this->createMountFile();
        $this->sshRunner->copyFile($vmName, $mountFile, '~/deven.mount');

        $result = $this->sshRunner->run($vmName, ['sudo', 'mv', '~/deven.mount', '/etc/systemd/system/deven.mount']);
        if ($result->getStatus() !== 0) {
            Utils::error('Unable to copy systemd mount file: ' . $result->getStdErr());
        }

        $result = $this->sshRunner->run($vmName, ['sudo', 'chown', 'root:root', '/etc/systemd/system/deven.mount']);
        if ($result->getStatus() !== 0) {
            Utils::error('Unable to set ownership for systemd mount file: ' . $result->getStdErr());
        }

        $result = $this->sshRunner->run($vmName, ['sudo', 'systemctl', 'enable', 'deven.mount']);
        if ($result->getStatus() !== 0) {
            Utils::error('Unable to enable systemd mount: ' . $result->getStdErr());
        }
    }

    private function createMountFile(): string
    {
        $data = <<<'EOF'
[Unit]
Description=Shared folder /deven
Requires=vboxadd-service.service
After=vboxadd-service.service

[Mount]
What=deven
Where=/deven
Type=vboxsf
Options=uid=1000,gid=1000,dmode=0755,fmode=0644

[Install]
WantedBy=multi-user.target

EOF;

        $file = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . 'deven.mount';

        Utils::writeFile($file, $data, true);

        return $file;
    }
}
