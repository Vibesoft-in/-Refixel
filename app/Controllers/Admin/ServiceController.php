<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Upload;
use App\Core\View;
use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceVariant;

class ServiceController extends Controller
{
    public function index(Request $request): Response
    {
        Service::ensureServiceSchema();
        $selectedCatId = (int)$request->query('category_id', 0);
        $typeFilter = (string)$request->query('type', 'main');
        $search = trim((string)($request->query('q') ?? ''));

        $sql = "SELECT s.*, c.name as category_name, c.slug as category_slug
                FROM services s
                JOIN categories c ON s.category_id = c.id
                WHERE 1=1";
        $params = [];

        // By default, strictly exclude Add-on services from the Main Services Catalog
        if ($typeFilter === 'main') {
            $sql .= " AND (s.is_addon = 0 OR s.is_addon IS NULL)";
        } elseif ($typeFilter === 'addon') {
            $sql .= " AND s.is_addon = 1";
        }

        if ($selectedCatId > 0) {
            $sql .= " AND s.category_id = :cid";
            $params['cid'] = $selectedCatId;
        }

        if (!empty($search)) {
            $sql .= " AND (s.name LIKE :q OR s.slug LIKE :q OR s.description LIKE :q)";
            $params['q'] = "%{$search}%";
        }

        $sql .= " ORDER BY c.sort_order ASC, s.name ASC";

        $services = Database::fetchAll($sql, $params);
        $categories = Category::all('sort_order ASC');

        $stats = Database::fetchOne("
            SELECT 
                COUNT(CASE WHEN is_addon = 0 OR is_addon IS NULL THEN 1 END) as total_main,
                COUNT(CASE WHEN is_addon = 1 THEN 1 END) as total_addons,
                COUNT(CASE WHEN (is_addon = 0 OR is_addon IS NULL) AND is_active = 1 THEN 1 END) as active_main,
                COUNT(CASE WHEN (is_addon = 0 OR is_addon IS NULL) AND is_coming_soon = 1 THEN 1 END) as coming_soon_main
            FROM services
        ");

        return $this->render('admin.services.index', [
            'title'         => 'Services & Pricing Catalog | REFIXEL Admin',
            'services'      => $services,
            'categories'    => $categories,
            'selectedCatId' => $selectedCatId,
            'typeFilter'    => $typeFilter,
            'search'        => $search,
            'stats'         => $stats ?? [],
        ], 'admin');
    }

    public function storeService(Request $request): Response
    {
        Service::ensureServiceSchema();
        $name = trim((string)$request->input('name'));
        $categoryId = (int)$request->input('category_id');
        $startingPrice = (float)$request->input('starting_price');
        $durationMinutes = (int)$request->input('duration_minutes', 60);
        $description = trim((string)$request->input('description'));
        $isAddon = (int)$request->input('is_addon', 0);
        $isComingSoon = (int)$request->input('is_coming_soon', 0);

        if (empty($name) || $categoryId <= 0 || $startingPrice < 0) {
            View::setFlash('error', 'Please provide a valid service name, category, and price.');
            return $this->redirect('/admin/services');
        }

        $imagePath = 'refixel-cleaning.jpg';
        $fileImage = $request->file('image');
        if ($fileImage && ($fileImage['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            try {
                $imagePath = Upload::process($fileImage, 'services');
            } catch (\Throwable $e) {}
        } elseif ($request->has('image_path')) {
            $pathVal = trim((string)$request->input('image_path'));
            if (!empty($pathVal)) {
                $imagePath = $pathVal;
            }
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        Service::create([
            'category_id'      => $categoryId,
            'name'             => $name,
            'slug'             => $slug,
            'starting_price'   => $startingPrice,
            'duration_minutes' => $durationMinutes > 0 ? $durationMinutes : 60,
            'description'      => $description,
            'image'            => $imagePath,
            'is_addon'         => $isAddon ? 1 : 0,
            'is_coming_soon'   => $isComingSoon ? 1 : 0,
            'is_active'        => 1,
        ]);

        View::setFlash('success', "Service '{$name}' created successfully.");
        return $this->redirect('/admin/services');
    }

    public function updateService(Request $request, string $id): Response
    {
        Service::ensureServiceSchema();
        $service = Service::find((int)$id);
        if (!$service) {
            View::setFlash('error', 'Service not found.');
            return $this->redirect('/admin/services');
        }

        $name = trim((string)$request->input('name'));
        $categoryId = (int)$request->input('category_id');
        $startingPrice = (float)$request->input('starting_price');
        $durationMinutes = (int)$request->input('duration_minutes', 60);
        $description = trim((string)$request->input('description'));

        if (empty($name) || $categoryId <= 0 || $startingPrice < 0) {
            View::setFlash('error', 'Please provide a valid service name, category, and price.');
            return $this->redirect('/admin/services');
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        $data = [
            'category_id'      => $categoryId,
            'name'             => $name,
            'slug'             => $slug,
            'starting_price'   => $startingPrice,
            'duration_minutes' => $durationMinutes > 0 ? $durationMinutes : 60,
            'description'      => $description,
            'is_addon'         => $request->has('is_addon') ? ((int)$request->input('is_addon') ? 1 : 0) : 0,
            'is_coming_soon'   => $request->has('is_coming_soon') ? ((int)$request->input('is_coming_soon') ? 1 : 0) : 0,
        ];

        $fileImage = $request->file('image');
        if ($fileImage && ($fileImage['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            try {
                $data['image'] = Upload::process($fileImage, 'services');
            } catch (\Throwable $e) {}
        } elseif ($request->has('image_path')) {
            $pathVal = trim((string)$request->input('image_path'));
            if (!empty($pathVal)) {
                $data['image'] = $pathVal;
            }
        }

        Service::update((int)$id, $data);
        View::setFlash('success', "Service '{$name}' updated successfully.");
        return $this->redirect('/admin/services');
    }

    public function deleteService(Request $request, string $id): Response
    {
        Service::delete((int)$id);
        View::setFlash('success', 'Service deleted successfully.');
        return $this->redirect('/admin/services');
    }

    public function toggleServiceStatus(Request $request, string $id): Response
    {
        $service = Service::find((int)$id);
        if (!$service) {
            View::setFlash('error', 'Service not found.');
            return $this->redirect('/admin/services');
        }

        $newActive = empty($service['is_active']) ? 1 : 0;
        Service::update((int)$id, ['is_active' => $newActive]);

        View::setFlash('success', "Service '{$service['name']}' " . ($newActive ? 'activated' : 'deactivated') . ".");
        return $this->redirect('/admin/services');
    }

    public function toggleAddonStatus(Request $request, string $id): Response
    {
        Service::ensureServiceSchema();
        $service = Service::find((int)$id);
        if (!$service) {
            View::setFlash('error', 'Service not found.');
            return $this->redirect('/admin/services');
        }

        $newAddon = empty($service['is_addon']) ? 1 : 0;
        Service::update((int)$id, ['is_addon' => $newAddon]);

        $statusText = $newAddon 
            ? 'moved to Add-ons Only (hidden from main catalog)' 
            : 'moved back to Main Public Catalog (now visible in Services catalog)';
        View::setFlash('success', "Service '{$service['name']}' {$statusText}.");

        $redirectTo = $request->input('redirect_to');
        if (!empty($redirectTo)) {
            return $this->redirect($redirectTo);
        }

        return $this->redirect('/admin/services');
    }

    public function toggleComingSoon(Request $request, string $id): Response
    {
        Service::ensureServiceSchema();
        $service = Service::find((int)$id);
        if (!$service) {
            if ($request->isAjax()) {
                return Response::json(['success' => false, 'error' => 'Service not found.'], 404);
            }
            View::setFlash('error', 'Service not found.');
            return $this->redirect('/admin/services');
        }

        $newComingSoon = empty($service['is_coming_soon']) ? 1 : 0;
        Service::update((int)$id, ['is_coming_soon' => $newComingSoon]);

        $statusText = $newComingSoon 
            ? 'marked as Coming Soon (visible to customers, but bookings & cart are paused)' 
            : 'marked as Live & Bookable (customers can add to cart and book)';
        $msg = "Service '{$service['name']}' {$statusText}.";

        if ($request->isAjax()) {
            return Response::json([
                'success'        => true,
                'is_coming_soon' => $newComingSoon,
                'message'        => $msg,
            ]);
        }

        View::setFlash('success', $msg);
        return $this->redirect('/admin/services');
    }

    public function categories(Request $request): Response
    {
        $categories = Database::fetchAll(
            "SELECT c.*, COUNT(s.id) as service_count
             FROM categories c
             LEFT JOIN services s ON c.id = s.category_id
             GROUP BY c.id
             ORDER BY c.sort_order ASC, c.name ASC"
        );

        return $this->render('admin.services.categories', [
            'title'      => 'Trade Categories | REFIXEL Admin',
            'categories' => $categories,
        ], 'admin');
    }

    public function storeCategory(Request $request): Response
    {
        $name = trim((string)$request->input('name'));
        $description = trim((string)$request->input('description'));
        $icon = trim((string)$request->input('icon', 'icons/cleaning icon.png'));
        $sortOrder = (int)$request->input('sort_order', 0);

        if (empty($name)) {
            View::setFlash('error', 'Category name is required.');
            return $this->redirect('/admin/services/categories');
        }

        $slugInput = trim((string)$request->input('slug', ''));
        $slug = !empty($slugInput)
            ? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $slugInput), '-'))
            : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        $imagePath = 'refixel-cleaning.jpg';
        $fileImage = $request->file('image');
        if ($fileImage && ($fileImage['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            try {
                $imagePath = Upload::process($fileImage, 'categories');
            } catch (\Throwable $e) {}
        } elseif ($request->has('image_path')) {
            $pathVal = trim((string)$request->input('image_path'));
            if (!empty($pathVal)) {
                $imagePath = $pathVal;
            }
        }

        $fileIcon = $request->file('icon_file');
        if ($fileIcon && ($fileIcon['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            try {
                $icon = Upload::process($fileIcon, 'categories');
            } catch (\Throwable $e) {}
        }

        Category::create([
            'name'        => $name,
            'slug'        => $slug,
            'description' => $description,
            'image'       => $imagePath,
            'icon'        => $icon,
            'sort_order'  => $sortOrder,
            'is_active'   => 1,
        ]);

        View::setFlash('success', "Category '{$name}' created successfully and is now active on the homepage!");
        return $this->redirect('/admin/services/categories');
    }

    public function updateCategory(Request $request, string $id): Response
    {
        $cat = Category::find((int)$id);
        if (!$cat) {
            View::setFlash('error', 'Category not found.');
            return $this->redirect('/admin/services/categories');
        }

        $name = trim((string)$request->input('name'));
        $description = trim((string)$request->input('description'));
        $icon = trim((string)$request->input('icon', $cat['icon'] ?? 'icons/cleaning icon.png'));
        $sortOrder = (int)$request->input('sort_order', 0);

        if (empty($name)) {
            View::setFlash('error', 'Category name is required.');
            return $this->redirect('/admin/services/categories');
        }

        $slugInput = trim((string)$request->input('slug', ''));
        $slug = !empty($slugInput)
            ? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $slugInput), '-'))
            : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        $data = [
            'name'        => $name,
            'slug'        => $slug,
            'description' => $description,
            'sort_order'  => $sortOrder,
        ];

        $fileImage = $request->file('image');
        if ($fileImage && ($fileImage['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            try {
                $data['image'] = Upload::process($fileImage, 'categories');
            } catch (\Throwable $e) {}
        } elseif ($request->has('image_path')) {
            $pathVal = trim((string)$request->input('image_path'));
            if (!empty($pathVal)) {
                $data['image'] = $pathVal;
            }
        }

        $fileIcon = $request->file('icon_file');
        if ($fileIcon && ($fileIcon['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            try {
                $data['icon'] = Upload::process($fileIcon, 'categories');
            } catch (\Throwable $e) {}
        } elseif ($request->has('icon')) {
            $iconVal = trim((string)$request->input('icon'));
            if (!empty($iconVal)) {
                $data['icon'] = $iconVal;
            }
        }

        Category::update((int)$id, $data);

        View::setFlash('success', "Category '{$name}' updated successfully.");
        return $this->redirect('/admin/services/categories');
    }

    public function deleteCategory(Request $request, string $id): Response
    {
        $cat = Category::find((int)$id);
        if (!$cat) {
            View::setFlash('error', 'Category not found.');
            return $this->redirect('/admin/services/categories');
        }

        $serviceCount = Database::fetchOne("SELECT COUNT(*) as cnt FROM services WHERE category_id = :id", ['id' => (int)$id]);
        if (!empty($serviceCount['cnt']) && (int)$serviceCount['cnt'] > 0) {
            View::setFlash('error', "Cannot delete category '{$cat['name']}' because it has {$serviceCount['cnt']} associated service(s). Please reassign or delete them first.");
            return $this->redirect('/admin/services/categories');
        }

        Category::delete((int)$id);
        View::setFlash('success', "Category '{$cat['name']}' deleted successfully.");
        return $this->redirect('/admin/services/categories');
    }

    public function toggleCategoryStatus(Request $request, string $id): Response
    {
        $cat = Category::find((int)$id);
        if (!$cat) {
            View::setFlash('error', 'Category not found.');
            return $this->redirect('/admin/services/categories');
        }

        $newActive = empty($cat['is_active']) ? 1 : 0;
        Category::update((int)$id, ['is_active' => $newActive]);

        View::setFlash('success', "Category '{$cat['name']}' " . ($newActive ? 'activated' : 'deactivated') . ".");
        return $this->redirect('/admin/services/categories');
    }

    public function variants(Request $request, string $id): Response
    {
        ServiceVariant::ensureSchema();
        $service = Service::find((int)$id);
        if (!$service) {
            View::setFlash('error', 'Service not found.');
            return $this->redirect('/admin/services');
        }

        $category = Category::find((int)$service['category_id']);
        $variants = ServiceVariant::forService((int)$id, false);

        // Group variants by group_name
        $groupedVariants = [];
        foreach ($variants as $v) {
            $grp = !empty($v['group_name']) ? $v['group_name'] : 'Option';
            if (!isset($groupedVariants[$grp])) {
                $groupedVariants[$grp] = [];
            }
            $groupedVariants[$grp][] = $v;
        }

        return $this->render('admin.services.variants', [
            'title'           => "{$service['name']} - Variations & Pricing | REFIXEL Admin",
            'service'         => $service,
            'category'        => $category,
            'groupedVariants' => $groupedVariants,
            'allVariants'     => $variants,
        ], 'admin');
    }

    public function storeVariant(Request $request, string $id): Response
    {
        ServiceVariant::ensureSchema();
        $service = Service::find((int)$id);
        if (!$service) {
            View::setFlash('error', 'Service not found.');
            return $this->redirect('/admin/services');
        }

        $groupName = trim((string)$request->input('group_name', 'Option'));
        $name = trim((string)$request->input('name', ''));
        $price = (float)$request->input('price', 0);
        $duration = (int)$request->input('duration_minutes', 60);
        $badge = trim((string)$request->input('badge', '')) ?: null;
        $isDefault = (int)$request->input('is_default', 0);
        $sortOrder = (int)$request->input('sort_order', 0);

        if (empty($name)) {
            View::setFlash('error', 'Option / Variation name is required.');
            return $this->redirect("/admin/services/{$id}/variants");
        }

        if ($isDefault === 1) {
            // Clear previous default in the same group
            Database::execute("UPDATE service_variants SET is_default = 0 WHERE service_id = ? AND group_name = ?", [(int)$id, $groupName]);
        }

        Database::execute(
            "INSERT INTO service_variants (service_id, group_name, name, price, duration_minutes, badge, is_default, sort_order, is_active, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())",
            [(int)$id, $groupName, $name, $price, $duration, $badge, $isDefault, $sortOrder]
        );

        View::setFlash('success', "Option '{$name}' added to {$service['name']} successfully.");
        return $this->redirect("/admin/services/{$id}/variants");
    }

    public function updateVariant(Request $request, string $id): Response
    {
        ServiceVariant::ensureSchema();
        $variant = Database::fetchOne("SELECT * FROM service_variants WHERE id = ?", [(int)$id]);
        if (!$variant) {
            View::setFlash('error', 'Variation option not found.');
            return $this->redirect('/admin/services');
        }

        $serviceId = (int)$variant['service_id'];
        $groupName = trim((string)$request->input('group_name', $variant['group_name']));
        $name = trim((string)$request->input('name', $variant['name']));
        $price = (float)$request->input('price', $variant['price']);
        $duration = (int)$request->input('duration_minutes', $variant['duration_minutes']);
        $badge = trim((string)$request->input('badge', '')) ?: null;
        $isDefault = (int)$request->input('is_default', 0);
        $sortOrder = (int)$request->input('sort_order', $variant['sort_order']);

        if (empty($name)) {
            View::setFlash('error', 'Option name cannot be empty.');
            return $this->redirect("/admin/services/{$serviceId}/variants");
        }

        if ($isDefault === 1) {
            Database::execute("UPDATE service_variants SET is_default = 0 WHERE service_id = ? AND group_name = ? AND id != ?", [$serviceId, $groupName, (int)$id]);
        }

        Database::execute(
            "UPDATE service_variants SET group_name = ?, name = ?, price = ?, duration_minutes = ?, badge = ?, is_default = ?, sort_order = ? WHERE id = ?",
            [$groupName, $name, $price, $duration, $badge, $isDefault, $sortOrder, (int)$id]
        );

        View::setFlash('success', "Option '{$name}' updated successfully.");
        return $this->redirect("/admin/services/{$serviceId}/variants");
    }

    public function deleteVariant(Request $request, string $id): Response
    {
        ServiceVariant::ensureSchema();
        $variant = Database::fetchOne("SELECT * FROM service_variants WHERE id = ?", [(int)$id]);
        if (!$variant) {
            View::setFlash('error', 'Variation option not found.');
            return $this->redirect('/admin/services');
        }

        $serviceId = (int)$variant['service_id'];
        Database::execute("DELETE FROM service_variants WHERE id = ?", [(int)$id]);

        View::setFlash('success', "Option '{$variant['name']}' deleted successfully.");
        return $this->redirect("/admin/services/{$serviceId}/variants");
    }

    public function toggleVariantStatus(Request $request, string $id): Response
    {
        ServiceVariant::ensureSchema();
        $variant = Database::fetchOne("SELECT * FROM service_variants WHERE id = ?", [(int)$id]);
        if (!$variant) {
            View::setFlash('error', 'Variation option not found.');
            return $this->redirect('/admin/services');
        }

        $serviceId = (int)$variant['service_id'];
        $newActive = empty($variant['is_active']) ? 1 : 0;
        Database::execute("UPDATE service_variants SET is_active = ? WHERE id = ?", [$newActive, (int)$id]);

        View::setFlash('success', "Option '{$variant['name']}' " . ($newActive ? 'activated' : 'deactivated') . ".");
        return $this->redirect("/admin/services/{$serviceId}/variants");
    }
}

