<?php

namespace App\Service;


use App\Exception\FtpException;
use App\Repository\PropertiesProvider;

class FtpManager
{

    private const PROP_FTP_HOST = 'ftp_host';

    private const PROP_FTP_USER = 'ftp_user';

    private const PROP_FTP_PASSWORD = 'ftp_password';

    /**
     * @var bool|resource
     */
    public $connectionId = false;

    /**
     * @var string
     */
    private $ftpHost;

    /**
     * @var string
     */
    private $ftpUser;

    /**
     * @var string
     */
    private $ftpPassword;

    /**
     * @var false|array
     */
    private $pathFileTree;

    /**
     * @var string
     */
    private $initPath;

    /**
     * @param PropertiesProvider $propertiesProvider
     */
    public function __construct(PropertiesProvider $propertiesProvider)
    {
        $this->ftpHost = $propertiesProvider->getProp(self::PROP_FTP_HOST);
        $this->ftpUser = $propertiesProvider->getProp(self::PROP_FTP_USER);
        $this->ftpPassword = $propertiesProvider->getProp(self::PROP_FTP_PASSWORD);
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * @param $path
     *
     * @throws FtpException
     */
    public function init(string $path)
    {
        $this->disconnect();
        $this->connectionId = ftp_connect($this->ftpHost);
        if (!$this->connectionId) {
            throw new FtpException('Connection not established.', -1);
        }
        ftp_login($this->connectionId, $this->ftpUser, $this->ftpPassword);

        $this->initPath = $path;
        $this->updateFileTreeList();
    }

    public function disconnect(): void
    {
        if (!empty($this->connectionId)) {
            ftp_close($this->connectionId);
            $this->connectionId = false;
        }
    }

    /**
     * @param string $localPath
     * @param string $remotePath
     *
     * @return bool
     */
    public function putFile(string $remotePath, string $localPath): bool
    {
        $ret = ftp_put($this->connectionId, $remotePath, $localPath, FTP_BINARY);
        $this->updateFileTreeList();

        return $ret;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function changeDir(string $path): bool
    {
        return ftp_chdir($this->connectionId, $path);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function makeDir(string $path): bool
    {
        $ret = @ftp_mkdir($this->connectionId, $path);
        $this->updateFileTreeList();

        return $ret;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function pathExists(string $path): bool
    {
        return in_array($path, $this->pathFileTree) || $path === $this->initPath;
    }

    private function updateFileTreeList(): void
    {
        $this->pathFileTree = @ftp_nlist($this->connectionId, $this->initPath);
        if ($this->pathFileTree === false) {
            throw new FtpException("Path `$this->initPath` does not exist");
        }
    }
}