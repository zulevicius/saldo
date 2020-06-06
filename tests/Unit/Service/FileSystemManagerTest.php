<?php

use App\Exception\InvalidArgumentException;
use App\Service\FtpManager;
use App\Service\FileSystemManager;
use App\Service\FileSystemUtils;
use PHPUnit\Framework\TestCase;

final class FileSystemManagerTest extends TestCase
{
    public function testCreateFolder_FailNoRoot(): void
    {
        $fsuStub = $this
            ->getMockBuilder(FileSystemUtils::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fsuStub->method('fileExists')->willReturn(false);
        $ftpStub = $this
            ->getMockBuilder(FtpManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fsm = new FileSystemManager($fsuStub, $ftpStub);

        $path = 'root';
        $this->assertSame(
            "Path `$path` does not exist",
            $fsm->createFolder('folder', $path)
        );
    }

    public function testCreateFolder_FailAlreadyExists(): void
    {
        $fsuStub = $this
            ->getMockBuilder(FileSystemUtils::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fsuStub->method('fileExists')->will(
            $this->onConsecutiveCalls(true, true)
        );
        $ftpStub = $this
            ->getMockBuilder(FtpManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fsm = new FileSystemManager($fsuStub, $ftpStub);

        $path = 'root';
        $name = 'folder';
        $this->assertSame(
            "Folder `$path\\$name` already exists",
            $fsm->createFolder($name, $path)
        );
    }

    public function testCreateFolder_FailCannotCreate(): void
    {
        $fsuStub = $this
            ->getMockBuilder(FileSystemUtils::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fsuStub->method('fileExists')->will(
            $this->onConsecutiveCalls(true, false)
        );
        $fsuStub->method('makeDir')->willReturn(false);
        $ftpStub = $this
            ->getMockBuilder(FtpManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fsm = new FileSystemManager($fsuStub, $ftpStub);

        $path = 'root';
        $name = 'folder';
        $this->assertSame(
            "$path\\$name cannot be created",
            $fsm->createFolder($name, $path)
        );
    }

    public function testCreateFolder_Success(): void
    {
        $fsuStub = $this
            ->getMockBuilder(FileSystemUtils::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fsuStub->method('fileExists')->will(
            $this->onConsecutiveCalls(true, false)
        );
        $fsuStub->method('makeDir')->willReturn(true);
        $ftpStub = $this
            ->getMockBuilder(FtpManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fsm = new FileSystemManager($fsuStub, $ftpStub);

        $path = 'root';
        $name = 'folder';
        $this->assertSame(
            "$path\\$name created",
            $fsm->createFolder($name, $path)
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDeleteTree(): void
    {
        $fsuStub = $this
            ->getMockBuilder(FileSystemUtils::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fsuStub->method('getFolderFileSystem')->will(
            $this->onConsecutiveCalls(['file1', 'file2'], [])
        );
        $fsuStub->method('isDir')->will(
            $this->onConsecutiveCalls(false, false)
        );
        $fsuStub->method('deleteFile')->will(
            $this->onConsecutiveCalls(true, true)
        );
        $fsuStub->method('removeDir')->willReturn(true);
        $ftpStub = $this
            ->getMockBuilder(FtpManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $expectedResult = "folder\\file1 deleted\n";
        $expectedResult .= "folder\\file2 deleted\n";
        $expectedResult .= "folder deleted\n";

        $fsm = new FileSystemManager($fsuStub, $ftpStub);
        $this->assertSame(
            $expectedResult,
            $fsm->deleteTree('folder')
        );
    }
    
    // todo: test the rest of the methods
}
