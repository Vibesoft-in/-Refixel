<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceArea;
use App\Models\ServiceChecklistItem;
use App\Models\ServiceAddon;
use App\Models\ServiceVariant;

class ServiceController extends Controller
{
    public function index(Request $request): Response
    {
        $categories = Category::getActive();
        $allServices = Service::getActive();
        $cities = ServiceArea::getActiveCities();
        $activeCat = (string)($request->query('category') ?? $request->query('cat') ?? 'all');
        $searchQuery = trim((string)($request->query('q') ?? $request->query('search') ?? ''));

        if (!empty($searchQuery)) {
            $matchedServices = Service::search($searchQuery, 30);
            if (!empty($matchedServices)) {
                $allServices = $matchedServices;
                $catIds = array_values(array_unique(array_column($matchedServices, 'category_id')));
                if (count($catIds) === 1) {
                    foreach ($categories as $cat) {
                        if ((int)$cat['id'] === (int)$catIds[0]) {
                            $activeCat = $cat['slug'];
                            break;
                        }
                    }
                }
            }
        }

        $title = !empty($searchQuery) ? 'Search: ' . htmlspecialchars($searchQuery) . ' | REFIXEL' : 'All Home Services in Kashipur, Jaspur & Thakurdwara | REFIXEL';
        $description = 'Explore 40+ verified doorstep home services in Kashipur, Jaspur & Thakurdwara. Deep cleaning, AC repair, plumbing, electrician, painting & false ceiling. Book online!';
        $keywords = 'home services directory, AC repair, home cleaning, electrician, plumber, painting, pest control, false ceiling, kashipur, jaspur, thakurdwara, refixel';
        $canonicalUrl = View::fullUrl('/services');

        return $this->render('customer.services.index', [
            'title'             => $title,
            'description'       => $description,
            'keywords'          => $keywords,
            'canonicalUrl'      => $canonicalUrl,
            'ogTitle'           => $title,
            'ogDescription'     => $description,
            'ogUrl'             => $canonicalUrl,
            'categories'        => $categories,
            'services'          => $allServices,
            'cities'            => $cities,
            'activeCategory'    => $activeCat,
            'searchQuery'       => $searchQuery,
            'variantsByService' => ServiceVariant::getAllGroupedByServiceId(),
        ], 'customer');
    }

    public function apiSearch(Request $request): Response
    {
        $q = trim((string)$request->query('q', ''));
        if (strlen($q) < 2) {
            return Response::json(['services' => []]);
        }

        $results = Service::search($q, 15);
        $mapped = [];
        foreach ($results as $row) {
            $mapped[] = [
                'id'             => (int)$row['id'],
                'name'           => $row['name'],
                'slug'           => $row['slug'],
                'starting_price' => number_format((float)($row['starting_price'] ?? 0), 0),
                'category_name'  => $row['category_name'] ?? 'Home Service',
                'category_slug'  => $row['category_slug'] ?? 'cleaning',
                'image'          => $row['image'] ?? 'refixel-cleaning.jpg',
            ];
        }

        return Response::json(['services' => $mapped]);
    }

    public function categoryClean(Request $request, string $category): Response
    {
        // 1. Try resolving as Category
        $cat = $this->resolveCategory($category);
        if ($cat) {
            return $this->renderCategoryPage($cat, '', '');
        }

        // 2. Fallback: check if slug is actually a Service
        $svc = $this->resolveService($category);
        if ($svc) {
            return $this->renderServicePage($svc, '', '');
        }

        return $this->render('partials.404', ['title' => 'Category Not Found'], 'customer')
            ->setStatusCode(404);
    }

    public function serviceClean(Request $request, string $service): Response
    {
        // 1. Try resolving as Service first so specific service pages are displayed
        $svc = $this->resolveService($service);
        if ($svc) {
            return $this->renderServicePage($svc, '', '');
        }

        // 2. Try resolving as Category
        $cat = $this->resolveCategory($service);
        if ($cat) {
            return $this->renderCategoryPage($cat, '', '');
        }

        return $this->render('partials.404', ['title' => 'Page Not Found'], 'customer')
            ->setStatusCode(404);
    }

