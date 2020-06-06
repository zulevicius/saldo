<?php

namespace App\Repository;


use App\Exception\InvalidArgumentException;

class PropertiesProvider extends FileReader
{

    private const PROPERTIES_FILE = __DIR__ . '\..\..\properties.conf';

    /**
     * @var string[]
     */
    private $properties = [];

    public function __construct()
    {
        $this->getProps();
    }

    /**
     * @param string $key
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public function getProp(string $key): string
    {
        if (!isset($this->properties[$key])) {
            throw new InvalidArgumentException("Property `$key` not found in " . self::PROPERTIES_FILE);
        }

        return $this->properties[$key];
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getProps(): void
    {
        foreach ($this->getFileByLines(self::PROPERTIES_FILE) as $line) {
            $keyValue = explode(':', $line, 2);
            if (count($keyValue) !== 2) {
                throw new InvalidArgumentException(self::PROPERTIES_FILE . ' contains property which does not match `key:value` pattern');
            }
            $this->properties[$keyValue[0]] = $keyValue[1];
        }
    }
}