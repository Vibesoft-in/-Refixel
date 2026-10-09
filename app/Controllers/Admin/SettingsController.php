<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Upload;
use App\Core\View;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index(Request $request): Response
    {
        $settings = Setting::getAllKeyValue();
        return $this->render('admin.settings.index', [
            'title'    => 'Business & Media Settings | REFIXEL Admin',
            'settings' => $settings,
        ], 'admin');
    }

    public function save(Request $request): Response
    {
        // Save text inputs
        foreach ($request->all() as $k => $v) {
            if ($k === '_csrf') continue;
            Setting::set($k, (string)$v);
        }

        // Handle uploaded files (e.g., site_logo, hero_banner_image, promo_banner_image, about_image)
        $imageFields = ['site_logo', 'hero_banner_image', 'promo_banner_image', 'about_image', 'hero_secondary_image'];
        foreach ($imageFields as $field) {
            $file = $request->file($field);
            if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                try {
                    $uploadedPath = Upload::process($file, 'site');
                    Setting::set($field, $uploadedPath);
                } catch (\Throwable $e) {
                    // Silently ignore or continue
                }
            }
        }

        View::setFlash('success', 'Business, media, and notification settings updated successfully.');
        return $this->redirect('/admin/settings');
    }
}

