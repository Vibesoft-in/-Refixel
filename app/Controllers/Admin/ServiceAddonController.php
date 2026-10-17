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
use App\Models\ServiceAddon;

class ServiceAddonController extends Controller
{
    /**
     * Display the Add-ons Management Dashboard.
     */
    public function index(Request $request): Response
    {
        $filterServiceId = $request->input('service_id') ? (int)$request->input('service_id') : null;
        $filterCategoryId = $request->input('category_id') ? (int)$request->input('category_id') : null;
        $rules = ServiceAddon::allAdminList($filterServiceId, $filterCategoryId);

        $services = Database::fetchAll("SELECT id, name, slug, starting_price, category_id FROM services WHERE is_active = 1 ORDER BY category_id ASC, name ASC");
        $categories = Category::all('sort_order ASC, name ASC');

        // Statistics
        $totalRules = count(Database::fetchAll("SELECT id FROM service_addons"));
        $activeRules = count(Database::fetchAll("SELECT id FROM service_addons WHERE is_active = 1"));
        $servicesWithAddons = count(Database::fetchAll("SELECT DISTINCT source_service_id FROM service_addons WHERE source_service_id IS NOT NULL"));
        $autoSensible = ServiceAddon::isAutoSensibleEnabled();

        return $this->render('admin.services.addons', [
            'title'              => 'Add-ons Control & Smart Pairing | REFIXEL Admin',
            'rules'              => $rules,
            'services'           => $services,
            'categories'         => $categories,
            'filterServiceId'    => $filterServiceId,
            'filterCategoryId'   => $filterCategoryId,
            'totalRules'         => $totalRules,
            'activeRules'        => $activeRules,
            'servicesWithAddons' => $servicesWithAddons,
            'autoSensible'       => $autoSensible,
        ], 'admin');
    }

    /**
     * Create a new Add-on Pairing Rule.
     */
    public function store(Request $request): Response
    {
        $sourceServiceId = (int)$request->input('source_service_id', 0);
        $sourceCategoryId = (int)$request->input('source_category_id', 0);
        $addonServiceId = (int)$request->input('addon_service_id', 0);
        $badge = trim((string)$request->input('custom_badge', '🔥 Popular Add-on'));
        $price = $request->input('custom_price') !== null && $request->input('custom_price') !== '' 
            ? (float)$request->input('custom_price') 
            : null;
        $note = trim((string)$request->input('custom_note', 'Special Companion Rate'));
        $sortOrder = (int)$request->input('sort_order', 0);

        if ($addonServiceId <= 0) {
            View::setFlash('error', 'Please select a valid add-on service to pair.');
            return $this->redirect('/admin/services/addons');
        }

        // If source service is chosen, resolve its category if not explicitly provided
        if ($sourceServiceId > 0 && $sourceCategoryId <= 0) {
            $src = Service::find($sourceServiceId);
            if ($src) {
                $sourceCategoryId = (int)($src['category_id'] ?? 0);
            }
        }

        if ($sourceServiceId <= 0 && $sourceCategoryId <= 0) {
            View::setFlash('error', 'Please select a Category or Target Service.');
            return $this->redirect('/admin/services/addons');
        }

        if ($sourceServiceId > 0 && $sourceServiceId === $addonServiceId) {
            View::setFlash('error', 'A service cannot be an add-on of itself.');
            return $this->redirect('/admin/services/addons');
        }

        // Check if pairing already exists
        if ($sourceServiceId > 0) {
            $existing = Database::fetchOne(
                "SELECT id FROM service_addons WHERE source_service_id = :sid AND addon_service_id = :aid",
                ['sid' => $sourceServiceId, 'aid' => $addonServiceId]
            );
        } else {
            $existing = Database::fetchOne(
                "SELECT id FROM service_addons WHERE source_category_id = :cid AND source_service_id IS NULL AND addon_service_id = :aid",
                ['cid' => $sourceCategoryId, 'aid' => $addonServiceId]
            );
        }

        if ($existing) {
            View::setFlash('error', 'This service add-on pairing already exists. You can edit the existing rule below.');
            return $this->redirect('/admin/services/addons');
        }

        ServiceAddon::create([
            'source_service_id'  => $sourceServiceId > 0 ? $sourceServiceId : null,
            'source_category_id' => $sourceCategoryId > 0 ? $sourceCategoryId : null,
            'addon_service_id'   => $addonServiceId,
            'custom_badge'       => !empty($badge) ? $badge : '🔥 Popular Add-on',
            'custom_price'       => $price,
            'custom_note'        => !empty($note) ? $note : 'Special Companion Rate',
            'sort_order'         => $sortOrder,
            'is_active'          => 1,
        ]);

        View::setFlash('success', 'Smart add-on pairing created successfully!');
        return $this->redirect('/admin/services/addons' . ($sourceServiceId > 0 ? '?service_id=' . $sourceServiceId : ($sourceCategoryId > 0 ? '?category_id=' . $sourceCategoryId : '')));
    }

