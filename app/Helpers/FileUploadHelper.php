<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class FileUploadHelper
{
    /**
     * Upload a file to local or S3 based on environment.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder
     * @param string|null $filename
     * @return string  Full URL or local path
     */
    public static function upload($file, $folder = 'uploads', $filename = null)
    {
        $filename = $filename ?? uniqid() . '.' . $file->getClientOriginalExtension();

        // Local upload
        $path = public_path($folder);
        if (!file_exists($path)) {
            try {
                mkdir($path, 0777, true);
            } catch (\Throwable $e) {
                throw new \Exception("mkdir() failed at: " . $path . ". Error: " . $e->getMessage());
            }
        }

        $file->move($path, $filename);

        return url("api/nextjs/uploads/$folder/$filename"); // serve via API endpoint
    }


    public static function refNo()
    {
        $alphabet = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 6; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        //$Reference= $initcode . implode($pass);
        return implode($pass);
    }
}
