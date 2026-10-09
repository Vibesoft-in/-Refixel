<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Upload;
use App\Core\View;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\ProcessStep;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceArea;

class ContentController extends Controller
{
    // ==========================================
    // FAQs
    // ==========================================
    public function faqs(Request $request): Response
    {
        $faqs = Database::fetchAll(
            "SELECT f.*, s.name as service_name
             FROM faqs f
             LEFT JOIN services s ON f.service_id = s.id
             ORDER BY f.sort_order ASC, f.id DESC"
        );
        $services = Service::all('name ASC');

        return $this->render('admin.content.faqs', [
            'title'    => 'Manage FAQs | REFIXEL Admin',
            'faqs'     => $faqs,
            'services' => $services,
        ], 'admin');
    }

    public function storeFaq(Request $request): Response
    {
        $question = trim((string)$request->input('question'));
        $answer = trim((string)$request->input('answer'));
        $serviceId = (int)$request->input('service_id', 0);
        $sortOrder = (int)$request->input('sort_order', 0);

        if (empty($question) || empty($answer)) {
            View::setFlash('error', 'Question and answer are required.');
            return $this->redirect('/admin/content/faqs');
        }

        Faq::create([
            'service_id' => $serviceId > 0 ? $serviceId : null,
            'question'   => $question,
            'answer'     => $answer,
            'sort_order' => $sortOrder,
            'is_active'  => 1,
        ]);

        View::setFlash('success', 'FAQ added successfully.');
        return $this->redirect('/admin/content/faqs');
    }

    public function deleteFaq(Request $request, string $id): Response
    {
        Database::query("DELETE FROM faqs WHERE id = :id", ['id' => $id]);
        View::setFlash('success', 'FAQ deleted.');
        return $this->redirect('/admin/content/faqs');
    }

    // ==========================================
    // GALLERY
    // ==========================================
    public function gallery(Request $request): Response
    {
        $items = Database::fetchAll(
            "SELECT g.*, s.name as service_name
             FROM gallery_items g
             LEFT JOIN services s ON g.service_id = s.id
             ORDER BY g.sort_order ASC, g.id DESC"
        );
        $services = Service::all('name ASC');

        return $this->render('admin.content.gallery', [
            'title'    => 'Manage Gallery | REFIXEL Admin',
            'items'    => $items,
            'services' => $services,
        ], 'admin');
    }

