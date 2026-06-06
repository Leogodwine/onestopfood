<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class MealImageService
{
    /** Display cards use ~140–210px height at full column width; 3:2 landscape fits best. */
    public const RECOMMENDED_WIDTH = 1200;

    public const RECOMMENDED_HEIGHT = 800;

    public const MIN_WIDTH = 400;

    public const MIN_HEIGHT = 300;

    public const MAX_WIDTH = 1200;

    public const MAX_HEIGHT = 800;

    public const JPEG_QUALITY = 85;

    public function store(UploadedFile $file): string
    {
        if (! extension_loaded('gd')) {
            return $file->store('meals', 'public');
        }

        if ($file->getMimeType() === 'image/gif') {
            return $file->store('meals', 'public');
        }

        $path = $file->getRealPath();
        $size = @getimagesize($path);

        if ($size === false) {
            throw new RuntimeException('Unable to read the uploaded image.');
        }

        [$width, $height] = $size;

        $image = $this->loadImage($file, $path);

        if (! $image instanceof GdImage) {
            throw new RuntimeException('Unable to process the uploaded image.');
        }

        $processed = $this->resizeToFit($image, $width, $height);

        $filename = 'meals/' . Str::uuid()->toString() . '.jpg';
        Storage::disk('public')->makeDirectory('meals');

        $saved = imagejpeg($processed, Storage::disk('public')->path($filename), self::JPEG_QUALITY);

        if ($processed instanceof GdImage) {
            imagedestroy($processed);
        }

        if (! $saved) {
            throw new RuntimeException('Unable to save the meal image.');
        }

        return $filename;
    }

    public function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        $normalized = ltrim($path, '/');

        if (Storage::disk('public')->exists($normalized)) {
            Storage::disk('public')->delete($normalized);
        }
    }

    private function loadImage(UploadedFile $file, string $path): ?GdImage
    {
        return match ($file->getMimeType()) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path) ?: null,
            'image/png' => @imagecreatefrompng($path) ?: null,
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) ?: null : null,
            default => null,
        };
    }

    private function resizeToFit(GdImage $image, int $width, int $height): GdImage
    {
        if ($width <= self::MAX_WIDTH && $height <= self::MAX_HEIGHT) {
            return $this->flattenToJpegCanvas($image, $width, $height);
        }

        $ratio = min(self::MAX_WIDTH / $width, self::MAX_HEIGHT / $height);
        $newWidth = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        $white = imagecolorallocate($resized, 255, 255, 255);
        imagefill($resized, 0, 0, $white);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);

        return $resized;
    }

    private function flattenToJpegCanvas(GdImage $image, int $width, int $height): GdImage
    {
        $canvas = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);
        imagedestroy($image);

        return $canvas;
    }
}
