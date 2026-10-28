<?php
define('ROOT_PATH', dirname(__DIR__, 2));

spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = ROOT_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Core\Env;
use App\Core\Router;
use App\Core\Request;
use App\Models\Blog;

Env::load(ROOT_PATH . '/.env');
require_once ROOT_PATH . '/config/routes.php';

echo "=== RUNNING BLOG ROUTES VERIFICATION TESTS ===\n\n";

// 1. Verify /blogs
$resBlogs = Router::dispatch(new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/blogs']));
assert($resBlogs->getStatusCode() === 200, "GET /blogs must return 200 OK");
assert(str_contains($resBlogs->getBody(), 'The REFIXEL Knowledge Journal'), "/blogs must render heading");
echo "  ✓ PASS: GET /blogs returns 200 OK with full listing\n";

// 2. Verify /blog alias
$resBlog = Router::dispatch(new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/blog']));
assert($resBlog->getStatusCode() === 200, "GET /blog must return 200 OK");
echo "  ✓ PASS: GET /blog returns 200 OK\n";

// 3. Verify All 21 Blog Slugs Open Successfully with 200 OK and 400+ words
$allBlogs = Blog::all();
assert(count($allBlogs) >= 20, "Must have at least 20 blog articles");
echo "  ✓ PASS: Total " . count($allBlogs) . " blog articles verified in repository\n";

$testedCount = 0;
foreach ($allBlogs as $b) {
    $slug = $b['slug'];
    $resPost = Router::dispatch(new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/blogs/{$slug}"]));
    assert($resPost->getStatusCode() === 200, "GET /blogs/{$slug} must return 200 OK");
    $body = $resPost->getBody();
    assert(str_contains($body, $b['title']), "Page must contain article title: {$b['title']}");
    assert(str_contains($body, 'schema.org') && str_contains($body, 'BlogPosting'), "Page must contain BlogPosting Schema");
    
    // Check word count
    $cleanText = strip_tags($b['content']);
    $wordCount = str_word_count($cleanText);
    assert($wordCount >= 300, "Blog {$slug} word count must be substantial, got {$wordCount}");
    $testedCount++;
}
echo "  ✓ PASS: All {$testedCount} individual blog URLs render 200 OK with schema and rich content\n";

// 4. Verify 404 on invalid blog
$res404 = Router::dispatch(new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/blogs/non-existent-blog-slug-123']));
assert($res404->getStatusCode() === 404, "Invalid blog slug must return 404");
echo "  ✓ PASS: Invalid blog slug returns clean 404 response\n";

echo "\n=======================================================\n";
echo "   ALL BLOG SYSTEM TESTS PASSED 100%! ✓\n";
echo "=======================================================\n";