    /**
     * Update an existing Add-on Pairing Rule.
     */
    public function update(Request $request, string|int $id = ''): Response
    {
        $id = (int)($id ?: $request->input('id', 0));
        $rule = ServiceAddon::find($id);

        if (!$rule) {
            View::setFlash('error', 'Add-on rule not found.');
            return $this->redirect('/admin/services/addons');
        }

        $sourceCategoryId = $request->has('source_category_id') ? (int)$request->input('source_category_id') : (int)($rule['source_category_id'] ?? 0);
        $sourceServiceId = $request->has('source_service_id') ? (int)$request->input('source_service_id') : (int)($rule['source_service_id'] ?? 0);
        $addonServiceId = $request->has('addon_service_id') ? (int)$request->input('addon_service_id') : (int)($rule['addon_service_id'] ?? 0);

        // If source service is chosen, resolve its category if not explicitly provided
        if ($sourceServiceId > 0 && $sourceCategoryId <= 0) {
            $src = Service::find($sourceServiceId);
            if ($src) {
                $sourceCategoryId = (int)($src['category_id'] ?? 0);
            }
        }

        $badge = trim((string)$request->input('custom_badge', ''));
        $price = $request->input('custom_price') !== null && $request->input('custom_price') !== '' 
            ? (float)$request->input('custom_price') 
            : null;
        $note = trim((string)$request->input('custom_note', ''));
        $sortOrder = (int)$request->input('sort_order', 0);
        $isActive = (int)$request->input('is_active', 1);

        ServiceAddon::update($id, [
            'source_category_id' => $sourceCategoryId > 0 ? $sourceCategoryId : null,
            'source_service_id'  => $sourceServiceId > 0 ? $sourceServiceId : null,
            'addon_service_id'   => $addonServiceId > 0 ? $addonServiceId : (int)$rule['addon_service_id'],
            'custom_badge'       => !empty($badge) ? $badge : '🔥 Popular Add-on',
            'custom_price'       => $price,
            'custom_note'        => !empty($note) ? $note : 'Special Companion Rate',
            'sort_order'         => $sortOrder,
            'is_active'          => $isActive ? 1 : 0,
        ]);

        View::setFlash('success', 'Add-on rule updated successfully.');
        return $this->redirect('/admin/services/addons' . (!empty($sourceServiceId) ? '?service_id=' . $sourceServiceId : (!empty($sourceCategoryId) ? '?category_id=' . $sourceCategoryId : '')));
    }

    /**
     * Delete an Add-on Pairing Rule.
     */
    public function delete(Request $request, string|int $id = ''): Response
    {
        $id = (int)($id ?: $request->input('id', 0));
        $rule = ServiceAddon::find($id);

        if ($rule) {
            Database::query("DELETE FROM service_addons WHERE id = :id", ['id' => $id]);
            View::setFlash('success', 'Add-on pairing rule removed.');
        } else {
            View::setFlash('error', 'Rule not found.');
        }

        return $this->redirect('/admin/services/addons');
    }

