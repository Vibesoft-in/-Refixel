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

class ServiceController extends Controller
{
    public function index(Request $request): Response
    {
        $services = Database::fetchAll(
            "SELECT s.*, c.name as category_name, c.slug as category_slug
             FROM services s
             JOIN categories c ON s.category_id = c.id
             ORDER BY c.sort_order ASC, s.name ASC"
        );
        $categories = Category::all('sort_order ASC');

        return $this->render('admin.services.index', [
            'title'      => 'Manage Services | REFIXEL Admin',
            'services'   => $services,
            'categories' => $categories,
        ], 'admin');
    }

    public function storeService(Request $request): Response
    {
        $name = trim((string)$request->input('name'));
        $categoryId = (int)$request->input('category_id');
        $startingPrice = (float)$request->input('starting_price');
        $durationMinutes = (int)$request->input('duration_minutes', 60);
        $description = trim((string)$request->input('description'));

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
            'is_active'        => 1,
        ]);

        View::setFlash('success', "Service '{$name}' created successfully.");
        return $this->redirect('/admin/services');
    }

    public function updateService(Request $request, string $id): Response
    {
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
        $icon = trim((string)$request->input('icon', 'fa-wrench'));
        $sortOrder = (int)$request->input('sort_order', 0);

        if (empty($name)) {
            View::setFlash('error', 'Category name is required.');
            return $this->redirect('/admin/services/categories');
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        Category::create([
            'name'        => $name,
            'slug'        => $slug,
            'description' => $description,
            'icon'        => $icon,
            'sort_order'  => $sortOrder,
            'is_active'   => 1,
        ]);

        View::setFlash('success', "Category '{$name}' created successfully.");
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
        $icon = trim((string)$request->input('icon', 'fa-wrench'));
        $sortOrder = (int)$request->input('sort_order', 0);

        if (empty($name)) {
            View::setFlash('error', 'Category name is required.');
            return $this->redirect('/admin/services/categories');
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        Category::update((int)$id, [
            'name'        => $name,
            'slug'        => $slug,
            'description' => $description,
            'icon'        => $icon,
            'sort_order'  => $sortOrder,
        ]);

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
}

