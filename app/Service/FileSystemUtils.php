<?php

namespace App\Service;


use App\Exception\InvalidArgumentException;

class FileSystemUtils
{
    /**
     * @param string $path
     *
     * @return bool
     */
    public function fileExists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * @param string $path
     * @param int $mode
     * @param bool $recursive
     *
     * @return bool
     */
    public function makeDir(string $path, int $mode, bool $recursive): bool
    {
        return mkdir($path, $mode, $recursive);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function isDir(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function changeDir(string $path): bool
    {
        return chdir($path);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function deleteFile(string $path): bool
    {
        return unlink($path);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function removeDir(string $path): bool
    {
        return @rmdir($path);
    }

    /**
     * @param string $path
     *
     * @return array
     */
    public function scanDir(string $path): array
    {
        return scandir($path);
    }

    /**
     * @param string $dir
     *
     * @throws InvalidArgumentException
     *
     * @return array
     */
    public function getFolderFileSystem(string $dir): array
    {
        if (!$this->fileExists($dir)) {
            throw new InvalidArgumentException($dir . ' does not exist');
        } elseif (!$this->isDir($dir)) {
            throw new InvalidArgumentException($dir . ' is not directory');
        }

        $ffs = $this->scanDir($dir);
        unset($ffs[array_search('.', $ffs, true)]);
        unset($ffs[array_search('..', $ffs, true)]);

        return $ffs;
    }
}