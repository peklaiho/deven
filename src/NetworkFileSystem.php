<?php
namespace PekLaiho\Deven;

class NetworkFileSystem
{
    public function __construct(
        protected IHypervisor $hypervisor,
        protected SshRunner $sshRunner
    ) {

    }

    public function checkExport(string $vmName, string $hostDir): bool
    {
        $file = $this->getExportFile($vmName);

        if (file_exists($file)) {
            Utils::outln("Export file $file for NFS already exists");
            return true;
        }

        Utils::outln("Export file $file for NFS needs to be created");
        Utils::outln("Run the following command to do it:");
        Utils::outln("sudo deven-create-nfs-export $vmName $hostDir");

        return false;
    }

    public function configure(string $vmName, string $hostDir): void
    {
        // Install NFS client
        $this->sshRunner->run($vmName, ['sudo', 'apt-get', 'install', '-y', 'nfs-common'], false, true);

        // Create the mount dir
        $this->sshRunner->run($vmName, ['sudo', 'mkdir', '-p', '/deven']);

        // Install the systemd mount file
        $mountFile = $this->createMountFile($hostDir);
        $this->sshRunner->copyFile($vmName, $mountFile, '~/deven.mount');
        Utils::deleteFile($mountFile);

        $this->sshRunner->run($vmName, ['sudo', 'mv', '~/deven.mount', '/etc/systemd/system/deven.mount']);
        $this->sshRunner->run($vmName, ['sudo', 'chown', 'root:root', '/etc/systemd/system/deven.mount']);

        $this->sshRunner->run($vmName, ['sudo', 'systemctl', 'daemon-reload']);
        $this->sshRunner->run($vmName, ['sudo', 'systemctl', 'enable', '--now', 'deven.mount']);
    }

    protected function getExportFile(string $vmName): string
    {
        return "/etc/exports.d/$vmName.exports";
    }

    protected function createMountFile(string $hostDir): string
    {
        $data = <<<EOF
[Unit]
Description=NFS mount /deven
Wants=network-online.target
After=network-online.target

[Mount]
What=192.168.56.1:$hostDir
Where=/deven
Type=nfs
Options=vers=4,_netdev,rw,noatime

[Install]
WantedBy=multi-user.target

EOF;

        $file = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . 'deven.mount';

        Utils::writeFile($file, $data, true);

        return $file;
    }
}
