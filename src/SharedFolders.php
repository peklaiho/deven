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
        $this->sshRunner->run($vmName, ['sudo', 'usermod', '-aG', 'vboxsf', 'deven']);

        // Create the mount dir
        $this->sshRunner->run($vmName, ['sudo', 'mkdir', '-p', '/deven']);

        // Install the systemd mount file
        $mountFile = $this->createMountFile();
        $this->sshRunner->copyFile($vmName, $mountFile, '~/deven.mount');
        Utils::deleteFile($mountFile);

        $this->sshRunner->run($vmName, ['sudo', 'mv', '~/deven.mount', '/etc/systemd/system/deven.mount']);
        $this->sshRunner->run($vmName, ['sudo', 'chown', 'root:root', '/etc/systemd/system/deven.mount']);

        // Install the systemd service file to load vboxsf module
        $vboxsfServiceFile = $this->createVboxsfServiceFile();
        $this->sshRunner->copyFile($vmName, $vboxsfServiceFile, '~/deven-vboxsf.service');
        Utils::deleteFile($vboxsfServiceFile);

        $this->sshRunner->run($vmName, ['sudo', 'mv', '~/deven-vboxsf.service', '/etc/systemd/system/deven-vboxsf.service']);
        $this->sshRunner->run($vmName, ['sudo', 'chown', 'root:root', '/etc/systemd/system/deven-vboxsf.service']);

        // Enable the new units
        $this->sshRunner->run($vmName, ['sudo', 'systemctl', 'daemon-reload']);
        $this->sshRunner->run($vmName, ['sudo', 'systemctl', 'enable', 'deven-vboxsf.service']);
        $this->sshRunner->run($vmName, ['sudo', 'systemctl', 'enable', 'deven.mount']);
    }

    private function createMountFile(): string
    {
        $data = <<<'EOF'
[Unit]
Description=Shared folder /deven
Requires=deven-vboxsf.service
After=deven-vboxsf.service

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

    private function createVboxsfServiceFile(): string
    {
        $data = <<<'EOF'
[Unit]
Description=Load VirtualBox shared folders kernel module
Requires=vboxadd-service.service
After=vboxadd-service.service

[Service]
Type=oneshot
ExecStart=/sbin/modprobe vboxsf
RemainAfterExit=yes

[Install]
WantedBy=multi-user.target

EOF;

        $file = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . 'deven-vboxsf.service';

        Utils::writeFile($file, $data, true);

        return $file;
    }
}
