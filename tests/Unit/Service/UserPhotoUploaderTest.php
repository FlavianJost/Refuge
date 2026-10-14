<?php

namespace App\Tests\Unit\Service;

use App\Service\UserPhotoUploader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserPhotoUploaderTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir().'/refuge_user_uploads_'.uniqid('', true);
        mkdir($this->directory, 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->directory);
    }

    public function testUploadMovesTheFileAndReturnsAPrefixedName(): void
    {
        $uploader = new UserPhotoUploader($this->directory);

        $filename = $uploader->upload($this->createUploadedFile());

        self::assertStringStartsWith('user_', $filename);
        self::assertFileExists($this->directory.'/'.$filename);
    }

    public function testRemoveDeletesTheFile(): void
    {
        $uploader = new UserPhotoUploader($this->directory);
        $filename = $uploader->upload($this->createUploadedFile());

        $uploader->remove($filename);

        self::assertFileDoesNotExist($this->directory.'/'.$filename);
    }

    public function testRemoveWithNullDoesNothing(): void
    {
        $uploader = new UserPhotoUploader($this->directory);

        $uploader->remove(null);

        self::assertDirectoryExists($this->directory);
    }

    private function createUploadedFile(): UploadedFile
    {
        $path = $this->directory.'/source_'.uniqid('', true).'.png';
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='));

        return new UploadedFile($path, 'photo.png', 'image/png', null, true);
    }
}
