<?php

namespace App\Service;


use App\Exception\InvalidArgumentException;

class FileSystemManager
{
    private const SYMBOL_TAB = "\t";

    private const SYMBOL_NEW_LINE = "\n";

    /**
     * @var FileSystemUtils
     */
    var $fileSystemUtils;

    /**
     * @var FtpManager
     */
    var $ftpManager;

    /**
     * @param FileSystemUtils $fileSystemUtils
     * @param FtpManager $ftpManager
     */
    public function __construct(FileSystemUtils $fileSystemUtils, FtpManager $ftpManager)
    {
        $this->fileSystemUtils = $fileSystemUtils;
        $this->ftpManager = $ftpManager;
    }

    /**
     * @return string
     */
    public function help(): string
    {
        return <<<EOD
Commands:
    -l - print file system tree
    -l FOLDER - print folder files
    -d PATH - delete file or directory
    -c PATH - create folder in the path
    -uf LOCAL_PATH REMOTE_PATH - upload file or folder to FTP server
    
Config:
    FTP connection parameters and virtual file system root path are set in properties.conf file.
EOD;
    }

    /**
     * @param string $name
     * @param string $path
     *
     * @return string
     */
    public function createFolder(string $name, string $path): string
    {
        if (!$this->fileSystemUtils->fileExists($path)) {
            return "Path `$path` does not exist";
        }

        $fullPath = $path . '\\' . $name;
        if (!$this->fileSystemUtils->fileExists($fullPath)) {
            return $fullPath .
                ($this->fileSystemUtils->makeDir($fullPath, 0777, true) ?
                    ' created' :
                    ' cannot be created');
        }

        return "Folder `$fullPath` already exists";
    }

    /**
     * @param string $path
     * @param int $tryAgain
     *
     * @return string
     */
    public function deleteTree(string $path, int $tryAgain = 0): string
    {
        $result = '';
        $ffs = $this->fileSystemUtils->getFolderFileSystem($path);
        foreach ($ffs as $file) {
            $filePath = "$path\\$file";
            if ($this->fileSystemUtils->isDir($filePath)) {
                $result .= $this->deleteTree($filePath);
            } else {
                $isDeleted = $this->fileSystemUtils->deleteFile($filePath);
                $result .= $filePath . ($isDeleted ? '' : ' cannot be') .
                    ' deleted' . self::SYMBOL_NEW_LINE;
            }
        }

        $isDeleted = @$this->fileSystemUtils->removeDir($path);
        if (!$isDeleted &&
            count($this->fileSystemUtils->getFolderFileSystem($path)) === 0 &&
            $tryAgain < 1
        ) {
            $result .= $this->deleteTree($path, ++$tryAgain);
        }

        return $result . $path .
            ($isDeleted ? '' : ' cannot be') . ' deleted' . self::SYMBOL_NEW_LINE;
    }

    /**
     * @param string $dir
     * @param int $tab
     *
     * @return string
     */
    public function list(string $dir, int $tab = 0): string
    {
        $result = '';

        $ffs = $this->fileSystemUtils->getFolderFileSystem($dir);
        if (count($ffs) < 1) {
            return $result;
        }

        foreach ($ffs as $ff) {
            $isDir = $this->fileSystemUtils->isDir($dir . '\\' . $ff);
            $result .= str_repeat(self::SYMBOL_TAB, $tab) . $ff . ($isDir ? '\\' : '') . self::SYMBOL_NEW_LINE;
            if ($isDir) {
                $result .= $this->list($dir . '\\' . $ff, $tab + 1);
            }
        }

        return $result;
    }

    /**
     * @param string $dir
     *
     * @return string
     */
    public function listFiles(string $dir): string
    {
        $result = '';

        $ffs = $this->fileSystemUtils->getFolderFileSystem($dir);
        if (count($ffs) < 1) {
            return $result;
        }

        foreach ($ffs as $ff) {
            if (!$this->fileSystemUtils->isDir($dir . '\\' . $ff)) {
                $result .= $ff . self::SYMBOL_NEW_LINE;
            }
        }

        return $result;
    }

    /**
     * @param string $localPath
     * @param string $remotePath
     *
     * @return string
     */
    public function uploadToFtp(string $localPath, string $remotePath): string
    {
        $this->ftpManager->init($remotePath);

        if ($this->fileSystemUtils->isDir($localPath)) {
            $result = $this->uploadFolderToFtp($localPath, $remotePath);
        } elseif ($this->fileSystemUtils->fileExists($localPath)) {
            $result = $this->uploadFileToFtp($localPath, $remotePath);
        } else {
            $result = "Nothing exists in `$localPath` path";
        }

        $this->ftpManager->disconnect();

        return $result;
    }

    /**
     * @param string $localPath
     * @param string $remotePath
     *
     * @return string
     */
    private function uploadFolderToFtp(string $localPath, string $remotePath): string
    {
        $result = '';
        $ffs = $this->fileSystemUtils->getFolderFileSystem($localPath);
        foreach ($ffs as $ff) {
            $localFilePath = str_replace("\\", '/', "$localPath\\$ff");
            $remoteFilePath = str_replace("\\", '/', "$remotePath\\$ff");

            if (!$this->ftpManager->pathExists($remotePath)) {
                $result .= "$remotePath cannot go to dir" . self::SYMBOL_NEW_LINE;
            } elseif ($this->fileSystemUtils->isDir($localFilePath)) {
                $created = $this->ftpManager->makeDir($remoteFilePath);
                $result .= $remoteFilePath . ' folder ' . ($created ? '' : 'not ')
                    . 'created' . self::SYMBOL_NEW_LINE;
                $result .= $this->uploadFolderToFtp($localFilePath, $remoteFilePath);
            } else {
                $result .= $this->uploadFileToFtp($localFilePath, $remoteFilePath);
            }
        }

        return $result;
    }

    /**
     * @param string $localPath
     * @param string $remotePath
     *
     * @return string
     */
    private function uploadFileToFtp(string $localPath, string $remotePath): string
    {
        $created = $this->ftpManager->putFile($remotePath, $localPath);
        return $remotePath . ' file ' . ($created ? '' : 'not ')
            . 'created' . self::SYMBOL_NEW_LINE;
    }
}