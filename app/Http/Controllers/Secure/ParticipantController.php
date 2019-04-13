<?php


namespace App\Http\Controllers\Secure;


use App\Constants\ErrorMessagesConstant;
use App\Http\Controllers\Controller;
use App\Services\ParticipantService;
use Endroid\QrCode\QrCode;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ParticipantController extends Controller
{
    private $service;

    public function __construct(ParticipantService $service)
    {
        $this->service = $service;
    }

    // Register user to event
    function register(Request $request, $eventId)
    {
        $data = $this->validate($request, [
            'volunteerTypeId' => 'integer',
            'note' => 'string',
            'transportIn' => 'required|string',
            'transportOut' => 'required|string'
        ]);

        $result = $this->service->create($data, $eventId);

        if ($result) {
            return $this->successResponse(201);
        }

        return ErrorMessagesConstant::badAttempt();
    }

    function userEdit(Request $request, $eventId)
    {
        $data = $this->validate($request, [
            'note' => 'string',
            'transportIn' => 'required|string',
            'transportOut' => 'required|string'
        ]);

        $result = $this->service->userEdit($data, $eventId);

        if ($result) {
            return $this->successResponse();
        }

        return ErrorMessagesConstant::badAttempt();
    }

     // Subscribe user to event after unsubscribe
     function subscribe(Request $request, $eventId)
     {
         $result = $this->service->subscribe($eventId);
 
         if ($result) {
             return $this->successResponse();
         }
 
         return ErrorMessagesConstant::badAttempt();
     }

    // Unsubscribe user from event after registration
    function unsubscribe(Request $request, $eventId)
    {
        $result = $this->service->unsubscribe($eventId);

        if ($result) {
            return $this->successResponse();
        }

        return ErrorMessagesConstant::badAttempt();
    }

    function eventQRCode(Request $request, $eventId)
    {
        $userId = $request->user()->id;
        $path = "/tmp/qr-$userId.png";
        $qrCode = new QrCode("secure/registration/events/$eventId/participants/$userId");
        $qrCode->setSize(300);

        $qrCode->writeFile($path);

        $type = 'image/png';
        $headers = ['Content-Type' => $type];
        $response = new BinaryFileResponse($path, 200 , $headers);
        return $response;
    }

    function userEvents()
    {
        $detail = $this->service->userEvents();

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

    function edit(Request $request, $participantId, $eventId)
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

    function registrationList($eventId) {
        $list = $this->service->registrationList($eventId);

        return $this->jsonResponse($list);
    }
}