    /**
     * Toggle Active Status of an Add-on Rule.
     */
    public function toggleStatus(Request $request, string|int $id = ''): Response
    {
        $id = (int)($id ?: $request->input('id', 0));
        $rule = ServiceAddon::find($id);

        if (!$rule) {
            if ($request->isAjax()) {
                return Response::json(['status' => 'error', 'message' => 'Rule not found'], 404);
            }
            View::setFlash('error', 'Rule not found.');
            return $this->redirect('/admin/services/addons');
        }

        $newStatus = ((int)$rule['is_active'] === 1) ? 0 : 1;
        ServiceAddon::update($id, ['is_active' => $newStatus]);

        if ($request->isAjax()) {
            return Response::json([
                'status'    => 'success',
                'is_active' => $newStatus,
                'message'   => $newStatus ? 'Add-on pairing enabled.' : 'Add-on pairing disabled.',
            ]);
        }

        View::setFlash('success', $newStatus ? 'Add-on rule enabled.' : 'Add-on rule disabled.');
        return $this->redirect('/admin/services/addons');
    }

    /**
     * Toggle the Global Auto-Sensible Add-on Engine.
     */
    public function toggleAutoEngine(Request $request): Response
    {
        $current = ServiceAddon::isAutoSensibleEnabled();
        $newVal = !$current;
        ServiceAddon::setAutoSensibleEnabled($newVal);

        $msg = $newVal 
            ? 'Intelligent Auto-Sensible Engine ENABLED! Services without custom rules will automatically show sensible companion addons.'
            : 'Intelligent Auto-Sensible Engine DISABLED! Only explicitly configured admin add-on rules will be shown.';

        View::setFlash('success', $msg);
        return $this->redirect('/admin/services/addons');
    }

    /**
     * Quickly create a new catalog service and optionally pair it as an addon.
     */
    public function createAddonService(Request $request): Response
    {
        $name = trim((string)$request->input('name'));
        $categoryId = (int)$request->input('category_id');
        $price = (float)$request->input('starting_price');
        $duration = (int)$request->input('duration_minutes', 30);
        $desc = trim((string)$request->input('description', ''));
        $image = trim((string)$request->input('image', 'refixel-cleaning.jpg'));
        $pairWithServiceId = (int)$request->input('pair_with_service_id', 0);

        if (empty($name) || $categoryId <= 0 || $price < 0) {
            View::setFlash('error', 'Please provide a valid add-on service name, category, and price.');
            return $this->redirect('/admin/services/addons');
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        $existing = Database::fetchOne("SELECT id FROM services WHERE slug = :slug", ['slug' => $slug]);
        if ($existing) {
            $slug .= '-' . rand(100, 999);
        }

        Database::query(
            "INSERT INTO services (category_id, name, slug, description, starting_price, duration_minutes, image, is_active, created_at)
             VALUES (:cid, :name, :slug, :desc, :price, :duration, :img, 1, NOW())",
            [
                'cid'      => $categoryId,
                'name'     => $name,
                'slug'     => $slug,
                'desc'     => $desc,
                'price'    => $price,
                'duration' => $duration,
                'img'      => !empty($image) ? $image : 'refixel-cleaning.jpg',
            ]
        );

        $newServiceId = (int)Database::lastInsertId();

        // If admin selected to pair it immediately with a source service
        if ($pairWithServiceId > 0 && $newServiceId > 0) {
            ServiceAddon::create([
                'source_service_id'  => $pairWithServiceId,
                'source_category_id' => null,
                'addon_service_id'   => $newServiceId,
                'custom_badge'       => '🔥 New Add-on',
                'custom_price'       => $price,
                'custom_note'        => 'Companion Service',
                'sort_order'         => 0,
                'is_active'          => 1,
            ]);
        }

        View::setFlash('success', "New add-on service '{$name}' created and added to catalog!");
        return $this->redirect('/admin/services/addons' . ($pairWithServiceId > 0 ? '?service_id=' . $pairWithServiceId : ''));
    }
}
