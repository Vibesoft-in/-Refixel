<?php
declare(strict_types=1);

namespace App\Controllers\SuperAdmin;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Theme;
use App\Core\Upload;
use App\Core\View;
use App\Models\Setting;

class ThemeController extends Controller
{
    public function index(Request $request): Response
    {
        $currentPreset = Theme::getPreset();
        $presets = Theme::PRESETS;
        $fonts = Theme::FONTS;

        $themeData = [
            'preset'          => $currentPreset,
            'primary_color'   => Theme::getPrimaryColor(),
            'primary_hover'   => Theme::getPrimaryHover(),
            'secondary_color' => Theme::getSecondaryColor(),
            'accent_color'    => Theme::getAccentColor(),
            'font_slug'       => Theme::getFontSlug(),
            'border_radius'   => Theme::getBorderRadius(),
            'brand_name'      => Theme::getBrandName(),
            'brand_tagline'   => Theme::getBrandTagline(),
            'site_logo'       => Theme::getLogo(),
            'site_favicon'    => Theme::getFavicon(),
        ];

        return $this->render('superadmin.theme.index', [
            'title'     => 'Theme & White-Label Customizer | REFIXEL Super Admin',
            'themeData' => $themeData,
            'presets'   => $presets,
            'fonts'     => $fonts,
        ], 'superadmin');
    }

    public function save(Request $request): Response
    {
        $preset = $request->input('theme_preset', 'custom');
        
        // If an established preset was selected and user didn't customize, apply preset defaults
        if ($preset !== 'custom' && isset(Theme::PRESETS[$preset]) && $request->input('apply_preset_defaults') === '1') {
            $p = Theme::PRESETS[$preset];
            Setting::set('theme_preset', $p['id']);
            Setting::set('theme_primary_color', $p['primary_color']);
            Setting::set('theme_primary_hover', $p['primary_hover']);
            Setting::set('theme_secondary_color', $p['secondary_color']);
            Setting::set('theme_accent_color', $p['accent_color']);
            Setting::set('theme_font_slug', $p['font_slug']);
            Setting::set('theme_border_radius', $p['border_radius']);
        } else {
            Setting::set('theme_preset', $preset);
            if ($request->has('theme_primary_color')) {
                Setting::set('theme_primary_color', trim($request->input('theme_primary_color')));
            }
            if ($request->has('theme_primary_hover')) {
                Setting::set('theme_primary_hover', trim($request->input('theme_primary_hover')));
            }
            if ($request->has('theme_secondary_color')) {
                Setting::set('theme_secondary_color', trim($request->input('theme_secondary_color')));
            }
            if ($request->has('theme_accent_color')) {
                Setting::set('theme_accent_color', trim($request->input('theme_accent_color')));
            }
            if ($request->has('theme_font_slug')) {
                Setting::set('theme_font_slug', trim($request->input('theme_font_slug')));
            }
            if ($request->has('theme_border_radius')) {
                Setting::set('theme_border_radius', trim($request->input('theme_border_radius')));
            }
        }

        // Brand Text Fields
        if ($request->has('business_name')) {
            $bName = trim($request->input('business_name'));
            Setting::set('business_name', $bName);
            Setting::set('company_name', $bName);
        }
        if ($request->has('hero_tagline')) {
            Setting::set('hero_tagline', trim($request->input('hero_tagline')));
        }

        // Image / Media Uploads
        $uploadFields = ['site_logo', 'site_favicon'];
        foreach ($uploadFields as $field) {
            $file = $request->file($field);
            if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                try {
                    $uploadedPath = Upload::process($file, 'site');
                    Setting::set($field, $uploadedPath);
                } catch (\Throwable $e) {
                    // Ignore or log
                }
            }
        }

        View::setFlash('success', 'Theme and white-label branding updated successfully! The entire website will now reflect your new styling and typography.');
        return $this->redirect('/superadmin/theme');
    }

    public function reset(Request $request): Response
    {
        $default = Theme::PRESETS['refixel_signature'];
        Setting::set('theme_preset', 'refixel_signature');
        Setting::set('theme_primary_color', $default['primary_color']);
        Setting::set('theme_primary_hover', $default['primary_hover']);
        Setting::set('theme_secondary_color', $default['secondary_color']);
        Setting::set('theme_accent_color', $default['accent_color']);
        Setting::set('theme_font_slug', $default['font_slug']);
        Setting::set('theme_border_radius', $default['border_radius']);

        View::setFlash('success', 'Theme reset to default REFIXEL brand identity (Signature Orange & Navy + Outfit Font).');
        return $this->redirect('/superadmin/theme');
    }
}
