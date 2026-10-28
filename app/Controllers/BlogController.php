<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Blog;
use App\Models\ServiceArea;

class BlogController extends Controller
{
    public function index(Request $request): Response
    {
        $allPosts = Blog::all();
        $selectedCat = strtolower(trim((string)($request->query('category') ?? $request->query('cat') ?? 'all')));
        $selectedCity = strtolower(trim((string)($request->query('city') ?? 'all')));
        $searchQuery = trim((string)($request->query('q') ?? $request->query('search') ?? ''));

        $filteredPosts = $allPosts;

        // Filter by Category
        if ($selectedCat !== 'all' && !empty($selectedCat)) {
            $filteredPosts = array_filter($filteredPosts, function ($p) use ($selectedCat) {
                if ($selectedCat === 'case-study' || $selectedCat === 'case-studies') {
                    return !empty($p['is_case_study']);
                }
                return (strtolower($p['category']) === $selectedCat || strtolower($p['service_cat'] ?? '') === $selectedCat);
            });
        }

        // Filter by City
        if ($selectedCity !== 'all' && !empty($selectedCity)) {
            $filteredPosts = array_filter($filteredPosts, function ($p) use ($selectedCity) {
                return str_contains(strtolower($p['city']), $selectedCity);
            });
        }

        // Filter by Search Query
        if (!empty($searchQuery)) {
            $q = strtolower($searchQuery);
            $filteredPosts = array_filter($filteredPosts, function ($p) use ($q) {
                return str_contains(strtolower($p['title']), $q)
                    || str_contains(strtolower($p['excerpt']), $q)
                    || str_contains(strtolower($p['city']), $q)
                    || str_contains(strtolower($p['category']), $q);
            });
        }

        $filteredPosts = array_values($filteredPosts);
        $caseStudies = Blog::getCaseStudies(4);
        $activeCities = ServiceArea::getActiveCities();

        $title = 'Home Maintenance Blog & Case Studies | REFIXEL - Kashipur, Jaspur, Thakurdwara';
        if ($selectedCity !== 'all' && !empty($selectedCity)) {
            $cityName = ucfirst($selectedCity);
            $title = "Home Services & Maintenance Blog in {$cityName} | REFIXEL Expert Guides";
        }

        $description = 'Explore expert guides, troubleshooting checklists, and real local case studies on home deep cleaning, AC repair, plumbing, electrical rewiring, and false ceiling in Kashipur, Jaspur & Thakurdwara.';
        $keywords = 'refixel blog, home maintenance tips, AC servicing kashipur, deep cleaning case study jaspur, electrician guide thakurdwara, plumbing tips, false ceiling ideas';
        $canonicalUrl = View::fullUrl('/blogs');

        return $this->render('customer.blogs.index', [
            'title'         => $title,
            'description'   => $description,
            'keywords'      => $keywords,
            'canonicalUrl'  => $canonicalUrl,
            'ogTitle'       => $title,
            'ogDescription' => $description,
            'ogUrl'         => $canonicalUrl,
            'posts'         => $filteredPosts,
            'allPostsCount' => count($allPosts),
            'caseStudies'   => $caseStudies,
            'selectedCat'   => $selectedCat,
            'selectedCity'  => $selectedCity,
            'searchQuery'   => $searchQuery,
            'cities'        => $activeCities,
        ], 'customer');
    }

    public function show(Request $request, string $slug): Response
    {
        $post = Blog::findBySlug($slug);
        if (!$post) {
            return $this->render('partials.404', [
                'title' => 'Blog Article Not Found | REFIXEL'
            ], 'customer')->setStatusCode(404);
        }

        $relatedPosts = Blog::getRelated($post['slug'], 3);
        $canonicalUrl = View::fullUrl("/blogs/{$post['slug']}");
        $postImage = View::fullUrl('assets/img/' . $post['image']);

        // Structured Data (BlogPosting Schema & BreadcrumbList)
        $schemaGraph = [
            [
                '@type'            => 'BlogPosting',
                'headline'         => $post['title'],
                'description'      => $post['excerpt'],
                'image'            => $postImage,
                'author'           => [
                    '@type' => 'Person',
                    'name'  => $post['author'],
                ],
                'publisher'        => [
                    '@type' => 'Organization',
                    'name'  => 'REFIXEL Home Services',
                    'logo'  => [
                        '@type' => 'ImageObject',
                        'url'   => View::fullUrl('assets/img/refixel-logo-horizontal.png'),
                    ],
                ],
                'datePublished'    => $post['published_at'] . 'T09:00:00+05:30',
                'dateModified'     => $post['published_at'] . 'T18:00:00+05:30',
                'mainEntityOfPage' => $canonicalUrl,
            ],
            [
                '@type'           => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => View::url('/')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blogs', 'item' => View::url('/blogs')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $post['title'], 'item' => $canonicalUrl],
                ],
            ],
        ];

        $schemaData = [
            '@context' => 'https://schema.org',
            '@graph'   => $schemaGraph,
        ];

        return $this->render('customer.blogs.show', [
            'title'         => "{$post['title']} | REFIXEL",
            'description'   => $post['excerpt'],
            'keywords'      => "{$post['title']}, {$post['category']}, {$post['city']} home services, refixel blog",
            'canonicalUrl'  => $canonicalUrl,
            'ogTitle'       => $post['title'],
            'ogDescription' => $post['excerpt'],
            'ogUrl'         => $canonicalUrl,
            'ogImage'       => $postImage,
            'schemaData'    => $schemaData,
            'post'          => $post,
            'relatedPosts'  => $relatedPosts,
        ], 'customer');
    }
}
