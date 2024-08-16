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

        // Get installation steps
        $installSteps = $this->getSteps();

        // TODO REMOVE
        dump($metaData);
        dump($installSteps);

        return [
            'meta' => $metaData
        ];
    }

    private function getSteps(): array
    {
        $data = [];

        foreach ($this->plguinFile as $line) {
            if (trim($line) === '~~~~~~~') {
                // Steps block end
                break;
            }

            if (str_starts_with($line, 'open:')) {
                $targetFile = explode(':', trim($line), 2)[1];
            } elseif (str_starts_with($line, 'action:')) {
            }
        }

        return $data;
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
                if (!in_array($key, ['name', 'author', 'version', 'homepage', 'compatibility'])) {
                    continue;
                }
                $data[trim($key)] = trim($value);
            }
        }

        return $data;
    }
}
