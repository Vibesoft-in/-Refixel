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
            'title'         => 'Home Cleaning, AC Repair, Plumber & Electrician in Kashipur, Jaspur | REFIXEL',
            'description'   => 'Book verified home service professionals in Kashipur, Jaspur & Thakurdwara. Deep cleaning, AC jet service, plumbing, electrician, painting & pest control. Upfront pricing. Book Now!',
            'canonicalUrl'  => \App\Core\View::fullUrl('/'),
            'ogTitle'       => 'Home Cleaning, AC Repair, Plumber & Electrician in Kashipur, Jaspur | REFIXEL',
            'ogDescription' => 'Book verified home service professionals in Kashipur, Jaspur & Thakurdwara. Deep cleaning, AC jet service, plumbing, electrician, painting & pest control. Upfront pricing. Book Now!',
            'ogImage'       => \App\Core\View::fullUrl('assets/img/refixel-logo-horizontal.png'),
            'ogUrl'         => \App\Core\View::fullUrl('/'),
            'categories'    => $categories,
            'faqs'          => $faqs,
            'reviews'       => $reviews,
            'cities'        => $cities,
        ], 'customer');
    }
}

