<?php
namespace PekLaiho\Deven;

// Install terminfo on the guest if needed
class TermInfoInstaller
{
    public function __construct(
        protected SshRunner $sshRunner
    ) {

    }

    public function install(string $vmName): void
    {
        $term = getenv('TERM');

        if (!$term) {
            Utils::error('Unable to read terminal type');
        }

        // Check if it already known on guest
        $result = $this->sshRunner->run($vmName, [
            'infocmp', $term,
        ], true);

        if ($result->getStatus() === 0) {
            Utils::outln("Terminal type $term is already supported on VM");
            return;
        }

        // Get the terminfo from host
        $result = (new ShellRunner())->run([
            'infocmp', $term,
        ]);

        if ($result->getStatus() !== 0) {
            Utils::error('Unable to read terminfo using infocmp: ' . $result->getStdErr());
        }

        // Save it to a temporary file
        $tempFile = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . "$term.ti";
        Utils::writeFile($tempFile, $result->getStdOut(), true);

        // Copy it over
        $this->sshRunner->copyFile($vmName, $tempFile, "~/$term.ti");

        // Apply it to the guest
        $this->sshRunner->run($vmName, [
            'sudo', 'tic', "~/$term.ti",
        ]);

        // Finally delete both files
        Utils::deleteFile($tempFile);

        $this->sshRunner->run($vmName, [
            'rm', "~/$term.ti"
        ]);

        Utils::outln("Terminfo for $term installed successfully on VM");
    }
}
