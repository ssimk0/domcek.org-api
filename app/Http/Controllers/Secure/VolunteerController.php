<?php


namespace App\Http\Controllers\Secure;


use App\Constants\ErrorMessagesConstant;
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

    public function edit(Request $request, $volunteerId)
    {
        $data = $this->validate($request, [
            'volunteerTypeId' => 'integer',
            'isLeader' => 'boolean'
        ]);

        $result = $this->service->editVolunteer($data, $volunteerId);

        if ($result) {
            return $this->successResponse();
        }
        return ErrorMessagesConstant::badAttempt();
    }


    public function list($eventId)
    {
        $list = $this->service->volunteerList($eventId);

        return $this->jsonResponse($list);
    }

    public function detail($volunteerId)
    {
        $detail = $this->service->volunteerDetail($volunteerId);

        if ($detail) {
            return $this->jsonResponse($detail);
        }

        return ErrorMessagesConstant::notFound();
    }

    public function types()
    {
        $types = $this->service->volunteerTypes();

        if ($types) {
            return $this->jsonResponse($types);
        }

        return ErrorMessagesConstant::notFound();
    }
}