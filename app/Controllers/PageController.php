<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\ServiceArea;

class PageController extends Controller
{
    public function about(Request $request): Response
    {
        return $this->render('customer.about', ['title' => 'About Us | REFIXEL'], 'customer');
    }

    public function faq(Request $request): Response
    {
        $faqs = Faq::getGlobal();
        return $this->render('customer.faq', ['title' => 'FAQs | REFIXEL', 'faqs' => $faqs], 'customer');
    }

    public function gallery(Request $request): Response
    {
        $this->ensureGalleryItemsSeeded();

        $categories = \App\Models\Category::getActive();
        $services = \App\Models\Service::getActive();

        $activeCategory = trim((string)($request->query('category') ?? $request->query('cat') ?? ''));
        $activeService = trim((string)($request->query('service') ?? $request->query('svc') ?? ''));

        // If activeService is provided, normalize and auto-detect parent category
        if (!empty($activeService)) {
            foreach ($services as $svc) {
                if ($svc['slug'] === $activeService || (string)$svc['id'] === $activeService) {
                    $activeService = $svc['slug'];
                    if (empty($activeCategory) || $activeCategory === 'all') {
                        foreach ($categories as $cat) {
                            if ((int)$cat['id'] === (int)$svc['category_id']) {
                                $activeCategory = $cat['slug'];
                                break;
                            }
                        }
                    }
                    break;
                }
            }
        }

        if (empty($activeCategory)) {
            $activeCategory = 'all';
        }

        $items = \App\Core\Database::fetchAll(
            "SELECT g.*, s.name as service_name, s.slug as service_slug, c.name as category_name, c.slug as category_slug
             FROM gallery_items g
             LEFT JOIN services s ON g.service_id = s.id
             LEFT JOIN categories c ON s.category_id = c.id
             WHERE g.is_active = 1
             ORDER BY g.sort_order ASC, g.id DESC"
        );

        // Fallback safety: Ensure all 15 master showcases are present if DB rows were missing
        $items = $this->enrichGalleryItems($items, $services, $categories);

        // If specific service or category requested, prioritize matching showcases to appear first!
        if (!empty($activeService)) {
            usort($items, function ($a, $b) use ($activeService) {
                $aMatch = (($a['service_slug'] ?? '') === $activeService) ? 1 : 0;
                $bMatch = (($b['service_slug'] ?? '') === $activeService) ? 1 : 0;
                if ($aMatch !== $bMatch) {
                    return $bMatch <=> $aMatch;
                }
                return ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0);
            });
        } elseif (!empty($activeCategory) && $activeCategory !== 'all') {
            usort($items, function ($a, $b) use ($activeCategory) {
                $aMatch = (($a['category_slug'] ?? '') === $activeCategory) ? 1 : 0;
                $bMatch = (($b['category_slug'] ?? '') === $activeCategory) ? 1 : 0;
                if ($aMatch !== $bMatch) {
                    return $bMatch <=> $aMatch;
                }
                return ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0);
            });
        }

        return $this->render('customer.gallery', [
            'title'          => 'Work Showcase & Transformations | REFIXEL',
            'items'          => $items,
            'categories'     => $categories,
            'services'       => $services,
            'activeCategory' => $activeCategory,
            'activeService'  => $activeService,
        ], 'customer');
    }

    protected function ensureGalleryItemsSeeded(): void
    {
        try {
            $count = (int)(\App\Core\Database::fetchColumn("SELECT COUNT(*) FROM gallery_items WHERE is_active = 1") ?? 0);
            if ($count >= 15) {
                return;
            }

            // Category ID lookups
            $catClean = (int)(\App\Core\Database::fetchColumn("SELECT id FROM categories WHERE slug = 'cleaning' LIMIT 1") ?: 1);
            $catCeil = (int)(\App\Core\Database::fetchColumn("SELECT id FROM categories WHERE slug = 'fall-ceiling-services' LIMIT 1") ?: 9);

            $existingServiceSlugs = array_column(\App\Core\Database::fetchAll("SELECT slug FROM services"), 'slug');
            $existingSlugSet = array_flip($existingServiceSlugs);

            if (!isset($existingSlugSet['fall-ceiling-installation'])) {
                \App\Core\Database::query(
                    "INSERT INTO services (category_id, name, slug, description, starting_price, duration_minutes, image, is_active) VALUES
                    (:cat, 'Designer Fall Ceiling & POP Installation', 'fall-ceiling-installation', 'End-to-end false ceiling design & installation with heavy GI steel channel framing, Saint-Gobain gypsum boards, laser alignment, and concealed LED cove light provision.', 1499.00, 180, 'refixel-fall-ceiling.jpg', 1)",
                    ['cat' => $catCeil]
                );
            }
            if (!isset($existingSlugSet['fall-ceiling-repair-modification'])) {
                \App\Core\Database::query(
                    "INSERT INTO services (category_id, name, slug, description, starting_price, duration_minutes, image, is_active) VALUES
                    (:cat, 'Fall Ceiling Repair & Cove Light Modification', 'fall-ceiling-repair-modification', 'Precision repair of sagging, damp, or cracked POP/gypsum ceiling panels, joint re-taping, acoustic leveling, and cutting custom slots.', 699.00, 90, 'refixel-fall-ceiling.jpg', 1)",
                    ['cat' => $catCeil]
                );
            }
            if (!isset($existingSlugSet['balcony-deep-pressure-wash'])) {
                \App\Core\Database::query(
                    "INSERT INTO services (category_id, name, slug, description, starting_price, duration_minutes, image, is_active) VALUES
                    (:cat, 'Balcony Deep Pressure Wash', 'balcony-deep-pressure-wash', 'High-pressure water jet washing for balcony tiles, railings, glass panes, and bird dropping removal.', 899.00, 60, 'refixel-balcony-pressure-wash.png', 1)",
                    ['cat' => $catClean]
                );
            }

            $masterItems = $this->getMasterShowcases();
            $svcRows = \App\Core\Database::fetchAll("SELECT id, slug FROM services");
            $svcSlugToId = [];
            foreach ($svcRows as $sr) {
                $svcSlugToId[$sr['slug']] = (int)$sr['id'];
            }

            $existingGallery = \App\Core\Database::fetchAll("SELECT id, service_id, title FROM gallery_items");
            $existingSvcIds = [];
            foreach ($existingGallery as $eg) {
                if (!empty($eg['service_id'])) {
                    $existingSvcIds[(int)$eg['service_id']] = true;
                }
            }

            foreach ($masterItems as $m) {
                $targetSvcId = $svcSlugToId[$m['service_slug']] ?? null;
                if ($targetSvcId && !isset($existingSvcIds[$targetSvcId])) {
                    \App\Core\Database::query(
                        "INSERT INTO gallery_items (service_id, title, before_image, after_image, sort_order, is_active)
                         VALUES (:sid, :title, :before, :after, :sort, 1)",
                        [
                            'sid'    => $targetSvcId,
                            'title'  => $m['title'],
                            'before' => $m['before_image'],
                            'after'  => $m['after_image'],
                            'sort'   => $m['sort_order'],
                        ]
                    );
                    $existingSvcIds[$targetSvcId] = true;
                }
            }
        } catch (\Throwable $e) {
            \App\Core\Logger::error("Gallery auto-seed note: " . $e->getMessage());
        }
    }

    protected function getMasterShowcases(): array
    {
        return [
            ['id' => 1, 'service_id' => 15, 'service_slug' => 'balcony-deep-pressure-wash', 'category_slug' => 'cleaning', 'service_name' => 'Balcony Deep Pressure Wash', 'category_name' => 'Cleaning', 'title' => 'Balcony Deep Pressure Wash & Algae Eradication', 'before_image' => 'assets/img/transformations/balcony-before.jpg', 'after_image' => 'assets/img/transformations/balcony-after.jpg', 'sort_order' => 1],
            ['id' => 2, 'service_id' => 1, 'service_slug' => 'full-home-cleaning', 'category_slug' => 'cleaning', 'service_name' => 'Professional Full Home Cleaning', 'category_name' => 'Cleaning', 'title' => 'Full Home Deep Hygiene & Floor Scrubbing', 'before_image' => 'assets/img/transformations/homeclean-before.jpg', 'after_image' => 'assets/img/transformations/homeclean-after.jpg', 'sort_order' => 2],
            ['id' => 3, 'service_id' => 2, 'service_slug' => 'bathroom-deep-cleaning', 'category_slug' => 'cleaning', 'service_name' => 'Professional Bathroom Cleaning', 'category_name' => 'Cleaning', 'title' => 'Shower Glass Hard-Water Descaling & Tile Scrubbing', 'before_image' => 'assets/img/transformations/bathroom-before.jpg', 'after_image' => 'assets/img/transformations/bathroom-after.jpg', 'sort_order' => 3],
            ['id' => 4, 'service_id' => 3, 'service_slug' => 'kitchen-deep-cleaning', 'category_slug' => 'cleaning', 'service_name' => 'Kitchen Deep Cleaning', 'category_name' => 'Cleaning', 'title' => 'Kitchen Stove, Chimney & Tiles Deep Degreasing', 'before_image' => 'assets/img/transformations/kitchen-before.jpg', 'after_image' => 'assets/img/transformations/kitchen-after.jpg', 'sort_order' => 4],
            ['id' => 5, 'service_id' => 4, 'service_slug' => 'sofa-cleaning', 'category_slug' => 'cleaning', 'service_name' => 'Sofa & Upholstery Deep Cleaning', 'category_name' => 'Cleaning', 'title' => 'Fabric Sofa Deep Shampoo & Stain Extraction', 'before_image' => 'assets/img/transformations/sofa-before.jpg', 'after_image' => 'assets/img/transformations/sofa-after.jpg', 'sort_order' => 5],
            ['id' => 6, 'service_id' => 5, 'service_slug' => 'office-cleaning', 'category_slug' => 'cleaning', 'service_name' => 'Commercial Space & Office Cleaning', 'category_name' => 'Cleaning', 'title' => 'Commercial Workspace & Carpet Deep Sanitization', 'before_image' => 'assets/img/transformations/office-before.jpg', 'after_image' => 'assets/img/transformations/office-after.jpg', 'sort_order' => 6],
            ['id' => 7, 'service_id' => 10, 'service_slug' => 'ac-jet-service', 'category_slug' => 'ac-services', 'service_name' => 'AC High-Pressure Jet Service', 'category_name' => 'AC Service & Repair', 'title' => 'AC Cooling Coil Deep Jet Wash & Mold Decontamination', 'before_image' => 'assets/img/transformations/ac-before.jpg', 'after_image' => 'assets/img/transformations/ac-after.jpg', 'sort_order' => 7],
            ['id' => 8, 'service_id' => 6, 'service_slug' => 'interior-painting', 'category_slug' => 'painting-services', 'service_name' => 'Interior Home Painting', 'category_name' => 'Painting Services', 'title' => 'Living Room Wall Seepage Repair & Royal Emulsion Painting', 'before_image' => 'assets/img/transformations/painting-before.jpg', 'after_image' => 'assets/img/transformations/painting-after.jpg', 'sort_order' => 8],
            ['id' => 9, 'service_id' => 7, 'service_slug' => 'cockroach-pest-control', 'category_slug' => 'pest-control', 'service_name' => 'Cockroach & Ant Pest Control', 'category_name' => 'Pest Control', 'title' => 'Kitchen Under-Counter Cockroach Nest Eradication', 'before_image' => 'assets/img/transformations/pest-before.jpg', 'after_image' => 'assets/img/transformations/pest-after.jpg', 'sort_order' => 9],
            ['id' => 10, 'service_id' => 8, 'service_slug' => 'tap-leak-repair', 'category_slug' => 'plumbers', 'service_name' => 'Tap & Pipe Leak Repair', 'category_name' => 'Plumbers', 'title' => 'Under-Sink Pipe Leak & Anti-Drip Drainage Repair', 'before_image' => 'assets/img/transformations/plumbing-before.jpg', 'after_image' => 'assets/img/transformations/plumbing-after.jpg', 'sort_order' => 10],
            ['id' => 11, 'service_id' => 9, 'service_slug' => 'furniture-assembly', 'category_slug' => 'carpenter', 'service_name' => 'Furniture Assembly & Wood Repair', 'category_name' => 'Carpenter', 'title' => 'Flatpack Wardrobe Assembly & Soft-Close Hinge Alignment', 'before_image' => 'assets/img/transformations/carpenter-before.jpg', 'after_image' => 'assets/img/transformations/carpenter-after.jpg', 'sort_order' => 11],
            ['id' => 12, 'service_id' => 11, 'service_slug' => 'fan-switchboard-repair', 'category_slug' => 'electrician', 'service_name' => 'Fan & Switchboard Repair', 'category_name' => 'Electrician', 'title' => 'MCB Distribution Board & Modular Switchboard Overhaul', 'before_image' => 'assets/img/transformations/electrical-before.jpg', 'after_image' => 'assets/img/transformations/electrical-after.jpg', 'sort_order' => 12],
            ['id' => 13, 'service_id' => 12, 'service_slug' => 'appliance-repair-service', 'category_slug' => 'appliance-repair', 'service_name' => 'Home Appliance Diagnostic & Repair', 'category_name' => 'Appliance Repair', 'title' => 'Refrigerator & Washing Machine Component Repair', 'before_image' => 'assets/img/transformations/appliance-before.jpg', 'after_image' => 'assets/img/transformations/appliance-after.jpg', 'sort_order' => 13],
            ['id' => 14, 'service_id' => 13, 'service_slug' => 'fall-ceiling-installation', 'category_slug' => 'fall-ceiling-services', 'service_name' => 'Designer Fall Ceiling & POP Installation', 'category_name' => 'Fall Ceiling', 'title' => 'Designer POP Fall Ceiling & Concealed LED Cove Installation', 'before_image' => 'assets/img/transformations/ceiling-before.jpg', 'after_image' => 'assets/img/transformations/ceiling-after.jpg', 'sort_order' => 14],
            ['id' => 15, 'service_id' => 14, 'service_slug' => 'fall-ceiling-repair-modification', 'category_slug' => 'fall-ceiling-services', 'service_name' => 'Fall Ceiling Repair & Cove Light Modification', 'category_name' => 'Fall Ceiling', 'title' => 'Gypsum Ceiling Crack Restoration & Leveling', 'before_image' => 'assets/img/transformations/ceilingrepair-before.jpg', 'after_image' => 'assets/img/transformations/ceilingrepair-after.jpg', 'sort_order' => 15],
        ];
    }

    protected function enrichGalleryItems(array $dbItems, array $services, array $categories): array
    {
        $existingSlugs = [];
        $enriched = [];

        foreach ($dbItems as $item) {
            $sSlug = $item['service_slug'] ?? '';
            if (!empty($sSlug)) {
                $existingSlugs[$sSlug] = true;
            }
            $enriched[] = $item;
        }

        $masters = $this->getMasterShowcases();
        foreach ($masters as $m) {
            if (!isset($existingSlugs[$m['service_slug']])) {
                $enriched[] = $m;
            }
        }

        return $enriched;
    }

    public function contact(Request $request): Response
    {
        $cities = ServiceArea::getActiveCities();
        return $this->render('customer.contact', [
            'title'       => 'Contact Us | REFIXEL HOME SERVICES',
            'description' => 'Get in touch with REFIXEL Home Services for trusted home maintenance, repair, and cleaning. Call +91 94581 82006 or visit our office.',
            'cities'      => $cities
        ], 'customer');
    }

    public function submitContact(Request $request): Response
    {
        $name    = trim((string)$request->post('name', ''));
        $email   = trim((string)$request->post('email', ''));
        $message = trim((string)$request->post('message', ''));

        if (empty($name) || empty($email) || empty($message)) {
            $_SESSION['flash_error'] = 'Please fill out your name, email address, and message.';
            return $this->redirect('/contact');
        }

        \App\Core\Logger::info("Contact inquiry received from {$name} ({$email}): {$message}");
        $_SESSION['flash_success'] = "Thank you, {$name}! Your message has been received. Our team will get back to you shortly.";
        return $this->redirect('/contact');
    }

    public function terms(Request $request): Response
    {
        return $this->render('customer.terms', ['title' => 'Terms & Conditions | REFIXEL'], 'customer');
    }

    public function privacy(Request $request): Response
    {
        return $this->render('customer.privacy', ['title' => 'Privacy Policy | REFIXEL'], 'customer');
    }

    public function refund(Request $request): Response
    {
        return $this->render('customer.refund', ['title' => 'Refund Policy | REFIXEL'], 'customer');
    }

    public function blog(Request $request): Response
    {
        return $this->render('customer.blog', ['title' => 'Blog & Home Guides | REFIXEL'], 'customer');
    }

    public function partner(Request $request): Response
    {
        $cities = \App\Models\ServiceArea::getActiveCities();
        $categories = \App\Models\Category::getActive();
        
        return $this->render('customer.partner', [
            'title' => 'Become a Service Partner | REFIXEL',
            'cities' => $cities,
            'categories' => $categories
        ], 'customer');
    }

    public function submitPartner(Request $request): Response
    {
        $name = $request->post('name');
        $phone = $request->post('phone');
        $trade = $request->post('trade');
        
        // Normally, save this to DB or send email.
        $_SESSION['flash_success'] = "Thank you, $name! Your application for $trade has been received. Our team will contact you shortly.";
        return $this->redirect('/partner');
    }

    public function sitemap(Request $request): Response
    {
        $baseUrl = rtrim((string)\App\Core\Env::get('APP_URL', 'https://www.REFIXEL.com'), '/');
        $today = date('Y-m-d');

        $urls = [
            ['loc' => $baseUrl . '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => $baseUrl . '/about', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => $baseUrl . '/faq', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => $baseUrl . '/gallery', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => $baseUrl . '/contact', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => $baseUrl . '/services', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => $baseUrl . '/terms', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => $baseUrl . '/privacy', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => $baseUrl . '/refund', 'priority' => '0.5', 'changefreq' => 'monthly'],
        ];

        // Active Cities
        $cities = ServiceArea::getActiveCities();
        $categories = \App\Models\Category::all('sort_order ASC');
        $services = \App\Models\Service::all('name ASC');

        foreach ($cities as $c) {
            $citySlug = strtolower(trim((string)$c['city']));
            if (empty($citySlug)) continue;

            // Categories in City
            foreach ($categories as $cat) {
                if (empty($cat['is_active'])) continue;
                $catSlug = $cat['slug'] ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $cat['name']), '-'));
                $urls[] = [
                    'loc'        => "{$baseUrl}/{$catSlug}-services-in-{$citySlug}",
                    'priority'   => '0.9',
                    'changefreq' => 'weekly',
                ];
            }

            // Services in City
            foreach ($services as $svc) {
                if (empty($svc['is_active'])) continue;
                $svcSlug = $svc['slug'] ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $svc['name']), '-'));
                $urls[] = [
                    'loc'        => "{$baseUrl}/{$svcSlug}-services-in-{$citySlug}",
                    'priority'   => '0.9',
                    'changefreq' => 'weekly',
                ];
            }
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
            $xml .= "    <lastmod>{$today}</lastmod>\n";
            $xml .= "    <changefreq>{$u['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$u['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= "</urlset>";

        return Response::xml($xml);
    }

    public function robots(Request $request): Response
    {
        $baseUrl = rtrim((string)\App\Core\Env::get('APP_URL', 'https://www.REFIXEL.com'), '/');

        $content = "User-agent: *\n";
        $content .= "Disallow: /admin\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /staff\n";
        $content .= "Disallow: /staff/\n";
        $content .= "Disallow: /account\n";
        $content .= "Disallow: /account/\n";
        $content .= "Disallow: /api/\n";
        $content .= "Allow: /\n\n";
        $content .= "Sitemap: {$baseUrl}/sitemap.xml\n";

        return Response::plain($content);
    }
}


