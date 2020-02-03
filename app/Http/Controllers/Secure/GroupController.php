<?php


namespace App\Http\Controllers\Secure;

use App\Constants\ErrorMessagesConstant;
use App\Http\Controllers\Controller;
use App\Services\GroupService;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    private $service;

    public function __construct(GroupService $service)
    {
        $this->service = $service;
    }

    public function eventGroups($eventId)
    {
        return $this->jsonResponse($this->service->eventGroups($eventId));
    }

    public function generateGroups(Request $request, $eventId)
    {
        $data = $this->validate($request, [
            'groupsCount' => 'required|integer'
        ]);

        $result = $this->jsonResponse($this->service->generateGroups($eventId, $data));

        if ($result) {
            return $this->successResponse();
        }

        return ErrorMessagesConstant::badAttempt();
    }

    public function AssignAnimator(Request $request, $eventId)
    {
        $data = $this->validate($request, [
            'groupName' => 'required|integer',
            'userId' => 'required|integer'
        ]);

        $result = $this->service->assignAnimator($eventId, $data['groupName'], $data['userId']);
        if ($result) {
            return $this->successResponse();
        }

        return ErrorMessagesConstant::badAttempt();
    }
}
