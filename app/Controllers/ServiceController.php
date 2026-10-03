<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceArea;
use App\Models\ServiceChecklistItem;

class ServiceController extends Controller
{
    public function index(Request $request): Response
    {
        $categories = Category::getActive();
        $allServices = Service::getActive();
        $cities = ServiceArea::getActiveCities();

        return $this->render('customer.services.index', [
            'title'       => 'All Home & Commercial Services | Primodomus',
            'description' => 'Explore professional home cleaning, painting, pest control, plumbing, carpentry, and AC services across Indian metro cities.',
            'categories'  => $categories,
            'services'    => $allServices,
            'cities'      => $cities,
        ], 'customer');
    }

    public function categoryInCity(Request $request, string $category, string $city): Response
    {
        $cityName = $this->formatCityName($city);

        // 1. Try resolving as a Category
        $cat = $this->resolveCategory($category);
        if ($cat) {
            return $this->renderCategoryPage($cat, $cityName, $city);
        }

        // 2. Fallback: check if the slug is actually a Service (e.g. /full-home-cleaning-services-in-kashipur)
        $svc = $this->resolveService($category);
        if ($svc) {
            return $this->renderServicePage($svc, $cityName, $city);
        }

        return $this->render('partials.404', ['title' => 'Service or Category Not Found'], 'customer')
            ->setStatusCode(404);
    }

    public function serviceInCity(Request $request, string $service, string $city): Response
    {
        $cityName = $this->formatCityName($city);

        // 1. Try resolving as a Service
        $svc = $this->resolveService($service);
        if ($svc) {
            return $this->renderServicePage($svc, $cityName, $city);
        }

        // 2. Fallback: check if slug is actually a Category
        $cat = $this->resolveCategory($service);
        if ($cat) {
            return $this->renderCategoryPage($cat, $cityName, $city);
        }

        return $this->render('partials.404', ['title' => 'Service Not Found'], 'customer')
            ->setStatusCode(404);
    }

