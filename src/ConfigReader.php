<?php
namespace PekLaiho\Deven;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class ConfigReader
{
    public function read(string $file): Config
    {
        if (!is_readable($file)) {
            Utils::error("File $file is not readable");
        }

        try {
            $data = Yaml::parse(file_get_contents($file));
        } catch (ParseException $ex) {
            Utils::error("Unable to parse $file: " . $ex->getMessage());
        }

        if (!array_key_exists('name', $data)) {
            Utils::error('Required config setting missing: name');
        }

        $config = new Config($data['name'], dirname($file));

        // Optional settings

        if (array_key_exists('image', $data)) {
            $config->setImage($data['image']);
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

        if (array_key_exists('ports', $data)) {
            $config->setPorts($data['ports']);
        }

        return $config;
    }
}
