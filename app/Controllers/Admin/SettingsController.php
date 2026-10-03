<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index(Request $request): Response
    {
        $settings = Setting::getAllKeyValue();
        return $this->render('admin.settings.index', [
            'title'    => 'Business & Notification Settings | Primodomus Admin',
            'settings' => $settings,
        ], 'admin');
    }

    public function save(Request $request): Response
    {
        foreach ($request->all() as $k => $v) {
            if ($k === '_csrf') continue;
            Setting::set($k, (string)$v);
        }
        View::setFlash('success', 'Business and notification settings updated successfully.');
        return $this->redirect('/admin/settings');
    }
}
