<?php

namespace TorrentPier\Plugins;

class PluginsParser
{
    private $xml;

    public function __construct($path)
    {
        $this->xml = simplexml_load_file($path);

        if (!$this->xml) {
            // TODO: File not found exception
        }

        $metadata = $this->getMetadata();
        dump($metadata);
    }

    private function getMetadata()
    {
        return [
            'name' => $this->xml->meta->name,
            'author' => $this->xml->meta->author,
            'version' => $this->xml->meta->version,
            'homepage' => $this->xml->meta->homepage,
            'compatibility' => $this->xml->meta->compatibility
        ];
    }
}
