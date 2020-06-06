<?php

namespace App\Repository;


abstract class FileReader
{

    /**
     * New line delimiter.
     */
    private const NEW_LINE_DELIMITER = "\r\n";

    /**
     * @param string $filename
     *
     * @return string[]
     */
    protected function getFileByLines(string $filename): array
    {
        $content = file_exists($filename) ? file_get_contents($filename) : '';
        if (empty($content)) {
            return [];
        }
        $lines = explode(self::NEW_LINE_DELIMITER, $content);
        return $lines;
    }
}