<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Review;
use App\Models\ServiceArea;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        $categories = Category::getActive();
        $faqs = Faq::getGlobal();
        $reviews = Review::getApproved();
        $cities = ServiceArea::getActiveCities();

        return $this->render('customer.home', [
            'title'      => 'Professional Home and Commercial Services | Primodomus',
            'categories' => $categories,
            'faqs'       => $faqs,
            'reviews'    => $reviews,
            'cities'     => $cities,
        ], 'customer');
    }
}
