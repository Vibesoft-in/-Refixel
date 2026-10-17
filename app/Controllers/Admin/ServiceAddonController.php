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
use App\Models\ServiceAddon;
use Throwable;

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

        $services = Database::fetchAll("SELECT id, name, slug, starting_price, category_id, image FROM services WHERE is_active = 1 ORDER BY category_id ASC, name ASC");
        $categories = Category::all('sort_order ASC, name ASC');

        // Map of addon_service_id => array of category IDs it is paired in
        $addonCategoriesMap = [];
        $allPairings = Database::fetchAll("SELECT DISTINCT addon_service_id, COALESCE(source_category_id, (SELECT category_id FROM services WHERE id = source_service_id)) as cat_id FROM service_addons WHERE is_active = 1");
        foreach ($allPairings as $ap) {
            $aid = (int)$ap['addon_service_id'];
            $cid = (int)$ap['cat_id'];
            if ($aid > 0 && $cid > 0) {
                $addonCategoriesMap[$aid][] = $cid;
            }
        }

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
            'addonCategoriesMap' => $addonCategoriesMap,
            'filterServiceId'    => $filterServiceId,
            'filterCategoryId'   => $filterCategoryId,
            'totalRules'         => $totalRules,
            'activeRules'        => $activeRules,
            'servicesWithAddons' => $servicesWithAddons,
            'autoSensible'       => $autoSensible,
        ], 'admin');
    }

    /**
     * Create a new Add-on Pairing Rule across one or multiple categories.
     */
    public function store(Request $request): Response
    {
        $sourceServiceId = (int)$request->input('source_service_id', 0);
        
        // Support multiple category checkboxes (source_category_ids[]) or fallback single (source_category_id)
        $categoryIds = [];
        if ($request->has('source_category_ids')) {
            $raw = $request->input('source_category_ids');
            if (is_array($raw)) {
                $categoryIds = array_values(array_unique(array_filter(array_map('intval', $raw))));
            }
        }
        if (empty($categoryIds) && $request->has('source_category_id')) {
            $singleCid = (int)$request->input('source_category_id', 0);
            if ($singleCid > 0) {
                $categoryIds[] = $singleCid;
            }
        }

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

        // Process Add-on Image Upload
        $uploadedImagePath = null;
        if ($request->hasFile('image')) {
            try {
                $uploadedImagePath = Upload::process($request->file('image'), 'addons');
                if ($addonServiceId > 0) {
                    Service::update($addonServiceId, ['image' => $uploadedImagePath]);
                }
            } catch (Throwable $e) {
                // Ignore upload failure or proceed
            }
        }

        // If a specific source service was chosen, resolve its category
        $srcServiceCatId = 0;
        if ($sourceServiceId > 0) {
            $src = Service::find($sourceServiceId);
            if ($src) {
                $srcServiceCatId = (int)($src['category_id'] ?? 0);
                if (!in_array($srcServiceCatId, $categoryIds, true) && empty($categoryIds)) {
                    $categoryIds[] = $srcServiceCatId;
                }
            }
        }

        if (empty($categoryIds) && $sourceServiceId <= 0) {
            View::setFlash('error', 'Please select at least one Category for this add-on.');
            return $this->redirect('/admin/services/addons');
        }

        if ($sourceServiceId > 0 && $sourceServiceId === $addonServiceId) {
            View::setFlash('error', 'A service cannot be an add-on of itself.');
            return $this->redirect('/admin/services/addons');
        }

        $createdCount = 0;
        $updatedCount = 0;

        // If a specific target service was selected, pair it directly
        if ($sourceServiceId > 0) {
            $existing = Database::fetchOne(
                "SELECT id, custom_image FROM service_addons WHERE source_service_id = :sid AND addon_service_id = :aid",
                ['sid' => $sourceServiceId, 'aid' => $addonServiceId]
            );
            if ($existing) {
                ServiceAddon::update((int)$existing['id'], [
                    'source_category_id' => $srcServiceCatId > 0 ? $srcServiceCatId : null,
                    'custom_badge'       => !empty($badge) ? $badge : '🔥 Popular Add-on',
                    'custom_price'       => $price,
                    'custom_note'        => !empty($note) ? $note : 'Special Companion Rate',
                    'custom_image'       => $uploadedImagePath ?: ($existing['custom_image'] ?? null),
                    'sort_order'         => $sortOrder,
                    'is_active'          => 1,
                ]);
                $updatedCount++;
            } else {
                ServiceAddon::create([
                    'source_service_id'  => $sourceServiceId,
                    'source_category_id' => $srcServiceCatId > 0 ? $srcServiceCatId : null,
                    'addon_service_id'   => $addonServiceId,
                    'custom_badge'       => !empty($badge) ? $badge : '🔥 Popular Add-on',
                    'custom_price'       => $price,
                    'custom_note'        => !empty($note) ? $note : 'Special Companion Rate',
                    'custom_image'       => $uploadedImagePath,
                    'sort_order'         => $sortOrder,
                    'is_active'          => 1,
                ]);
                $createdCount++;
            }
        }

        // Pair across all selected categories (category-wide)
        foreach ($categoryIds as $catId) {
            if ($sourceServiceId > 0 && $catId === $srcServiceCatId) {
                continue;
            }

            $existingCat = Database::fetchOne(
                "SELECT id, custom_image FROM service_addons WHERE source_category_id = :cid AND source_service_id IS NULL AND addon_service_id = :aid",
                ['cid' => $catId, 'aid' => $addonServiceId]
            );

            if ($existingCat) {
                ServiceAddon::update((int)$existingCat['id'], [
                    'custom_badge' => !empty($badge) ? $badge : '🔥 Popular Add-on',
                    'custom_price' => $price,
                    'custom_note'  => !empty($note) ? $note : 'Special Companion Rate',
                    'custom_image' => $uploadedImagePath ?: ($existingCat['custom_image'] ?? null),
                    'sort_order'   => $sortOrder,
                    'is_active'    => 1,
                ]);
                $updatedCount++;
            } else {
                ServiceAddon::create([
                    'source_service_id'  => null,
                    'source_category_id' => $catId,
                    'addon_service_id'   => $addonServiceId,
                    'custom_badge'       => !empty($badge) ? $badge : '🔥 Popular Add-on',
                    'custom_price'       => $price,
                    'custom_note'        => !empty($note) ? $note : 'Special Companion Rate',
                    'custom_image'       => $uploadedImagePath,
                    'sort_order'         => $sortOrder,
                    'is_active'          => 1,
                ]);
                $createdCount++;
            }
        }

        $totalAffected = $createdCount + $updatedCount;
        View::setFlash('success', "Add-on successfully assigned across {$totalAffected} category pairing rule(s)!");
        return $this->redirect('/admin/services/addons');
    }

    /**
     * Update an existing Add-on Pairing Rule across selected categories.
     */
    public function update(Request $request, string|int $id = ''): Response
    {
        $id = (int)($id ?: $request->input('id', 0));
        $rule = ServiceAddon::find($id);

        if (!$rule) {
            View::setFlash('error', 'Add-on rule not found.');
            return $this->redirect('/admin/services/addons');
        }

        // Support multiple category checkboxes (source_category_ids[]) or fallback single (source_category_id)
        $categoryIds = [];
        if ($request->has('source_category_ids')) {
            $raw = $request->input('source_category_ids');
            if (is_array($raw)) {
                $categoryIds = array_values(array_unique(array_filter(array_map('intval', $raw))));
            }
        }
        if (empty($categoryIds) && $request->has('source_category_id')) {
            $singleCid = (int)$request->input('source_category_id', 0);
            if ($singleCid > 0) {
                $categoryIds[] = $singleCid;
            }
        }

        $sourceServiceId = $request->has('source_service_id') ? (int)$request->input('source_service_id') : (int)($rule['source_service_id'] ?? 0);
        $addonServiceId = $request->has('addon_service_id') ? (int)$request->input('addon_service_id') : (int)($rule['addon_service_id'] ?? 0);

        // Process Add-on Image Upload
        $uploadedImagePath = null;
        if ($request->hasFile('image')) {
            try {
                $uploadedImagePath = Upload::process($request->file('image'), 'addons');
                if ($addonServiceId > 0) {
                    Service::update($addonServiceId, ['image' => $uploadedImagePath]);
                }
            } catch (Throwable $e) {
                // Ignore upload failure or proceed
            }
        }

        $ruleImage = $uploadedImagePath ?: ($rule['custom_image'] ?? null);

        // If source service is chosen, resolve its category
        $srcServiceCatId = 0;
        if ($sourceServiceId > 0) {
            $src = Service::find($sourceServiceId);
            if ($src) {
                $srcServiceCatId = (int)($src['category_id'] ?? 0);
                if (!in_array($srcServiceCatId, $categoryIds, true) && empty($categoryIds)) {
                    $categoryIds[] = $srcServiceCatId;
                }
            }
        }

        $badge = trim((string)$request->input('custom_badge', ''));
        $price = $request->input('custom_price') !== null && $request->input('custom_price') !== '' 
            ? (float)$request->input('custom_price') 
            : null;
        $note = trim((string)$request->input('custom_note', ''));
        $sortOrder = (int)$request->input('sort_order', 0);
        $isActive = (int)$request->input('is_active', 1);

        $primaryCatId = !empty($categoryIds) ? $categoryIds[0] : (int)($rule['source_category_id'] ?? 0);

        // 1. Update the primary rule
        ServiceAddon::update($id, [
            'source_category_id' => $primaryCatId > 0 ? $primaryCatId : null,
            'source_service_id'  => $sourceServiceId > 0 ? $sourceServiceId : null,
            'addon_service_id'   => $addonServiceId > 0 ? $addonServiceId : (int)$rule['addon_service_id'],
            'custom_badge'       => !empty($badge) ? $badge : '🔥 Popular Add-on',
            'custom_price'       => $price,
            'custom_note'        => !empty($note) ? $note : 'Special Companion Rate',
            'custom_image'       => $ruleImage,
            'sort_order'         => $sortOrder,
            'is_active'          => $isActive ? 1 : 0,
        ]);

        // 2. Also ensure all other selected categories have this add-on assigned
        foreach ($categoryIds as $catId) {
            if ($catId === $primaryCatId) {
                continue;
            }

            $existingCat = Database::fetchOne(
                "SELECT id, custom_image FROM service_addons WHERE source_category_id = :cid AND source_service_id IS NULL AND addon_service_id = :aid",
                ['cid' => $catId, 'aid' => $addonServiceId]
            );

            if ($existingCat) {
                ServiceAddon::update((int)$existingCat['id'], [
                    'custom_badge' => !empty($badge) ? $badge : '🔥 Popular Add-on',
                    'custom_price' => $price,
                    'custom_note'  => !empty($note) ? $note : 'Special Companion Rate',
                    'custom_image' => $uploadedImagePath ?: ($existingCat['custom_image'] ?? $ruleImage),
                    'sort_order'   => $sortOrder,
                    'is_active'    => $isActive ? 1 : 0,
                ]);
            } else {
                ServiceAddon::create([
                    'source_service_id'  => null,
                    'source_category_id' => $catId,
                    'addon_service_id'   => $addonServiceId,
                    'custom_badge'       => !empty($badge) ? $badge : '🔥 Popular Add-on',
                    'custom_price'       => $price,
                    'custom_note'        => !empty($note) ? $note : 'Special Companion Rate',
                    'custom_image'       => $ruleImage,
                    'sort_order'         => $sortOrder,
                    'is_active'          => $isActive ? 1 : 0,
                ]);
            }
        }

        View::setFlash('success', 'Add-on rule updated across selected categories successfully.');
        return $this->redirect('/admin/services/addons');
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
