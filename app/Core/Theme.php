<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\Setting;

class Theme
{
    /**
     * White-label client presets
     */
    public const PRESETS = [
        'refixel_signature' => [
            'id'             => 'refixel_signature',
            'name'           => 'REFIXEL Signature (Orange & Navy)',
            'primary_color'  => '#f25b29',
            'primary_hover'  => '#d94a1d',
            'secondary_color'=> '#0a1c33',
            'accent_color'   => '#ff7a45',
            'font_slug'      => 'outfit',
            'border_radius'  => '10px',
            'tag'            => 'Refixel Default',
            'description'    => 'Signature high-contrast electric orange & deep midnight navy. Energetic, trusted, and memorable.',
        ],
        'corporate_blue' => [
            'id'             => 'corporate_blue',
            'name'           => 'Corporate Royal (Blue & Slate)',
            'primary_color'  => '#2563eb',
            'primary_hover'  => '#1d4ed8',
            'secondary_color'=> '#0f172a',
            'accent_color'   => '#38bdf8',
            'font_slug'      => 'inter',
            'border_radius'  => '8px',
            'tag'            => 'Corporate & Facilities',
            'description'    => 'Authoritative royal blue with deep slate accents. Ideal for B2B facility management & enterprise clients.',
        ],
        'emerald_clean' => [
            'id'             => 'emerald_clean',
            'name'           => 'Emerald Eco-Clean (Green & Forest)',
            'primary_color'  => '#059669',
            'primary_hover'  => '#047857',
            'secondary_color'=> '#064e3b',
            'accent_color'   => '#34d399',
            'font_slug'      => 'plus_jakarta_sans',
            'border_radius'  => '12px',
            'tag'            => 'Eco & Deep Cleaning',
            'description'    => 'Fresh botanical green with dark forest tones. Perfect for eco-friendly, sanitization, and green home care.',
        ],
        'royal_purple' => [
            'id'             => 'royal_purple',
            'name'           => 'Royal Luxury (Amethyst & Indigo)',
            'primary_color'  => '#7c3aed',
            'primary_hover'  => '#6d28d9',
            'secondary_color'=> '#1e1b4b',
            'accent_color'   => '#a78bfa',
            'font_slug'      => 'poppins',
            'border_radius'  => '14px',
            'tag'            => 'Premium & Concierge',
            'description'    => 'Vibrant royal amethyst with rich midnight indigo. Tailored for high-end residential & luxury villas.',
        ],
        'modern_crimson' => [
            'id'             => 'modern_crimson',
            'name'           => 'Modern Crimson (Ruby & Charcoal)',
            'primary_color'  => '#dc2626',
            'primary_hover'  => '#b91c1c',
            'secondary_color'=> '#18181b',
            'accent_color'   => '#f87171',
            'font_slug'      => 'montserrat',
            'border_radius'  => '8px',
            'tag'            => 'Fast Express & Repairs',
            'description'    => 'High-urgency bold crimson red on sleek dark charcoal. Engineered for quick on-demand emergency repairs.',
        ],
        'amber_gold' => [
            'id'             => 'amber_gold',
            'name'           => 'Golden Amber (Warm Honey & Graphite)',
            'primary_color'  => '#d97706',
            'primary_hover'  => '#b45309',
            'secondary_color'=> '#1f2937',
            'accent_color'   => '#fbbf24',
            'font_slug'      => 'dm_sans',
            'border_radius'  => '10px',
            'tag'            => 'Warm Hospitality',
            'description'    => 'Warm golden honey tone with sophisticated graphite slate. Inviting, friendly, and comforting.',
        ],
    ];

