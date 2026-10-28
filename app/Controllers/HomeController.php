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
            'title'         => 'REFIXEL: Best Home Services in Kashipur, Jaspur & Thakurdwara | Cleaning, AC Repair',
            'description'   => 'REFIXEL offers the best doorstep home services in Kashipur, Jaspur & Thakurdwara. Verified pros for deep cleaning, AC jet service, plumbing, electrician, painting & pest control. Book Now!',
            'keywords'      => 'home services Kashipur, home repair Jaspur, cleaning services Thakurdwara, electrician Kashipur, plumber Jaspur, AC service Thakurdwara, pest control Kashipur, painting Jaspur, false ceiling, refixel',
            'canonicalUrl'  => \App\Core\View::fullUrl('/'),
            'ogTitle'       => 'REFIXEL: Best Home Services in Kashipur, Jaspur & Thakurdwara | Cleaning, AC Repair',
            'ogDescription' => 'REFIXEL offers the best doorstep home services in Kashipur, Jaspur & Thakurdwara. Verified pros for deep cleaning, AC jet service, plumbing, electrician, painting & pest control. Book Now!',
            'ogImage'       => \App\Core\View::fullUrl('assets/img/refixel-logo-horizontal.png'),
            'ogUrl'         => \App\Core\View::fullUrl('/'),
            'categories'    => $categories,
            'faqs'          => $faqs,
            'reviews'       => $reviews,
            'cities'        => $cities,
        ], 'customer');
    }
}

