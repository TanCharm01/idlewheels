<?php
declare(strict_types=1);

namespace App\Core;

class Upload
{
    private const MAX_BYTES = 8 * 1024 * 1024;
    private const TYPES = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];

    // The smallest of our own limit and the server's PHP limits.
    public static function maxBytes(): int
    {
        return min(self::MAX_BYTES, self::iniBytes('upload_max_filesize'), self::iniBytes('post_max_size'));
    }

    // Total size of one form submission the server accepts.
    public static function maxPostBytes(): int
    {
        return self::iniBytes('post_max_size');
    }

    public static function mb(int $bytes): string
    {
        $mb = $bytes / 1048576;
        return ($mb == floor($mb) ? (string) (int) $mb : number_format($mb, 1)) . ' MB';
    }

    private static function iniBytes(string $key): int
    {
        $v = trim((string) ini_get($key));
        if ($v === '' || $v === '0' || $v === '-1') {
            return PHP_INT_MAX;
        }
        $n = (float) $v;
        switch (strtolower(substr($v, -1))) {
            case 'g': $n *= 1024;
            // no break
            case 'm': $n *= 1024;
            // no break
            case 'k': $n *= 1024;
        }
        return (int) $n;
    }

    public static function images(string $field, string $folder, int $max = 3): array
    {
        if (empty($_FILES[$field]) || !is_array($_FILES[$field]['name'])) {
            return [];
        }
        $dir = BASE_PATH . '/uploads/' . $folder;
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new \RuntimeException('Photos can\'t be saved yet: the uploads/' . $folder . ' folder isn\'t writable on the server. Set its permissions to 755 (or 775).');
        }
        $saved = [];
        $count = count($_FILES[$field]['name']);
        for ($i = 0; $i < $count && count($saved) < $max; $i++) {
            if ($_FILES[$field]['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $err = $_FILES[$field]['error'][$i];
            if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
                throw new \RuntimeException('"' . $_FILES[$field]['name'][$i] . '" is too big. This server accepts photos up to ' . self::mb(self::maxBytes()) . ' each.');
            }
            if ($err === UPLOAD_ERR_NO_TMP_DIR || $err === UPLOAD_ERR_CANT_WRITE || $err === UPLOAD_ERR_EXTENSION) {
                throw new \RuntimeException('The server could not receive the photo (upload error ' . $err . '). Ask the server admin to check PHP file uploads.');
            }
            if ($err !== UPLOAD_ERR_OK) {
                throw new \RuntimeException('A photo only partly uploaded. Please try again.');
            }
            $tmp = $_FILES[$field]['tmp_name'][$i];
            if ($_FILES[$field]['size'][$i] > self::maxBytes()) {
                throw new \RuntimeException('"' . $_FILES[$field]['name'][$i] . '" is too big. Photos can be up to ' . self::mb(self::maxBytes()) . ' each.');
            }
            $info = @getimagesize($tmp);
            $type = $info ? $info[2] : false;
            if (!isset(self::TYPES[$type])) {
                throw new \RuntimeException('Photos must be JPG, PNG or WebP images.');
            }
            $name = bin2hex(random_bytes(12)) . '.' . self::TYPES[$type];
            if (!move_uploaded_file($tmp, "$dir/$name")) {
                throw new \RuntimeException('A photo could not be saved. Check that the uploads/' . $folder . ' folder is writable (755 or 775).');
            }
            $saved[] = "uploads/$folder/$name";
        }
        return $saved;
    }

    public static function delete(string $path): void
    {
        if (strpos($path, 'uploads/') === 0) {
            $file = BASE_PATH . '/' . $path;
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}