    /**
     * Supported Google Font catalog
     */
    public const FONTS = [
        'outfit' => [
            'name'   => 'Outfit',
            'family' => "'Outfit', sans-serif",
            'url'    => 'https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap',
            'desc'   => 'Modern, geometric, and high-impact. REFIXEL default.',
        ],
        'inter' => [
            'name'   => 'Inter',
            'family' => "'Inter', sans-serif",
            'url'    => 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
            'desc'   => 'Ultra-clean, crisp tech aesthetic with exceptional legibility.',
        ],
        'inter_tight' => [
            'name'   => 'Inter Tight',
            'family' => "'Inter Tight', sans-serif",
            'url'    => 'https://fonts.googleapis.com/css2?family=Inter+Tight:ital,wght@0,300..900;1,300..900&display=swap',
            'desc'   => 'Compact, high-density sans for information-rich interfaces.',
        ],
        'poppins' => [
            'name'   => 'Poppins',
            'family' => "'Poppins', sans-serif",
            'url'    => 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap',
            'desc'   => 'Warm, rounded geometric sans with friendly, approachable appeal.',
        ],
        'plus_jakarta_sans' => [
            'name'   => 'Plus Jakarta Sans',
            'family' => "'Plus Jakarta Sans', sans-serif",
            'url'    => 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap',
            'desc'   => 'High-end modern SaaS standard. Elegant and polished.',
        ],
        'montserrat' => [
            'name'   => 'Montserrat',
            'family' => "'Montserrat', sans-serif",
            'url'    => 'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap',
            'desc'   => 'Bold, authoritative, and architectural.',
        ],
        'dm_sans' => [
            'name'   => 'DM Sans',
            'family' => "'DM Sans', sans-serif",
            'url'    => 'https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300..900;1,9..40,300..900&display=swap',
            'desc'   => 'Contemporary, airy, and minimalist.',
        ],
        'roboto' => [
            'name'   => 'Roboto',
            'family' => "'Roboto', sans-serif",
            'url'    => 'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap',
            'desc'   => 'Google classic with maximum universal cross-device familiarity.',
        ],
    ];

    public static function getPreset(): string
    {
        return (string)Setting::get('theme_preset', 'refixel_signature');
    }

    public static function getPrimaryColor(): string
    {
        return (string)Setting::get('theme_primary_color', '#f25b29');
    }

    public static function getPrimaryHover(): string
    {
        return (string)Setting::get('theme_primary_hover', '#d94a1d');
    }

    public static function getSecondaryColor(): string
    {
        return (string)Setting::get('theme_secondary_color', '#0a1c33');
    }

    public static function getAccentColor(): string
    {
        return (string)Setting::get('theme_accent_color', '#ff7a45');
    }

    public static function getFontSlug(): string
    {
        $slug = (string)Setting::get('theme_font_slug', 'outfit');
        return isset(self::FONTS[$slug]) ? $slug : 'outfit';
    }

    public static function getFontFamily(): string
    {
        $slug = self::getFontSlug();
        return self::FONTS[$slug]['family'] ?? "'Outfit', sans-serif";
    }

    public static function getGoogleFontUrl(): string
    {
        $slug = self::getFontSlug();
        return self::FONTS[$slug]['url'] ?? self::FONTS['outfit']['url'];
    }

    public static function getBorderRadius(): string
    {
        return (string)Setting::get('theme_border_radius', '10px');
    }

    public static function getBrandName(): string
    {
        return (string)Setting::get('business_name', Setting::get('company_name', 'REFIXEL Home Services'));
    }

    public static function getBrandTagline(): string
    {
        return (string)Setting::get('hero_tagline', 'Starting at ₹999 • Save up to 25% on your first booking');
    }

    public static function getLogo(): string
    {
        $logo = (string)Setting::get('site_logo', 'img/refixel-logo-horizontal.png');
        if (empty($logo) || $logo === 'img/logo.png' || $logo === 'logo.png') {
            return 'img/refixel-logo-horizontal.png';
        }
        return $logo;
    }

    public static function getFavicon(): string
    {
        return (string)Setting::get('site_favicon', 'img/favicon.png');
    }

