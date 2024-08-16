<?php

namespace TorrentPier\Plugins;

class PluginsParser
{
    private array $plguinFile;

    public function __construct($file_path)
    {
        $this->plguinFile = file($file_path, FILE_IGNORE_NEW_LINES);

        // Get meta information
        $metaData = $this->getMeta();

        return [
            'meta' => $metaData
        ];

    }

    /**
     * Getting metadata of plugin
     *
     * @return array
     */
    private function getMeta(): array
    {
        $data = [];

        foreach ($this->plguinFile as $line) {
            if (trim($line) === '~~~~~~~~~~~~~~') {
                // Meta block end
                break;
            }
            if (str_contains($line, ':')) {
                [$key, $value] = explode(':', trim($line), 2);
                $data[trim($key)] = trim($value);
            }
        }

        return $data;
    }
}
