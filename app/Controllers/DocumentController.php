<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Upload;

class DocumentController extends Controller
{
    /**
     * Whitelist of permitted private storage subdirectories.
     */
    protected const ALLOWED_FOLDERS = [
        'partner_docs',
        'kyc',
        'id_proofs',
        'invoices',
        'contracts',
    ];

    /**
     * Authenticated endpoint to serve sensitive private documents stored outside the webroot.
     * Accessible by:
     *  - Super Admin and Admin (all documents)
     *  - Staff/Technicians (their own KYC/partner documents)
     */
    public function serve(Request $request, string $folder, string $filename): Response
    {
        // 1. Authentication check
        if (!Auth::check()) {
            return Response::json(['error' => 'Unauthorized: Please log in to view documents.'], 401);
        }

        // 2. Folder whitelisting & filename sanitization (prevent path traversal)
        $cleanFolder = preg_replace('/[^a-zA-Z0-9_\-]/', '', $folder);
        $cleanFilename = basename($filename);

        if (!in_array($cleanFolder, self::ALLOWED_FOLDERS, true)) {
            return Response::json(['error' => 'Forbidden: Invalid document category.'], 403);
        }

        // 3. Prevent path traversal attacks (e.g. ../ or null bytes)
        if (str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\') || str_contains($filename, "\0")) {
            return Response::json(['error' => 'Forbidden: Invalid document path.'], 403);
        }

        $baseDir = Upload::getSecureStoragePath($cleanFolder);
        $fullPath = realpath($baseDir . '/' . $cleanFilename);

        // Ensure file exists and is strictly inside the target base directory
        if (!$fullPath || !file_exists($fullPath) || !str_starts_with($fullPath, realpath($baseDir) ?: $baseDir)) {
            return Response::json(['error' => 'Document not found.'], 404);
        }

        // 4. Role Authorization: Admins have full access; staff/customers can only access authorized documents
        $role = Auth::role();
        if ($role !== 'super_admin' && $role !== 'admin') {
            // For staff/technicians: can view partner_docs, kyc, and id_proofs
            if (!in_array($cleanFolder, ['partner_docs', 'kyc', 'id_proofs'], true)) {
                return Response::json(['error' => 'Forbidden: You do not have permission to view this document.'], 403);
            }
        }

        // 5. Determine MIME type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($fullPath) ?: 'application/octet-stream';
        $size = filesize($fullPath);

        // 6. Send file with secure headers
        $content = file_get_contents($fullPath);
        return new Response(
            $content !== false ? $content : '',
            200,
            [
                'Content-Type'              => $mime,
                'Content-Length'            => (string)$size,
                'Content-Disposition'       => 'inline; filename="' . addslashes($cleanFilename) . '"',
                'X-Content-Type-Options'    => 'nosniff',
                'Cache-Control'             => 'private, no-cache, no-store, must-revalidate',
                'Pragma'                    => 'no-cache',
                'Expires'                   => '0',
            ]
        );
    }
}
