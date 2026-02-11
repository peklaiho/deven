<?php
namespace PekLaiho\Deven;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class ConfigReader
{
    public function read(string $file): Config
    {
        if (!is_readable($file)) {
            fwrite(STDERR, "File $file is not readable");
            exit(1);
        }

        try {
            $data = Yaml::parse(file_get_contents($file));
        } catch (ParseException $ex) {
            fwrite(STDERR, "Unable to parse $file: " . $ex->getMessage());
            exit(1);
        }

        if (!array_key_exists('name', $data)) {
            fwrite(STDERR, 'Required config setting missing: name');
            exit(1);
        }

        $config = new Config($data['name'], dirname($file));

        // Optional settings

        if (array_key_exists('iso', $data)) {
            $config->setIsoFile($data['iso']);
        }

        if (array_key_exists('cpus', $data)) {
            $config->setCpus($data['cpus']);
        }

        if (array_key_exists('ram', $data)) {
            $config->setRam($data['ram']);
        }

        if (array_key_exists('disk', $data)) {
            $config->setDisk($data['disk']);
        }

        return $config;
    }
}
