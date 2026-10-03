<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
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
            'title'      => 'Manage Services | Primodomus Admin',
            'services'   => $services,
            'categories' => $categories,
        ], 'admin');
    }

    public function storeService(Request $request): Response
    {
        $name = trim((string)$request->input('name'));
        $categoryId = (int)$request->input('category_id');
        $startingPrice = (float)$request->input('starting_price');
        $description = trim((string)$request->input('description'));

        if (empty($name) || $categoryId <= 0 || $startingPrice < 0) {
            View::setFlash('error', 'Please provide a valid service name, category, and price.');
            return $this->redirect('/admin/services');
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        Service::create([
            'category_id'    => $categoryId,
            'name'           => $name,
            'slug'           => $slug,
            'starting_price' => $startingPrice,
            'description'    => $description,
            'is_active'      => 1,
        ]);

        View::setFlash('success', "Service '{$name}' created successfully.");
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
            'title'      => 'Trade Categories | Primodomus Admin',
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
