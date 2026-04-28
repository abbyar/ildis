<?php

// FileHelper.php
namespace backend\web\components;

use Yii;
use yii\base\Module;

class FileHelper extends Module
{
    /**
     * Sanitize uploaded filename: validate extension and generate random name.
     *
     * @param string $filename Original uploaded filename
     * @param array $allowedExtensions List of allowed file extensions
     * @return string Sanitized filename with random name
     * @throws \yii\web\BadRequestHttpException if extension is not allowed
     */
    public static function sanitizeFilename($filename, $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'])
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Tolak double extension (misalnya shell.php.jpg)
        $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
        $dangerousExtensions = ['php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'phps', 'cgi', 'pl', 'asp', 'aspx', 'shtml', 'shtm', 'htaccess', 'htpasswd', 'ini', 'log', 'sh', 'bat', 'exe', 'dll', 'com', 'js', 'jsp', 'py', 'rb', 'svg'];
        
        // Cek apakah ada ekstensi berbahaya di dalam nama file
        foreach ($dangerousExtensions as $dangerous) {
            if (stripos($nameWithoutExt, '.' . $dangerous) !== false) {
                throw new \yii\web\BadRequestHttpException('Nama file mengandung ekstensi berbahaya.');
            }
        }

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new \yii\web\BadRequestHttpException('Tipe file tidak diizinkan. Hanya diizinkan: ' . implode(', ', $allowedExtensions));
        }

        // Generate nama file random untuk mencegah overwrite dan prediksi nama
        return Yii::$app->security->generateRandomString(16) . '.' . $extension;
    }
}
