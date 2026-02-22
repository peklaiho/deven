<?php
namespace PekLaiho\Deven;

// Install terminfo on the guest if needed.
// This is sort of secondary functionality so
// do not die on errors here, just log failures.
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
            Utils::error('Unable to read terminal type', -1);
            return;
        }

        // Check if it already known on guest
        $result = $this->sshRunner->run($vmName, [
            'infocmp', $term,
        ]);

        if ($result->getStatus() === 0) {
            Utils::outln("Terminal type $term is already supported on VM");
            return;
        }

        // Get the terminfo from host
        $result = (new ShellRunner())->run([
            'infocmp', $term,
        ]);

        if ($result->getStatus() !== 0) {
            Utils::error('Unable to read terminfo using infocmp: ' . $result->getStdErr(), -1);
            return;
        }

        // Save it to a temporary file
        $tempFile = DEVEN_TMP_DIR . DIRECTORY_SEPARATOR . "$term.ti";
        Utils::writeFile($tempFile, $result->getStdOut(), true);

        // Copy it over
        if (!$this->sshRunner->copyFile($vmName, $tempFile, "~/$term.ti")) {
            return;
        }

        // Apply it to the guest
        $result = $this->sshRunner->run($vmName, [
            'sudo', 'tic', "~/$term.ti",
        ]);

        if ($result->getStatus() !== 0) {
            Utils::error('Unable to install terminfo on VM: ' . $result->getStdErr(), -1);
            return;
        }

        // Finally delete the file
        $result = $this->sshRunner->run($vmName, [
            'rm', "~/$term.ti"
        ]);

        if ($result->getStatus() !== 0) {
            Utils::error('Unable to clean up terminfo file on VM: ' . $result->getStdErr(), -1);
            return;
        }

        Utils::outln("Terminfo for $term installed successfully on VM");
    }
}
