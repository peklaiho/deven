<?php
namespace PekLaiho\Deven;

use Symfony\Component\Yaml\Yaml;

class CloudInitConfigGenerator
{
    public function makeMetaData(string $instanceId, string $hostname): string
    {
        $data = [
            'instance-id' => $instanceId,
            'local-hostname' => $hostname,
        ];

        return Yaml::dump($data);
    }

    public function makeUserData(string $hostname, SshKey $hostKey, SshKey $userKey): string
    {
        $hostPublic = $hostKey->getPublicKey();
        $hostPrivate = $hostKey->getPrivateKey();
        $userPublic = $userKey->getPublicKey();

        $data = [
            'hostname' => $hostname,
            'ssh_keys' => [
                'ed25519_private' => $hostPrivate,
                'ed25519_public' => $hostPublic,
            ],
            'users' => [
                [
                    'name' => 'deven',
                    'sudo' => 'ALL=(ALL) NOPASSWD:ALL',
                    'groups' => 'sudo',
                    'shell' => '/bin/bash',
                    'plain_text_passwd' => 'deven',
                    'lock_passwd' => false,
                    'ssh_authorized_keys' => [ $userPublic ],
                ]
            ],
            'chpasswd' => [
                'expire' => false,
            ],
            'package_update' => true,
            'package_upgrade' => true,
            'packages' => [
                // Packages needed for VirtualBox Guest Additions
                'build-essential',
                'dkms',
                'linux-headers-amd64',
            ],
            'runcmd' => [
                // Install VirtualBox Guest Additions
                //
                // We ignore non-zero exit from VBGA installer because
                // it fails with this non-important error:
                //
                // Could not set up the VBoxClient desktop service.
                [ 'mkdir', '-p', '/mnt/seed', '/mnt/vbga' ],
                [ 'mount', '/dev/cdrom', '/mnt/seed' ],
                [ 'mount', '-o', 'loop', '/mnt/seed/VBoxGuestAdditions.iso', '/mnt/vbga' ],
                [ 'test', '-x', '/mnt/vbga/VBoxLinuxAdditions.run' ],
                [ 'sh', '-c', "/mnt/vbga/VBoxLinuxAdditions.run --nox11 || echo 'Ignore non-zero exit from VBGA'" ],
            ],
        ];

        $header = "#cloud-config\n";

        return $header . Yaml::dump($data);
    }
}
