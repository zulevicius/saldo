<?php

namespace App\Service;


use App\Repository\FileSystemConfProvider;

class CommandRunner
{

    /**
     * @var FileSystemManager
     */
    private $fileSystemManager;

    /**
     * @var FileSystemConfProvider
     */
    private $fileSystemConfProvider;

    /**
     * @param FileSystemManager $fileSystemManager
     * @param FileSystemConfProvider $fileSystemConfProvider
     */
    public function __construct(
        FileSystemManager $fileSystemManager,
        FileSystemConfProvider $fileSystemConfProvider
    ) {
        $this->fileSystemManager = $fileSystemManager;
        $this->fileSystemConfProvider = $fileSystemConfProvider;
    }

    /**
     * @param $args array
     */
    public function execute(array $args): void
    {
        if (!isset($args[1])) {
            $printout = $this->help();
        } elseif ($args[1] === '-l') {
            $printout = $this->list($args);
        } elseif ($args[1] === '-d' && isset($args[2])) {
            $printout = $this->delete($this->fileSystemConfProvider->getRootPath() . $args[2]);
        } elseif ($args[1] === '-c' && isset($args[2])) {
            $printout = $this->create($args);
        } elseif ($args[1] === '-uf' && isset($args[2]) && isset($args[3])) {
            $printout = $this->uploadFtp($args);
        } else {
            $printout = $this->help();
        }
        echo $printout;
    }

    /**
     * @param array $args
     * @return string
     */
    private function uploadFtp(array $args): string
    {
        return $this->fileSystemManager->uploadToFtp(
            $this->fileSystemConfProvider->getRootPath() . $args[2],
            $args[3]
        );
    }

    /**
     * @param array $args
     * @return string
     */
    private function create(array $args): string
    {
        return $this->fileSystemManager->createFolder(
            $args[2],
            $this->fileSystemConfProvider->getRootPath()
        );
    }

    /**
     * @param string $path
     * @return string
     */
    private function delete(string $path): string
    {
        return $this->fileSystemManager->deleteTree($path);
    }

    /**
     * @param array $args
     * @return string
     */
    private function list(array $args): string
    {
        if (isset($args[2])) {
            return $this->fileSystemManager->listFiles(
                $this->fileSystemConfProvider->getRootPath() . $args[2]);
        }
        return $this->fileSystemManager->list($this->fileSystemConfProvider->getRootPath());
    }

    /**
     * @return string
     */
    private function help(): string
    {
        return $this->fileSystemManager->help();
    }
}