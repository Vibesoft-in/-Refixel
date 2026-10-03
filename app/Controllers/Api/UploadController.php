<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Upload;

class UploadController extends Controller
{
    public function upload(Request $request): Response
    {
        $file = $request->file('file');
        $subfolder = (string)$request->input('subfolder', 'issues');

        if (!$file) {
            return $this->json(['status' => 'error', 'message' => 'No file uploaded.'], 400);
        }

        try {
            $path = Upload::process($file, $subfolder);
            return $this->json(['status' => 'success', 'path' => $path]);
        } catch (\Throwable $e) {
            return $this->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}