    public function categoryInCity(Request $request, string $category, string $city): Response
    {
        if (!ServiceArea::isValidCity($city)) {
            $defaultCitySlug = strtolower(ServiceArea::DEFAULT_CITY);
            return $this->redirect(View::url("/{$category}-services-in-{$defaultCitySlug}"), 301);
        }

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
        if (!ServiceArea::isValidCity($city)) {
            $defaultCitySlug = strtolower(ServiceArea::DEFAULT_CITY);
            return $this->redirect(View::url("/{$service}-in-{$defaultCitySlug}"), 301);
        }

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
        $categories = Category::getActive();
        $allServices = Service::getActive();
        $cities = ServiceArea::getActiveCities();
        $relatedCategories = Category::getRelated((int)$cat['id'], 4);
        $faqs = Faq::getGlobal();
        
        $catSlug = $cat['slug'] ?? '';
        if ($catSlug === 'plumbers') {
            $catSlug = 'plumber';
        }
        $cleanCatSlug = str_ends_with($catSlug, '-services') ? $catSlug : "{$catSlug}-services";
        $cleanCitySlug = !empty($rawCity) ? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $rawCity), '-')) : '';

        $cityPincodeMap = [
            'kashipur'    => ['pincode' => '244713', 'state' => 'Uttarakhand'],
            'jaspur'      => ['pincode' => '244712', 'state' => 'Uttarakhand'],
            'thakurdwara' => ['pincode' => '244601', 'state' => 'Uttar Pradesh'],
        ];

        $hasCity = !empty($cleanCitySlug) && !empty($cityName);
        $displayCity = $hasCity ? $cityName : 'Kashipur, Jaspur & Thakurdwara';
        $serviceAreas = $hasCity ? ServiceArea::getByCity($cityName) : ServiceArea::getActiveCities();

        if ($hasCity) {
            $canonicalUrl = View::fullUrl("/{$cleanCatSlug}-in-{$cleanCitySlug}");
            $title = "Best {$cat['name']} in {$cityName} | Verified Doorstep Pros - REFIXEL";
            $description = "Looking for verified {$cat['name']} in {$cityName}? Book certified local professionals in {$cityName} for 100% safe, on-time doorstep service with 30-day warranty. Upfront pricing!";
            $keywords = "{$cat['name']} in {$cityName}, {$cat['name']} services {$cityName}, best {$cat['name']} {$cityName}, doorstep {$cat['name']} {$cityName}, {$cat['name']} near me {$cityName}, home services {$cityName}, refixel {$cityName}";
            $areaServedSchema = [
                '@type'          => 'City',
                'name'           => $cityName,
                'postalCode'     => $cityPincodeMap[$cleanCitySlug]['pincode'] ?? '244713',
                'addressRegion'  => $cityPincodeMap[$cleanCitySlug]['state'] ?? 'Uttarakhand',
                'addressCountry' => 'IN',
            ];
            $breadcrumbCrumbs = [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => View::url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => View::url('/services')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => "{$cat['name']} in {$cityName}", 'item' => $canonicalUrl],
            ];
        } else {
            $canonicalUrl = View::fullUrl("/{$cleanCatSlug}");
            $title = "Best {$cat['name']} | Doorstep Services in Kashipur, Jaspur & Thakurdwara - REFIXEL";
            $description = "Explore top-rated {$cat['name']} with REFIXEL. Standardized checklists, background-verified technicians, upfront rates & 30-day guarantee in Kashipur, Jaspur & Thakurdwara.";
            $keywords = "{$cat['name']}, {$cat['name']} services, {$cat['name']} kashipur, {$cat['name']} jaspur, {$cat['name']} thakurdwara, home repair, refixel";
            $areaServedSchema = [
                ['@type' => 'City', 'name' => 'Kashipur', 'postalCode' => '244713'],
                ['@type' => 'City', 'name' => 'Jaspur', 'postalCode' => '244712'],
                ['@type' => 'City', 'name' => 'Thakurdwara', 'postalCode' => '244601'],
            ];
            $breadcrumbCrumbs = [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => View::url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => View::url('/services')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $cat['name'], 'item' => $canonicalUrl],
            ];
        }

        // Build Schema.org Structured Data
        $schemaGraph = [
            [
                '@type'       => 'LocalBusiness',
                'name'        => "REFIXEL {$cat['name']} Services - {$displayCity}",
                'description' => $description,
                'url'         => $canonicalUrl,
                'areaServed'  => $areaServedSchema,
                'priceRange'  => '₹₹',
                'telephone'   => '+91 87911 54730',
                'image'       => View::fullUrl('assets/img/refixel-logo-horizontal.png'),
            ],
            [
                '@type'           => 'BreadcrumbList',
                'itemListElement' => $breadcrumbCrumbs,
            ],
        ];

        if (!empty($faqs)) {
            $faqEntities = [];
            foreach (array_slice($faqs, 0, 5) as $f) {
                if (empty($f['question']) || empty($f['answer'])) continue;
                $faqEntities[] = [
                    '@type'          => 'Question',
                    'name'           => $f['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => strip_tags((string)$f['answer']),
                    ],
                ];
            }
            if (!empty($faqEntities)) {
                $schemaGraph[] = [
                    '@type'      => 'FAQPage',
                    'mainEntity' => $faqEntities,
                ];
            }
        }

        $schemaData = [
            '@context' => 'https://schema.org',
            '@graph'   => $schemaGraph,
        ];

        return $this->render('customer.services.index', [
            'title'             => $title,
            'description'       => $description,
            'keywords'          => $keywords,
            'canonicalUrl'      => $canonicalUrl,
            'ogTitle'           => $title,
            'ogDescription'     => $description,
            'ogUrl'             => $canonicalUrl,
            'schemaData'        => $schemaData,
            'category'          => $cat,
            'categories'        => $categories,
            'services'          => $allServices,
            'activeCategory'    => $cat['slug'],
            'city'              => $displayCity,
            'currentCity'       => $displayCity,
            'citySlug'          => $cleanCitySlug,
            'cities'            => $cities,
            'relatedCategories' => $relatedCategories,
            'variantsByService' => ServiceVariant::getAllGroupedByServiceId(),
            'serviceAreas'      => $serviceAreas,
            'faqs'              => $faqs,
        ], 'customer');
    }

    protected function renderServicePage(array $svc, string $cityName, string $rawCity): Response
    {
        // Publicly accessible for guest users and search engine indexing (Googlebot)
        $checklist = ServiceChecklistItem::getByService((int)$svc['id']);
        $faqs = Faq::getByService((int)$svc['id']);
        if (empty($faqs)) {
            $faqs = Faq::getGlobal();
        }
        $reviews = Review::getByService((int)$svc['id'], 6);
        $relatedServices = Service::getActiveByCategory((int)$svc['category_id']);
        $cleanCitySlug = !empty($rawCity) ? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $rawCity), '-')) : '';
        $hasCity = !empty($cleanCitySlug) && !empty($cityName);
        $displayCity = $hasCity ? $cityName : 'Kashipur, Jaspur & Thakurdwara';
        $serviceAreas = $hasCity ? ServiceArea::getByCity($cityName) : ServiceArea::getActiveCities();

        $cityPincodeMap = [
            'kashipur'    => ['pincode' => '244713', 'state' => 'Uttarakhand'],
            'jaspur'      => ['pincode' => '244712', 'state' => 'Uttarakhand'],
            'thakurdwara' => ['pincode' => '244601', 'state' => 'Uttar Pradesh'],
        ];

        $price = number_format((float)$svc['starting_price'], 0);
        $serviceImg = \App\Models\Service::resolveImage($svc['slug'] ?? '', $svc['image'] ?? null);
        $ogImage = View::fullUrl('assets/img/' . $serviceImg);

        if ($hasCity) {
            $canonicalUrl = View::fullUrl("/{$svc['slug']}-in-{$cleanCitySlug}");
            $title = "{$svc['name']} in {$cityName} | Starting ₹{$price} - REFIXEL";
            $description = "Book expert {$svc['name']} in {$cityName} starting at just ₹{$price}. Certified local technicians, on-time doorstep visit, genuine materials & 100% satisfaction guarantee.";
            $keywords = "{$svc['name']} in {$cityName}, {$svc['name']} {$cityName}, best {$svc['name']} {$cityName}, doorstep {$svc['name']} {$cityName}, {$svc['name']} near me {$cityName}, home maintenance {$cityName}, refixel {$cityName}";
            $areaServedSchema = [
                '@type'          => 'City',
                'name'           => $cityName,
                'postalCode'     => $cityPincodeMap[$cleanCitySlug]['pincode'] ?? '244713',
                'addressRegion'  => $cityPincodeMap[$cleanCitySlug]['state'] ?? 'Uttarakhand',
                'addressCountry' => 'IN',
            ];
            $breadcrumbCrumbs = [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => View::url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => View::url('/services')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => "{$svc['name']} in {$cityName}", 'item' => $canonicalUrl],
            ];
        } else {
            $canonicalUrl = View::fullUrl("/{$svc['slug']}");
            $title = "{$svc['name']} | Starting ₹{$price} - Book Online | REFIXEL";
            $description = "Book verified {$svc['name']} with REFIXEL starting @ ₹{$price}. Standardized pricing, genuine equipment, certified technicians & doorstep visit across Kashipur, Jaspur & Thakurdwara.";
            $keywords = "{$svc['name']}, {$svc['name']} kashipur, {$svc['name']} jaspur, {$svc['name']} thakurdwara, best home services uttarakhand, refixel";
            $areaServedSchema = [
                ['@type' => 'City', 'name' => 'Kashipur', 'postalCode' => '244713'],
                ['@type' => 'City', 'name' => 'Jaspur', 'postalCode' => '244712'],
                ['@type' => 'City', 'name' => 'Thakurdwara', 'postalCode' => '244601'],
            ];
            $breadcrumbCrumbs = [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => View::url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => View::url('/services')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $svc['name'], 'item' => $canonicalUrl],
            ];
        }

        // Build Schema.org Structured Data
        $schemaGraph = [
            [
                '@type'        => 'Service',
                'name'         => $hasCity ? "{$svc['name']} in {$cityName}" : $svc['name'],
                'description'  => $description,
                'url'          => $canonicalUrl,
                'image'        => $ogImage,
                'provider'     => [
                    '@type'      => 'LocalBusiness',
                    'name'       => 'REFIXEL',
                    'url'        => View::url('/'),
                    'telephone'  => '+91 87911 54730',
                    'priceRange' => '₹₹',
                ],
                'areaServed'   => $areaServedSchema,
                'offers'       => [
                    '@type'         => 'Offer',
                    'price'         => (string)$svc['starting_price'],
                    'priceCurrency' => 'INR',
                    'availability'  => 'https://schema.org/InStock',
                    'url'           => $canonicalUrl,
                ],
                'aggregateRating' => [
                    '@type'       => 'AggregateRating',
                    'ratingValue' => '4.88',
                    'reviewCount' => '142',
                ],
            ],
            [
                '@type'           => 'BreadcrumbList',
                'itemListElement' => $breadcrumbCrumbs,
            ],
        ];

        if (!empty($faqs)) {
            $faqEntities = [];
            foreach (array_slice($faqs, 0, 5) as $f) {
                if (empty($f['question']) || empty($f['answer'])) continue;
                $faqEntities[] = [
                    '@type'          => 'Question',
                    'name'           => $f['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => strip_tags((string)$f['answer']),
                    ],
                ];
            }
            if (!empty($faqEntities)) {
                $schemaGraph[] = [
                    '@type'      => 'FAQPage',
                    'mainEntity' => $faqEntities,
                ];
            }
        }

        $schemaData = [
            '@context' => 'https://schema.org',
            '@graph'   => $schemaGraph,
        ];

        $addons = ServiceAddon::forService((int)$svc['id'], [], 6);
        $variants = ServiceVariant::getGroupedByService((int)$svc['id']);

        return $this->render('customer.services.show', [
            'title'           => $title,
            'description'     => $description,
            'keywords'        => $keywords,
            'canonicalUrl'    => $canonicalUrl,
            'ogTitle'         => $title,
            'ogDescription'   => $description,
            'ogUrl'           => $canonicalUrl,
            'ogImage'         => $ogImage,
            'schemaData'      => $schemaData,
            'service'         => $svc,
            'city'            => $displayCity,
            'citySlug'        => $cleanCitySlug,
            'checklist'       => $checklist,
            'faqs'            => $faqs,
            'reviews'         => $reviews,
            'relatedServices' => $relatedServices,
            'serviceAreas'    => $serviceAreas,
            'addons'          => $addons,
            'variants'        => $variants,
        ], 'customer');
    }

    protected function formatCityName(string $city): string
    {
        return ServiceArea::normalizeCity($city);
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

        // 5. Aliases
        if (in_array($slug, ['fall-ceiling', 'fall-ceiling-services', 'false-ceiling', 'false-ceiling-services', 'pop-ceiling', 'pop-false-ceiling', 'ceiling', 'masonry', 'masonry-services', 'masonry-construction', 'civil-work'], true)) {
            $cat = Category::findBySlug('fall-ceiling-services') ?? Category::findBySlug('masonry-services');
            if ($cat) return $cat;
        }
        if ($slug === 'electrician' || $slug === 'electricians') {
            $cat = Category::findBySlug('electrician') ?? Category::findBySlug('appliance-repair');
            if ($cat) return $cat;
        }
        if ($slug === 'appliance-repair' || $slug === 'appliance' || $slug === 'appliances') {
            $cat = Category::findBySlug('appliance-repair') ?? Category::findBySlug('electrician');
            if ($cat) return $cat;
        }

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
            $svc = Service::findBySlug('professional-' . $stripped);
            if ($svc) return $svc;
        }

        // 3. Try adding -service or professional- prefix
        $svc = Service::findBySlug($slug . '-service');
        if ($svc) return $svc;

        $svc = Service::findBySlug('professional-' . $slug);
        if ($svc) return $svc;

        if (in_array($slug, ['sofa-cleaning', 'sofa-upholstery-cleaning', 'sofa-deep-cleaning', 'sofa-cleaning-service'], true)) {
            $svc = Service::findBySlug('sofa-upholstery-deep-cleaning');
            if ($svc) return $svc;
        }
        if (in_array($slug, ['bathroom-cleaning', 'bathroom-deep-cleaning', 'toilet-cleaning'], true)) {
            $svc = Service::findBySlug('professional-bathroom-cleaning');
            if ($svc) return $svc;
        }
        if (in_array($slug, ['home-cleaning', 'full-home-cleaning', 'house-cleaning', 'deep-home-cleaning'], true)) {
            $svc = Service::findBySlug('professional-full-home-cleaning');
            if ($svc) return $svc;
        }
        if (in_array($slug, ['ac-service', 'ac-jet-service', 'ac-foam-wash', 'split-ac-service'], true)) {
            $svc = Service::findBySlug('ac-antibacterial-foam-wash') ?? Service::findBySlug('ac-jet-service');
            if ($svc) return $svc;
        }

        if (in_array($slug, ['brick-masonry-repair', 'brickwork-wall-masonry-repair', 'fall-ceiling', 'false-ceiling', 'fall-ceiling-service'], true)) {
            $svc = Service::findBySlug('fall-ceiling-installation');
            if ($svc) return $svc;
        }
        if (in_array($slug, ['tank-cleaning', 'water-tank', 'water-tank-clean', 'tank-cleaning-services', 'water-tank-services', 'tank-clean', 'tank-services', 'tank', 'water-tank-cleaning-services', 'water-tank-cleaning-service'], true)) {
            $svc = Service::findBySlug('water-tank-cleaning');
            if ($svc) return $svc;
        }

        return null;
    }
}

