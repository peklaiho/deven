<?php
namespace PekLaiho\Deven\Cmd;

use PekLaiho\Deven\Config;
use PekLaiho\Deven\IHypervisor;
use PekLaiho\Deven\SshRunner;
use PekLaiho\Deven\Utils;

class Init implements ICommand
{
    const INIT_FILE = 'deven-init.sh';

    public function execute(IHypervisor $hypervisor, Config $config, array $args): void
    {
        if (!$hypervisor->exists($config->getName())) {
            Utils::error('VM does not exist!');
        }

        $status = $hypervisor->status($config->getName());
        if ($status['VMState'] !== 'running') {
            Utils::error('The VM must be running first!');
        }

        $initFile = $config->getDir() . DIRECTORY_SEPARATOR . self::INIT_FILE;
        if (!is_readable($initFile)) {
            Utils::error("Add an init file named '" . self::INIT_FILE . "' to your project first.");
        }

        $initCompleteFile = '/etc/deven-init-completed';
        $sshRunner = new SshRunner($config->getSshPort());

        // Check if init has been already completed
        if (!in_array('--confirm', $args)) {
            $result = $sshRunner->run($config->getName(), ['sudo', 'test', '-f', $initCompleteFile]);
            if ($result->getStatus() === 0) {
                Utils::error('Init has been already completed. Add --confirm option to run anyway.');
            }
        }

        $outputFile = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . $config->getName() . '-init-output.txt';
        Utils::outln("Running init script, saving output to $outputFile...");

        // Run the init file
        $result = $sshRunner->run($config->getName(), ['sudo', 'sh', '/deven/' . self::INIT_FILE]);

        // Save output to file
        Utils::writeFile($outputFile, $result->getStdOut(), true);

        // Check status
        if ($result->getStatus() !== 0) {
            Utils::error('Error while executing init script: ' . $result->getStdErr());
        }

        // Create the init-completed file
        $result = $sshRunner->run($config->getName(), ['sudo', 'touch', $initCompleteFile]);
        if ($result->getStatus() !== 0) {
            Utils::error("Unable to create file $initCompleteFile: " . $result->getStdErr());
        }

        Utils::outln('Init script completed successfully!');
    }
}
