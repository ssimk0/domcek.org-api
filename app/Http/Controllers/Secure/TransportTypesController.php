<?php
namespace App\Http\Controllers\Secure;

use App\Http\Controllers\Controller;
use App\Services\EventService;

class TransportTypesController extends Controller
{
    private $service;

    public function __construct(EventService $service)
    {
        $this->service = $service;
    }

    public function typesList()
    {
        $types = $this->service->getTransportTypes();

        return $this->jsonResponse($types);
    }
}
