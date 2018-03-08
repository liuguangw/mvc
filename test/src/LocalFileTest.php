<?php
use PHPUnit\Framework\TestCase;
use liuguang\mvc\file\LocalFileAdapter;

class LocalFileTest extends TestCase
{

    public function testCommon()
    {
        $file = LocalFileAdapter::createPublicInstance('upload');
        $savePath = 'path/to/text.txt';
        // 保存文件
        $file->saveFile(__FILE__, $savePath);
        $distSavePath = PUBLIC_PATH . '/./upload/' . $savePath;
        $this->assertFileExists($distSavePath);
        $this->assertFileEquals(__FILE__, $distSavePath);
        // 写入、读取文件
        $content = 'hello world';
        $file->writeFile($savePath, $content);
        $this->assertEquals($content, $file->readFile($savePath));
        // url获取
        $this->assertEquals('/upload/' . $savePath, $file->getFileUrl($savePath));
        // 删除文件
        $file->deleteFile($savePath);
        $this->assertFileNotExists($distSavePath);
    }
}