    public function storeGallery(Request $request): Response
    {
        $title = trim((string)$request->input('title'));
        $serviceId = (int)$request->input('service_id', 0);

        $beforeImg = 'assets/img/gallery-1.jpg';
        $afterImg = 'assets/img/gallery-2.jpg';

        $fileBefore = $request->file('before_image');
        if ($fileBefore && ($fileBefore['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            try {
                $beforeImg = Upload::process($fileBefore, 'gallery');
            } catch (\Throwable $e) {}
        }

        $fileAfter = $request->file('after_image');
        if ($fileAfter && ($fileAfter['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            try {
                $afterImg = Upload::process($fileAfter, 'gallery');
            } catch (\Throwable $e) {}
        }

        GalleryItem::create([
            'service_id'   => $serviceId > 0 ? $serviceId : null,
            'title'        => !empty($title) ? $title : 'Service Transformation',
            'before_image' => $beforeImg,
            'after_image'  => $afterImg,
            'sort_order'   => 0,
            'is_active'    => 1,
        ]);

        View::setFlash('success', 'Gallery item created successfully.');
        return $this->redirect('/admin/content/gallery');
    }

    public function updateGallery(Request $request, string $id): Response
    {
        $item = GalleryItem::find((int)$id);
        if (!$item) {
            View::setFlash('error', 'Showcase item not found.');
            return $this->redirect('/admin/content/gallery');
        }

        $title = trim((string)$request->input('title'));
        $serviceId = (int)$request->input('service_id', 0);
        $sortOrder = (int)$request->input('sort_order', 0);

        $updateData = [
            'title'      => !empty($title) ? $title : $item['title'],
            'service_id' => $serviceId > 0 ? $serviceId : null,
            'sort_order' => $sortOrder,
        ];

        $fileBefore = $request->file('before_image');
        if ($fileBefore && ($fileBefore['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            try {
                $updateData['before_image'] = Upload::process($fileBefore, 'gallery');
            } catch (\Throwable $e) {}
        } elseif ($request->has('before_image_path')) {
            $pathVal = trim((string)$request->input('before_image_path'));
            if (!empty($pathVal)) {
                $updateData['before_image'] = $pathVal;
            }
        }

        $fileAfter = $request->file('after_image');
        if ($fileAfter && ($fileAfter['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            try {
                $updateData['after_image'] = Upload::process($fileAfter, 'gallery');
            } catch (\Throwable $e) {}
        } elseif ($request->has('after_image_path')) {
            $pathVal = trim((string)$request->input('after_image_path'));
            if (!empty($pathVal)) {
                $updateData['after_image'] = $pathVal;
            }
        }

        GalleryItem::update((int)$id, $updateData);
        View::setFlash('success', 'Gallery showcase updated successfully.');
        return $this->redirect('/admin/content/gallery');
    }

    public function deleteGallery(Request $request, string $id): Response
    {
        GalleryItem::delete((int)$id);
        View::setFlash('success', 'Gallery showcase item deleted.');
        return $this->redirect('/admin/content/gallery');
    }

    public function toggleGallery(Request $request, string $id): Response
    {
        $item = GalleryItem::find((int)$id);
        if ($item) {
            $newActive = empty($item['is_active']) ? 1 : 0;
            GalleryItem::update((int)$id, ['is_active' => $newActive]);
            View::setFlash('success', 'Gallery item status updated.');
        }
        return $this->redirect('/admin/content/gallery');
    }

    // ==========================================
    // REVIEWS APPROVAL
    // ==========================================
    public function reviews(Request $request): Response
    {
        $reviews = Database::fetchAll(
            "SELECT r.*, u.name as customer_name, st.name as staff_name, s.name as service_name, b.booking_no
             FROM reviews r
             JOIN users u ON r.customer_id = u.id
             JOIN bookings b ON r.booking_id = b.id
             JOIN services s ON b.service_id = s.id
             LEFT JOIN users st ON r.staff_id = st.id
             ORDER BY r.id DESC"
        );

        return $this->render('admin.content.reviews', [
            'title'   => 'Customer Reviews Moderation | REFIXEL Admin',
            'reviews' => $reviews,
        ], 'admin');
    }

    public function approveReview(Request $request, string $id): Response
    {
        Review::update((int)$id, ['is_approved' => 1]);
        View::setFlash('success', 'Review approved for public display.');
        return $this->redirect('/admin/content/reviews');
    }

    public function deleteReview(Request $request, string $id): Response
    {
        Database::query("DELETE FROM reviews WHERE id = :id", ['id' => $id]);
        View::setFlash('success', 'Review deleted.');
        return $this->redirect('/admin/content/reviews');
    }

    // ==========================================
    // SERVICE AREAS
    // ==========================================
    public function areas(Request $request): Response
    {
        $areas = ServiceArea::all('city ASC, pincode ASC');
        return $this->render('admin.content.areas', [
            'title' => 'Service Areas & Pincodes | REFIXEL Admin',
            'areas' => $areas,
        ], 'admin');
    }

    public function storeArea(Request $request): Response
    {
        $city = trim((string)$request->input('city'));
        $pincode = trim((string)$request->input('pincode'));
        $name = trim((string)$request->input('name'));

        if (empty($city) || empty($pincode)) {
            View::setFlash('error', 'City and pincode are required.');
            return $this->redirect('/admin/content/areas');
        }

        ServiceArea::create([
            'city'      => $city,
            'pincode'   => $pincode,
            'area_name' => !empty($name) ? $name : "{$city} - {$pincode}",
            'is_active' => 1,
        ]);

        View::setFlash('success', "Service area {$city} ({$pincode}) added.");
        return $this->redirect('/admin/content/areas');
    }

    public function updateArea(Request $request, string $id): Response
    {
        $area = ServiceArea::find((int)$id);
        if (!$area) {
            View::setFlash('error', 'Service area not found.');
            return $this->redirect('/admin/content/areas');
        }

        $city = trim((string)$request->input('city'));
        $pincode = trim((string)$request->input('pincode'));
        $name = trim((string)$request->input('name'));

        if (empty($city) || empty($pincode)) {
            View::setFlash('error', 'City and pincode are required.');
            return $this->redirect('/admin/content/areas');
        }

        ServiceArea::update((int)$id, [
            'city'      => $city,
            'pincode'   => $pincode,
            'area_name' => !empty($name) ? $name : "{$city} - {$pincode}",
        ]);

        View::setFlash('success', "Service area updated.");
        return $this->redirect('/admin/content/areas');
    }

    public function deleteArea(Request $request, string $id): Response
    {
        ServiceArea::delete((int)$id);
        View::setFlash('success', 'Service area deleted.');
        return $this->redirect('/admin/content/areas');
    }

    public function toggleArea(Request $request, string $id): Response
    {
        $area = ServiceArea::find((int)$id);
        if ($area) {
            $newActive = empty($area['is_active']) ? 1 : 0;
            ServiceArea::update((int)$id, ['is_active' => $newActive]);
            View::setFlash('success', 'Service area status updated.');
        }
        return $this->redirect('/admin/content/areas');
    }

    // ==========================================
    // PROCESS STEPS
    // ==========================================
    public function steps(Request $request): Response
    {
        $steps = ProcessStep::all('step_no ASC');
        return $this->render('admin.content.steps', [
            'title' => 'Service Process Steps | REFIXEL Admin',
            'steps' => $steps,
        ], 'admin');
    }

    public function updateStep(Request $request, string $id): Response
    {
        $title = trim((string)$request->input('title'));
        $description = trim((string)$request->input('description'));

        if (!empty($title) && !empty($description)) {
            ProcessStep::update((int)$id, [
                'title'       => $title,
                'description' => $description,
            ]);
            View::setFlash('success', 'Process step updated.');
        }

        return $this->redirect('/admin/content/steps');
    }

    // ==========================================
    // CHECKLISTS (Inclusions / Exclusions)
    // ==========================================
    public function checklists(Request $request): Response
    {
        $serviceId = (int)$request->query('service_id', 0);
        $services = Service::all('name ASC');

        $where = $serviceId > 0 ? "WHERE c.service_id = :sid" : "";
        $params = $serviceId > 0 ? ['sid' => $serviceId] : [];

        $items = Database::fetchAll(
            "SELECT c.*, s.name as service_name
             FROM service_checklist_items c
             JOIN services s ON c.service_id = s.id
             {$where}
             ORDER BY s.name ASC, c.is_included DESC, c.sort_order ASC",
            $params
        );

        return $this->render('admin.content.checklists', [
            'title'            => 'Service Checklists | REFIXEL Admin',
            'items'            => $items,
            'services'         => $services,
            'currentServiceId' => $serviceId,
        ], 'admin');
    }

    public function storeChecklist(Request $request): Response
    {
        $serviceId = (int)$request->input('service_id');
        $label = trim((string)$request->input('label'));
        $isIncluded = (int)$request->input('is_included', 1);
        $sortOrder = (int)$request->input('sort_order', 0);

        if ($serviceId <= 0 || empty($label)) {
            View::setFlash('error', 'Service and checklist item label are required.');
            return $this->redirect('/admin/content/checklists');
        }

        Database::query(
            "INSERT INTO service_checklist_items (service_id, label, is_included, sort_order)
             VALUES (:sid, :label, :inc, :sort)",
            [
                'sid'   => $serviceId,
                'label' => $label,
                'inc'   => $isIncluded,
                'sort'  => $sortOrder,
            ]
        );

        View::setFlash('success', 'Checklist item added successfully.');
        return $this->redirect('/admin/content/checklists?service_id=' . $serviceId);
    }

    public function deleteChecklist(Request $request, string $id): Response
    {
        Database::query("DELETE FROM service_checklist_items WHERE id = :id", ['id' => $id]);
        View::setFlash('success', 'Checklist item deleted.');
        return $this->redirect('/admin/content/checklists');
    }
}

