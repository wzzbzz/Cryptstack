<?php

namespace App\Service\NativeX;

class ConfigLoader
{
    private string $configFile;
    private string $piFile;

    public function __construct(string $configFile, string $piFile)
    {
        $this->configFile = $configFile;
        $this->piFile = $piFile;
    }

    public function load(): array
    {
        $config = [];

        $fh = fopen($this->configFile, "r");
        while (($line = fgets($fh)) !== false) {
            $line = trim($line);
            if ($line === "" || $line[0] === "#") continue;

            list($key, $value) = explode("=", $line, 2);
            $config[trim($key)] = trim($value);
        }
        $config['pi'] = trim(file_get_contents($this->piFile));
        fclose($fh);

        return $config;
    }
}
