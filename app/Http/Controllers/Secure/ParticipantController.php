<?php


namespace App\Http\Controllers\Secure;


use App\Constants\ErrorMessagesConstant;
use App\Http\Controllers\Controller;
use App\Services\ParticipantService;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    private $service;

    public function __construct(ParticipantService $service)
    {
        $this->service = $service;
    }

    function create(Request $request, $eventId)
    {
        $data = $this->validate($request, [
            'volunteerTypeId' => 'integer',
            'note' => 'string'
        ]);

        $result = $this->service->create($data, $eventId);

        if ($result) {
            return $this->successResponse(201);
        }

        return ErrorMessagesConstant::badAttempt();
    }

    function detail($eventId)
    {
        $detail = $this->service->detail($eventId);

        if ($detail) {
            return $this->jsonResponse($detail);
        }

        return ErrorMessagesConstant::notFound();
    }

    function adminDetail($eventId, $userId)
    {
        $detail = $this->service->adminDetail($eventId, $userId);

        if ($detail) {
            return $this->jsonResponse($detail);
        }

        return ErrorMessagesConstant::notFound();
    }

    function edit(Request $request, $eventId, $participantId)
    {
        $data = $this->validate($request, [
            'volunteerTypeId' => 'integer',
            'registrationUserId' => 'integer',
            'paid' => 'integer',
            'userId' => 'integer',
            'isLeader' => 'bool',
        ]);

        $result = $this->service->edit($data, $eventId, $participantId);

        if ($result) {
            return $this->successResponse();
        }

        return ErrorMessagesConstant::badAttempt();
    }

    function list($eventId)
    {
        $list = $this->service->list($eventId);

        return $this->jsonResponse($list);
    }
}