    /**
     * Convert Hex color to "R, G, B" string for CSS rgba()
     */
    public static function hexToRgb(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (strlen($hex) !== 6) {
            return '242, 91, 41';
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "{$r}, {$g}, {$b}";
    }

    /**
     * Render the complete HTML head tags:
     * 1. Google Font preconnect & stylesheet
     * 2. Comprehensive dynamic CSS rules overriding colors, fonts, buttons, badges, navigation
     */
    public static function renderHead(): string
    {
        $primary = self::getPrimaryColor();
        $primaryHover = self::getPrimaryHover();
        $secondary = self::getSecondaryColor();
        $accent = self::getAccentColor();
        $fontFamily = self::getFontFamily();
        $fontUrl = self::getGoogleFontUrl();
        $radius = self::getBorderRadius();
        $primaryRgb = self::hexToRgb($primary);
        $secondaryRgb = self::hexToRgb($secondary);

        $html = "<!-- Dynamic Client Branding & Theme Engine -->\n";
        $html .= "<link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">\n";
        $html .= "<link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>\n";
        $html .= "<link href=\"{$fontUrl}\" rel=\"stylesheet\">\n";
        $html .= "<style id=\"refixel-dynamic-theme-vars\">\n";
        $html .= ":root {\n";
        $html .= "  --primary-color: {$primary};\n";
        $html .= "  --primary-hover: {$primaryHover};\n";
        $html .= "  --primary-rgb: {$primaryRgb};\n";
        $html .= "  --primary-light: rgba({$primaryRgb}, 0.12);\n";
        $html .= "  --primary-glow: rgba({$primaryRgb}, 0.35);\n";
        $html .= "  --secondary-color: {$secondary};\n";
        $html .= "  --secondary-rgb: {$secondaryRgb};\n";
        $html .= "  --accent-color: {$accent};\n";
        $html .= "  --font-family-base: {$fontFamily};\n";
        $html .= "  --brand-radius: {$radius};\n";
        $html .= "  --main-color: {$secondary};\n";
        $html .= "}\n";

        // Global font application
        $html .= "body, h1, h2, h3, h4, h5, h6, .btn, .nav-link, input, select, textarea, .hdr-cart, .badge, .dropdown-item {\n";
        $html .= "  font-family: var(--font-family-base) !important;\n";
        $html .= "}\n";

        // Primary Buttons & Action Callouts
        $html .= ".btn-primary, .top_offer_btn, .btn_add_to_cart, .cart-counter, .badge-primary, .page_title_ant h6, .btn-brand, .btn-refixel, .btn-checkout, .btn-primary-custom, a.btn-primary {\n";
        $html .= "  background-color: var(--primary-color) !important;\n";
        $html .= "  border-color: var(--primary-color) !important;\n";
        $html .= "  color: #ffffff !important;\n";
        $html .= "}\n";

        $html .= ".btn-primary:hover, .btn_add_to_cart:hover, .top_offer_btn:hover, .btn-brand:hover, .btn-refixel:hover, .btn-checkout:hover {\n";
        $html .= "  background-color: var(--primary-hover) !important;\n";
        $html .= "  border-color: var(--primary-hover) !important;\n";
        $html .= "  color: #ffffff !important;\n";
        $html .= "}\n";

        // Outlined Buttons
        $html .= ".btn-outline-primary {\n";
        $html .= "  color: var(--primary-color) !important;\n";
        $html .= "  border-color: var(--primary-color) !important;\n";
        $html .= "}\n";
        $html .= ".btn-outline-primary:hover {\n";
        $html .= "  background-color: var(--primary-color) !important;\n";
        $html .= "  color: #ffffff !important;\n";
        $html .= "}\n";

        // Secondary / Brand Header Bar
        $html .= ".top_offer_bar {\n";
        $html .= "  background-color: var(--secondary-color) !important;\n";
        $html .= "}\n";

        // Header Cart & Icons
        $html .= ".hdr-cart {\n";
        $html .= "  border-color: var(--primary-glow) !important;\n";
        $html .= "}\n";
        $html .= ".hdr-cart svg, .cart-icon-svg {\n";
        $html .= "  stroke: var(--primary-color) !important;\n";
        $html .= "}\n";

        // Text & Links
        $html .= ".text-primary, a.text-primary, .link-primary, .brand-text-accent {\n";
        $html .= "  color: var(--primary-color) !important;\n";
        $html .= "}\n";
        $html .= ".footer-nav-link:hover {\n";
        $html .= "  color: var(--primary-color) !important;\n";
        $html .= "}\n";
        $html .= ".footer-social-btn:hover {\n";
        $html .= "  background: var(--primary-color) !important;\n";
        $html .= "  box-shadow: 0 5px 14px var(--primary-glow) !important;\n";
        $html .= "}\n";

        // Active States & Form Focus
        $html .= ".form-control:focus, .custom-select:focus {\n";
        $html .= "  border-color: var(--primary-color) !important;\n";
        $html .= "  box-shadow: 0 0 0 0.2rem var(--primary-light) !important;\n";
        $html .= "}\n";
        $html .= ".nav-pills .nav-link.active, .nav-tabs .nav-link.active {\n";
        $html .= "  color: var(--primary-color) !important;\n";
        $html .= "  border-bottom-color: var(--primary-color) !important;\n";
        $html .= "}\n";
        $html .= ".pagination .page-item.active .page-link {\n";
        $html .= "  background-color: var(--primary-color) !important;\n";
        $html .= "  border-color: var(--primary-color) !important;\n";
        $html .= "}\n";

        // Superadmin & Admin Sidebar active accents
        $html .= ".super-nav li.active a, .sidebar-nav li.active a {\n";
        $html .= "  border-left-color: var(--primary-color) !important;\n";
        $html .= "}\n";

        $html .= "</style>\n";

        return $html;
    }
}
