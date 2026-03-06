<?php
namespace PekLaiho\Deven;

trait SubCommandHandler
{
    protected function handleSubCommand(array $subCommands, IHypervisor $hypervisor, Config $config, array $args): void
    {
        if (empty($args)) {
            Utils::outln('Available subcommands:');
            foreach (array_keys($subCommands) as $name) {
                Utils::outln($name);
            }
            return;
        }

        foreach ($subCommands as $name => $sub) {
            if (str_starts_with($name, $args[0])) {
                [$this, $sub]($hypervisor, $config, array_slice($args, 1));
                return;
            }
        }

        Utils::error('Unknown subcommand: ' . $args[0]);
    }
}
