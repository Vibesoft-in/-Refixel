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

        // Restrict API upload subfolders (only 'issues' allowed for unauthenticated visitors)
        $allowedPublicFolders = ['issues'];
        if (!in_array($subfolder, $allowedPublicFolders, true) && !\App\Core\Auth::isAdmin()) {
            return $this->json(['status' => 'error', 'message' => 'Unauthorized upload destination.'], 403);
        }

        try {
            $path = Upload::process($file, $subfolder);
            return $this->json(['status' => 'success', 'path' => $path]);
        } catch (\Throwable $e) {
            return $this->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}
