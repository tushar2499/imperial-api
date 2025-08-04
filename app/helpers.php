<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * File Uploaded
 *
 * @param UploadedFile|null $file
 * @param string $path
 *
 * @return string | null
 */

if (!function_exists('file_uploaded')) {
    function file_uploaded(UploadedFile | null $file, string $path): ?string
    {

        if (!$file || !$file->isValid()) {
            return null;
        }

        $path = "uploads/$path";

        $fullPath = public_path($path);

        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        $imageName = time() . '_' . Str::random(20) . '.' . $file->getClientOriginalExtension();

        if ($file->move($fullPath, $imageName)) {
            return $path . '/' . $imageName;
        }

        return null;
    }

}

/**
 * Uploaded file Delete
 *
 * @param string | null $path
 * @return void
 */
if (!function_exists('uploaded_file_delete')) {
    function uploaded_file_delete(string | null $path): void
    {
        if (!$path) {
            return;
        }

        $path = public_path($path);

        if (file_exists($path)) {
            unlink($path);
        }

    }

}
