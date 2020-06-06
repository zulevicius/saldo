<?php

namespace App\Repository;


class FileSystemConfProvider
{

    private const PROPERTY_ROOT_PATH = 'file_system_root_dir';

    /**
     * @var string
     */
    private $rootPath;


    public function __construct(PropertiesProvider $propertiesProvider)
    {
        $this->rootPath = $propertiesProvider->getProp(self::PROPERTY_ROOT_PATH);
    }

    /**
     * @return string
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }
}