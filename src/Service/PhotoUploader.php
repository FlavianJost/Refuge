<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class PhotoUploader
{
    public function __construct(
        private readonly string $targetDirectory,
        private readonly string $prefix,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        $filename = uniqid($this->prefix, true).'.'.($file->guessExtension() ?? 'bin');
        $file->move($this->targetDirectory, $filename);

        return $filename;
    }

    public function remove(?string $filename): void
    {
        if (null === $filename) {
            return;
        }

        $path = $this->targetDirectory.\DIRECTORY_SEPARATOR.$filename;

        if (is_file($path)) {
            unlink($path);
        }
    }
}
