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
        $items = GalleryItem::getActive();
        return $this->render('customer.gallery', ['title' => 'Work Showcase | REFIXEL', 'items' => $items], 'customer');
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


