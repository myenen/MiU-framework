<?php

declare(strict_types=1);

namespace App\Services;

use Core\Orm\Models;
use RuntimeException;

/**
 * Dosya yukleme, dizin organizasyonu ve upload metadata kaydini yonetir.
 */
final class FileUploadService
{
    /**
     * @param string $uploadRoot Herkese acik yukleme kok dizini.
     */
    public function __construct(
        private readonly string $uploadRoot
    ) {
        $this->ensureDirectory($this->uploadRoot);
    }

    /**
     * Yuklenen dosyayi yil/ay/gun klasor yapisinda kaydeder ve metadata'sini veritabanina yazar.
     *
     * @param array $file PHP'den gelen yuklenen dosya dizisi.
     * @param string $directory Yukleme kok dizini altindaki kanal klasoru.
     * @param array $options Izin verilen uzantilar ve azami boyut gibi yukleme kisitlari.
     * @return array
     */
    public function uploadFile(array $file, string $directory = 'common', array $options = []): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('File upload failed.');
        }

        $maxSize = (int) ($options['max_size'] ?? 5 * 1024 * 1024);
        $allowedExtensions = $options['allowed_extensions'] ?? [];
        $originalName = (string) ($file['name'] ?? 'file');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ((int) ($file['size'] ?? 0) > $maxSize) {
            throw new RuntimeException('Uploaded file exceeds the allowed size.');
        }

        if ($allowedExtensions !== [] && ! in_array($extension, $allowedExtensions, true)) {
            throw new RuntimeException('Uploaded file extension is not allowed.');
        }

        $channel = trim($directory, '/');
        if ($channel === '') {
            $channel = 'common';
        }

        $timestamp = time();
        $dateDirectory = date('Y/m/d', $timestamp);
        $storageDirectory = $channel . '/' . $dateDirectory;
        $targetDirectory = rtrim($this->uploadRoot . '/' . $storageDirectory, '/');
        $this->ensureDirectory($targetDirectory);

        $baseName = $this->sanitizeFileName(pathinfo($originalName, PATHINFO_FILENAME));
        $fileName = $this->resolveUniqueFileName($targetDirectory, $baseName, $extension);
        $targetPath = $targetDirectory . '/' . $fileName;
        $tmpName = (string) ($file['tmp_name'] ?? '');

        $moved = is_uploaded_file($tmpName)
            ? move_uploaded_file($tmpName, $targetPath)
            : rename($tmpName, $targetPath);

        if (! $moved) {
            throw new RuntimeException('Uploaded file could not be moved.');
        }

        $upload = Models::get('uploads');
        $upload->channel = $channel;
        $upload->original_name = $originalName;
        $upload->stored_name = $fileName;
        $upload->directory_name = $storageDirectory;
        $upload->mime_type = (string) ($file['type'] ?? '');
        $upload->extension = $extension;
        $upload->size = (int) ($file['size'] ?? 0);
        $upload->public_path = $this->publicUploadPath($storageDirectory, $fileName);
        $upload->created_at = $timestamp;
        $saved = $upload->save();

        if (is_object($saved) && (bool) ($saved->error ?? false)) {
            @unlink($targetPath);
            throw new RuntimeException((string) ($saved->msg ?? 'Upload metadata could not be saved.'));
        }

        return [
            'original_name' => $originalName,
            'stored_name' => $fileName,
            'directory' => $storageDirectory,
            'path' => $targetPath,
            'public_path' => $this->publicUploadPath($storageDirectory, $fileName),
            'size' => (int) ($file['size'] ?? 0),
            'extension' => $extension,
        ];
    }

    /**
     * Yuklenen dosya ya da klasor icin herkese acik URL yolu olusturur.
     *
     * @param string $directory Yukleme dizini adi.
     * @param string $fileName Opsiyonel dosya adi.
     * @return string
     */
    public function publicUploadPath(string $directory = 'common', string $fileName = ''): string
    {
        $path = '/uploads/' . trim($directory, '/');

        if ($fileName !== '') {
            $path .= '/' . $fileName;
        }

        return $path;
    }

    /**
     * Ayni klasorde cakisani olmayan dosya adini cozer.
     *
     * @param string $directory Hedef klasor yolu.
     * @param string $baseName Dosya baz adi.
     * @param string $extension Dosya uzantisi.
     * @return string
     */
    private function resolveUniqueFileName(string $directory, string $baseName, string $extension): string
    {
        $suffix = '';
        $counter = 0;

        do {
            $candidate = $baseName . $suffix;

            if ($extension !== '') {
                $candidate .= '.' . $extension;
            }

            $counter++;
            $suffix = '_' . $counter;
        } while (file_exists($directory . '/' . $candidate));

        return $candidate;
    }

    /**
     * Orijinal dosya adini guvenli ve tekrar kullanilabilir bir dosya adina cevirir.
     *
     * @param string $name Ham dosya adi.
     * @return string
     */
    private function sanitizeFileName(string $name): string
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/[^a-z0-9\-_]+/', '-', $name) ?? $name;
        $name = trim($name, '-_.');

        return $name !== '' ? $name : 'file';
    }

    /**
     * Dosya islemlerinden once dizinin var oldugundan emin olur.
     *
     * @param string $path Dizin yolu.
     * @return void
     */
    private function ensureDirectory(string $path): void
    {
        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }
}
