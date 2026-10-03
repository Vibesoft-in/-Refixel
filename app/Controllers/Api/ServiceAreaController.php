<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Service;
use App\Models\ServiceArea;

class ServiceAreaController extends Controller
{
    public function checkPincode(Request $request): Response
    {
        $pincode = trim((string)$request->input('pincode', ''));
        $served = ServiceArea::isPincodeServed($pincode);

        return $this->json([
            'status'  => 'success',
            'pincode' => $pincode,
            'served'  => $served,
        ]);
    }

    public function search(Request $request): Response
    {
        $q = trim((string)$request->input('q', ''));
        if (mb_strlen($q) < 2) {
            return $this->json(['status' => 'success', 'results' => []]);
        }

        $results = Service::search($q, 8);
        return $this->json(['status' => 'success', 'results' => $results]);
    }
}
