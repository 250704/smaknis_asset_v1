<?php

namespace App\Http\Controllers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class Controller
{
    protected function storeMediaFile(
        UploadedFile $file,
        string $directory,
        string $disk = 'public',
        int $maxWidth = 1600,
        int $quality = 82
    ): string {
        if ($this->isImageFile($file)) {
            $compressed = $this->storeCompressedImageFile($file, $directory, $disk, $maxWidth, $quality);
            if ($compressed !== null) {
                return $compressed;
            }
        }

        return $file->store($directory, $disk);
    }

    protected function storeCompressedImageFile(
        UploadedFile $file,
        string $directory,
        string $disk = 'public',
        int $maxWidth = 1600,
        int $quality = 82
    ): ?string {
        $imageInfo = @getimagesize($file->getRealPath());
        if ($imageInfo === false) {
            return null;
        }

        $source = $this->createImageResource($file->getRealPath(), (string) ($imageInfo['mime'] ?? ''));
        if (!$source) {
            return null;
        }

        $width = imagesx($source);
        $height = imagesy($source);

        if ($width > $maxWidth) {
            $newHeight = (int) round(($height * $maxWidth) / $width);
            $target = imagecreatetruecolor($maxWidth, max(1, $newHeight));
            if (!$target) {
                imagedestroy($source);
                return null;
            }

            $mime = (string) ($imageInfo['mime'] ?? '');
            $preserveAlpha = in_array($mime, ['image/png', 'image/webp', 'image/gif'], true) && $this->canWriteAlpha();

            if ($preserveAlpha) {
                imagealphablending($target, false);
                imagesavealpha($target, true);
                $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
                imagefilledrectangle($target, 0, 0, $maxWidth, max(1, $newHeight), $transparent);
            } else {
                $white = imagecolorallocate($target, 255, 255, 255);
                imagefilledrectangle($target, 0, 0, $maxWidth, max(1, $newHeight), $white);
            }

            imagecopyresampled($target, $source, 0, 0, 0, 0, $maxWidth, max(1, $newHeight), $width, $height);
            imagedestroy($source);
            $source = $target;
        }

        $extension = $this->preferredImageExtension();
        $relativePath = trim($directory, '/');
        $relativePath = $relativePath === '' ? Str::uuid()->toString() . '.' . $extension : $relativePath . '/' . Str::uuid()->toString() . '.' . $extension;

        $tempPath = tempnam(sys_get_temp_dir(), 'codex_img_');
        if ($tempPath === false) {
            imagedestroy($source);
            return null;
        }

        $written = false;
        if ($extension === 'webp' && function_exists('imagewebp')) {
            $written = imagewebp($source, $tempPath, $quality);
        } else {
            if (function_exists('imageinterlace')) {
                imageinterlace($source, true);
            }
            $written = imagejpeg($source, $tempPath, $quality);
        }

        imagedestroy($source);

        if (!$written) {
            @unlink($tempPath);
            return null;
        }

        $contents = file_get_contents($tempPath);
        if ($contents === false) {
            @unlink($tempPath);
            return null;
        }

        Storage::disk($disk)->put($relativePath, $contents);
        @unlink($tempPath);

        return $relativePath;
    }

    protected function deleteStoredFiles(array|string|null $paths, string $disk = 'public'): void
    {
        $list = is_array($paths) ? $paths : [$paths];
        $filtered = array_values(array_filter($list, fn ($path) => is_string($path) && trim($path) !== ''));

        if ($filtered !== []) {
            Storage::disk($disk)->delete($filtered);
        }
    }

    protected function isImageFile(UploadedFile $file): bool
    {
        return str_starts_with((string) $file->getMimeType(), 'image/');
    }

    protected function createImageResource(string $path, string $mime): \GdImage|false
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/gif' => imagecreatefromgif($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : false,
            'image/bmp' => function_exists('imagecreatefrombmp') ? imagecreatefrombmp($path) : false,
            default => false,
        };
    }

    protected function preferredImageExtension(): string
    {
        return function_exists('imagewebp') ? 'webp' : 'jpg';
    }

    protected function canWriteAlpha(): bool
    {
        return function_exists('imagesavealpha');
    }
}
