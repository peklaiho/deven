<?php
namespace PekLaiho\Deven;

class ConfigFinder
{
    const FILENAME = 'deven.yml';

    public function find(?string $dir = null): ?string
    {
        if ($dir === null) {
            $dir = getcwd();
        }

        $file = $dir . DIRECTORY_SEPARATOR . self::FILENAME;

        if (file_exists($file)) {
            return $file;
        }

        $parent = dirname($dir);

        if ($parent !== $dir) {
            return $this->find($parent);
        }

        return null;
    }
}
