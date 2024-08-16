<?php

namespace TorrentPier\Plugins;

use Exception;

class PluginsParser
{
    private $data = [];

    public function __construct($file_content)
    {
        $this->parseFile($file_content);
    }

    private function parseFile($file_path)
    {
        $fp = fopen($file_path, 'r');
        if (!$fp) {
            throw new Exception("Не удалось открыть файл: $file_path");
        }

        $state = 'initial'; // Состояние парсинга: initial, meta, action
        $currentAction = []; // Текущий блок действия
        $meta = []; // Данные мета-блока

        while (($line = fgets($fp)) !== false) {
            $line = trim($line);
            switch ($state) {
                case 'initial':
                    if ($line === '~~~~~~~~~~~~~~') {
                        $state = 'meta';
                    }
                    break;
                case 'meta':
                    if ($line === '~~~~~~~~~~~~~~') {
                        $state = 'action';
                        $this->data['meta'] = $meta;
                        $meta = [];
                    } else {
                        if (str_contains($line, ':')) {
                            list($key, $value) = explode(':', $line, 2);
                            $meta[trim($key)] = trim($value);
                        }
                    }
                    break;
                case 'action':
                    if ($line === '~~~~~~~') {
                        $this->data['actions'][] = $currentAction;
                        $currentAction = [];
                    } else {
                        if (str_starts_with($line, 'open:')) {
                            $currentAction['open'] = trim(str_replace('open:', '', $line));
                        } elseif (str_starts_with($line, 'action:')) {
                            $currentAction['action'] = trim(str_replace('action:', '', $line));
                        } elseif (str_starts_with($line, '---- FIND ----')) {
                            $currentAction['find'] = trim(implode("\n", array_slice(
                                explode("\n", $line),
                                array_search('---- FIND ----', explode("\n", $line)) + 1,
                                array_search('---- PLUGIN ----', explode("\n", $line)) - array_search('---- FIND ----', explode("\n", $line)) - 1
                            )));
                        } elseif (str_starts_with($line, '---- PLUGIN ----')) {
                            $currentAction['code'] = trim(implode("\n", array_slice(explode("\n", $line), array_search('---- PLUGIN ----', explode("\n", $line)) + 1)));
                        }
                    }
                    break;
            }
        }

        fclose($fp);
    }

    public function install()
    {
        foreach ($this->data['actions'] as $action) {
            $this->applyAction($action);
        }
    }

    private function applyAction($action)
    {
        echo "Применяю действие для файла: {$action['open']}n";
        echo "  Тип действия: {$action['action']}n";

        $fileContent = file_get_contents($action['open']);

        switch ($action['action']) {
            case 'after_add':
                $fileContent = str_replace(
                    $action['find'],
                    $action['find'] . "\n" . $action['code'],
                    $fileContent
                );
                break;
            case 'before_add':
                $fileContent = str_replace(
                    $action['find'],
                    $action['code'] . "\n" . $action['find'],
                    $fileContent
                );
                break;
            default:
                echo "Неизвестный тип действия: {$action['action']}n";
                break;
        }

        file_put_contents($action['open'], $fileContent);
    }
}
