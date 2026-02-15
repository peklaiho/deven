<?php
namespace PekLaiho\Deven;

class CloudInitConfigGenerator
{
    public function makeMetaData(string $instanceId, string $hostname): string
    {
        return <<<EOF
instance-id: $instanceId
local-hostname: $hostname

EOF;
    }

    public function makeUserData(string $hostname, string $sshPublicKey): string
    {
        return <<<EOF
hostname: $hostname
users:
  - name: deven
    sudo: ALL=(ALL) NOPASSWD:ALL
    groups: sudo
    shell: /bin/bash
    ssh_authorized_keys:
      - $sshPublicKey
package_update: true
package_upgrade: true

EOF;
    }
}
