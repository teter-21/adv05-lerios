<?php

function userImageUpload(string $username, ?string $oldPath = null): ?string
{
    $file = $_FILES['imageUpload'] ?? null;
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) return $oldPath;
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Image upload failed (code ' . $file['error'] . ').');
    if ($file['size'] > 5 * 1024 * 1024) throw new RuntimeException('Image must be 5 MB or less.');
    if (!preg_match('/^[a-zA-Z0-9_.-]{1,50}$/', $username) || $username === '.' || $username === '..') {
        throw new RuntimeException('Username may contain only letters, numbers, dots, dashes and underscores.');
    }
    if (!is_uploaded_file($file['tmp_name'])) throw new RuntimeException('Invalid upload.');
    $info = @getimagesize($file['tmp_name']);
    if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF], true)) {
        throw new RuntimeException('Select a JPG, PNG, or GIF image.');
    }
    $gdEnabled = function_exists('imagecreatefromjpeg') && function_exists('imagejpeg');
    if (!$gdEnabled && $info[2] !== IMAGETYPE_JPEG) {
        throw new RuntimeException('the uploaded image is not a real JPEG.');
    }
    if ($info[0] > 5000 || $info[1] > 5000) throw new RuntimeException('Image dimensions are too large.');
    if (!$gdEnabled) {
        $folder = __DIR__ . '/images/users/';
        if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
            throw new RuntimeException('Cannot create images/users directory.');
        }
        $relative = 'images/users/' . $username . '.JPG';
        if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/' . $relative)) {
            throw new RuntimeException('Unable to save image into images/users/. Check folder permissions.');
        }
        return $relative;
    }
    switch ($info[2]) {
        case IMAGETYPE_JPEG:
            $source = @imagecreatefromjpeg($file['tmp_name']);
            break;
        case IMAGETYPE_PNG:
            $source = @imagecreatefrompng($file['tmp_name']);
            break;
        default:
            $source = @imagecreatefromgif($file['tmp_name']);
    }
    if (!$source) throw new RuntimeException('Unable to read image.');
    $dest = imagecreatetruecolor(imagesx($source), imagesy($source));
    imagefill($dest, 0, 0, imagecolorallocate($dest, 255, 255, 255));
    imagecopy($dest, $source, 0, 0, 0, 0, imagesx($source), imagesy($source));
    imagedestroy($source);
    $folder = __DIR__ . '/images/users/';
    if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
        imagedestroy($dest);
        throw new RuntimeException('Cannot create images/users directory.');
    }
    $relative = 'images/users/' . $username . '.JPG';
    $temp = tempnam($folder, 'img_');
    if (!$temp || !imagejpeg($dest, $temp, 88)) {
        imagedestroy($dest);
        if ($temp) @unlink($temp);
        throw new RuntimeException('Could not save profile image.');
    }
    imagedestroy($dest);
    if (!rename($temp, __DIR__ . '/' . $relative)) {
        @unlink($temp);
        throw new RuntimeException('Could not finalize image.');
    }
    if ($oldPath && $oldPath !== $relative && str_starts_with($oldPath, 'images/users/')) {
        @unlink(__DIR__ . '/' . $oldPath);
    }
    return $relative;
}
function removeUserImage(?string $path): void
{
    if ($path && preg_match('~^images/users/[a-zA-Z0-9_.-]+\.JPG$~', $path)) @unlink(__DIR__ . '/' . $path);
}
