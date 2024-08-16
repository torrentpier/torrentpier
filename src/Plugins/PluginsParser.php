<?php

namespace TorrentPier\Plugins;

use Exception;

class PluginsParser
{
    private $data = [];

    public function __construct($file_content)
    {
        $this->parseContent($file_content);
    }

    private function parseContent($file_content)
    {
        $lines = explode("\n", trim(file_get_contents($file_content)));

        // Извлечение информации о модуле
        $metaStart = array_search('~~~~~~~~~~~~~~', $lines);
        $metaEnd = array_search('~~~~~~~~~~~~~~', $lines, $metaStart + 1);
        if ($metaStart === false || $metaEnd === false) {
            throw new Exception("Не найден мета-блок.");
        }
        $metaLines = array_slice($lines, $metaStart + 1, $metaEnd - $metaStart - 1);
        foreach ($metaLines as $line) {
            if (str_contains($line, ':')) {
                list($key, $value) = explode(':', trim($line), 2);
                $this->data[trim($key)] = trim($value);
            }
        }

        // Извлечение информации о действиях
        $actionStart = array_search('~~~~~~~', $lines);
        while (($actionStart = array_search('~~~~~~~', $lines, $actionStart + 1)) !== false) {
            $actionEnd = array_search('~~~~~~~', $lines, $actionStart + 1);
            if ($actionEnd === false) {
                throw new Exception("Не закрыт блок действий.");
            }
            $actionLines = array_slice($lines, $actionStart + 1, $actionEnd - $actionStart - 1);

            $openFile = null;
            $actionType = null;
            $findCode = null;
            $pluginCode = null;

            foreach ($actionLines as $line) {
                if (str_starts_with($line, 'open:')) {
                    $openFile = trim(str_replace('open:', '', $line));
                } elseif (str_starts_with($line, 'action:')) {
                    $actionType = trim(str_replace('action:', '', $line));
                } elseif (str_starts_with($line, '---- FIND ----')) {
                    $findCode = trim(implode("\n", array_slice($actionLines, array_search($line, $actionLines) + 1, array_search('---- PLUGIN ----', $actionLines) - array_search($line, $actionLines) - 1)));
                } elseif (str_starts_with($line, '---- PLUGIN ----')) {
                    $pluginCode = trim(implode("\n", array_slice($actionLines, array_search($line, $actionLines) + 1)));
                }
            }

            if ($openFile && $actionType && $findCode && $pluginCode) {
                $this->data['actions'][] = [
                    'open' => $openFile,
                    'action' => $actionType,
                    'find' => $findCode,
                    'code' => $pluginCode,
                ];
            }
        }
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
