<?php


namespace App\Http\Controllers\Secure;


use App\Http\Controllers\Controller;
use App\Services\VolunteerService;
use Illuminate\Http\Request;

class VolunteerController extends Controller
{
    private $service;

    public function __construct(VolunteerService $service)
    {
        $this->service = $service;
    }

    // TODO: finish
    public function edit($volunteerId, Request $request)
    {
        $data = $this->validate($request, [
            'volunteerTypeId' => 'integer',
            'isLeader' => 'boolean'
        ]);

        $result = $this->service->edit($data, $volunteerId);

        if ($result) {
            return $this->successResponse();
        }
        return null;
    }

    // TODO: finish
    public function list($eventId)
    {
        $list = $this->service->volunteerList($eventId);

        $this->jsonResponse($list);
    }
    // TODO: finish
    public function detail($volunteerId)
    {
        $detail = $this->service->volunteerDetail($volunteerId);

        $this->jsonResponse($detail);
    }
}