    protected function renderCategoryPage(array $cat, string $cityName, string $rawCity): Response
    {
        $services = Service::getActiveByCategory((int)$cat['id']);
        $relatedCategories = Category::getRelated((int)$cat['id'], 4);
        $serviceAreas = ServiceArea::getByCity($cityName);
        $faqs = Faq::getGlobal();
        $cleanCitySlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $rawCity), '-'));

        $title = "Professional {$cat['name']} Services in {$cityName} | Primodomus";
        $description = "Looking for verified {$cat['name']} in {$cityName}? Background-verified technicians, standardized checklists, transparent pricing, and 24-hour guarantee. Book online!";
        $canonicalUrl = View::url("/{$cat['slug']}-services-in-{$cleanCitySlug}");

        // Build Schema.org Structured Data
        $schemaData = [
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'       => 'LocalBusiness',
                    'name'        => "Primodomus {$cat['name']} Services in {$cityName}",
                    'description' => $description,
                    'url'         => $canonicalUrl,
                    'areaServed'  => [
                        '@type'   => 'City',
                        'name'    => $cityName,
                    ],
                    'priceRange'  => '₹₹',
                    'telephone'   => '+919953358855',
                ],
                [
                    '@type'           => 'BreadcrumbList',
                    'itemListElement' => [
                        [
                            '@type'    => 'ListItem',
                            'position' => 1,
                            'name'     => 'Home',
                            'item'     => View::url('/'),
                        ],
                        [
                            '@type'    => 'ListItem',
                            'position' => 2,
                            'name'     => 'Services',
                            'item'     => View::url('/services'),
                        ],
                        [
                            '@type'    => 'ListItem',
                            'position' => 3,
                            'name'     => "{$cat['name']} in {$cityName}",
                            'item'     => $canonicalUrl,
                        ],
                    ],
                ],
            ],
        ];

        return $this->render('customer.services.category', [
            'title'             => $title,
            'description'       => $description,
            'canonicalUrl'      => $canonicalUrl,
            'schemaData'        => $schemaData,
            'category'          => $cat,
            'services'          => $services,
            'city'              => $cityName,
            'citySlug'          => $cleanCitySlug,
            'relatedCategories' => $relatedCategories,
            'serviceAreas'      => $serviceAreas,
            'faqs'              => $faqs,
        ], 'customer');
    }

    protected function renderServicePage(array $svc, string $cityName, string $rawCity): Response
    {
        $checklist = ServiceChecklistItem::getByService((int)$svc['id']);
        $faqs = Faq::getByService((int)$svc['id']);
        if (empty($faqs)) {
            $faqs = Faq::getGlobal();
        }
        $reviews = Review::getByService((int)$svc['id'], 6);
        $relatedServices = Service::getActiveByCategory((int)$svc['category_id']);
        $serviceAreas = ServiceArea::getByCity($cityName);
        $cleanCitySlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $rawCity), '-'));

        $price = number_format((float)$svc['starting_price'], 0);
        $title = "{$svc['name']} in {$cityName} | Primodomus";
        $description = "Book {$svc['name']} in {$cityName} starting at ₹{$price}. Industrial tools, vetted professionals, and 24-hour satisfaction guarantee.";
        $canonicalUrl = View::url("/{$svc['slug']}-in-{$cleanCitySlug}");

        // Build Schema.org Structured Data
        $schemaData = [
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'        => 'Service',
                    'name'         => "{$svc['name']} in {$cityName}",
                    'description'  => $description,
                    'url'          => $canonicalUrl,
                    'provider'     => [
                        '@type' => 'Organization',
                        'name'  => 'Primodomus',
                        'url'   => View::url('/'),
                    ],
                    'areaServed'   => [
                        '@type' => 'City',
                        'name'  => $cityName,
                    ],
                    'offers'       => [
                        '@type'         => 'Offer',
                        'price'         => (string)$svc['starting_price'],
                        'priceCurrency' => 'INR',
                        'availability'  => 'https://schema.org/InStock',
                    ],
                    'aggregateRating' => [
                        '@type'       => 'AggregateRating',
                        'ratingValue' => '4.85',
                        'reviewCount' => '128',
                    ],
                ],
                [
                    '@type'           => 'BreadcrumbList',
                    'itemListElement' => [
                        [
                            '@type'    => 'ListItem',
                            'position' => 1,
                            'name'     => 'Home',
                            'item'     => View::url('/'),
                        ],
                        [
                            '@type'    => 'ListItem',
                            'position' => 2,
                            'name'     => 'Services',
                            'item'     => View::url('/services'),
                        ],
                        [
                            '@type'    => 'ListItem',
                            'position' => 3,
                            'name'     => "{$svc['name']} in {$cityName}",
                            'item'     => $canonicalUrl,
                        ],
                    ],
                ],
            ],
        ];

        return $this->render('customer.services.show', [
            'title'           => $title,
            'description'     => $description,
            'canonicalUrl'    => $canonicalUrl,
            'schemaData'      => $schemaData,
            'service'         => $svc,
            'city'            => $cityName,
            'citySlug'        => $cleanCitySlug,
            'checklist'       => $checklist,
            'faqs'            => $faqs,
            'reviews'         => $reviews,
            'relatedServices' => $relatedServices,
            'serviceAreas'    => $serviceAreas,
        ], 'customer');
    }

    protected function formatCityName(string $city): string
    {
        $clean = str_replace('-', ' ', $city);
        return ucwords(strtolower($clean));
    }

    protected function resolveCategory(string $slug): ?array
    {
        $slug = strtolower(trim($slug));

        // 1. Direct match
        $cat = Category::findBySlug($slug);
        if ($cat) return $cat;

        // 2. Plural/singular variants (e.g. plumber -> plumbers)
        $cat = Category::findBySlug($slug . 's');
        if ($cat) return $cat;

        if (str_ends_with($slug, 's')) {
            $cat = Category::findBySlug(rtrim($slug, 's'));
            if ($cat) return $cat;
        }

        // 3. Strip trailing -services or -service (e.g. ac-services -> ac)
        $stripped = preg_replace('/-(services|service)$/', '', $slug);
        if ($stripped !== $slug) {
            $cat = Category::findBySlug($stripped);
            if ($cat) return $cat;
            $cat = Category::findBySlug($stripped . 's');
            if ($cat) return $cat;
        }

        // 4. Try adding -services
        $cat = Category::findBySlug($slug . '-services');
        if ($cat) return $cat;

        return null;
    }

    protected function resolveService(string $slug): ?array
    {
        $slug = strtolower(trim($slug));

        // 1. Direct match
        $svc = Service::findBySlug($slug);
        if ($svc) return $svc;

        // 2. Strip trailing -services or -service (e.g. full-home-cleaning-services -> full-home-cleaning)
        $stripped = preg_replace('/-(services|service)$/', '', $slug);
        if ($stripped !== $slug) {
            $svc = Service::findBySlug($stripped);
            if ($svc) return $svc;
        }

        // 3. Try adding -service
        $svc = Service::findBySlug($slug . '-service');
        if ($svc) return $svc;

        return null;
    }
}
