<?php


namespace App\Http\Controllers\Secure;

use App\Constants\ErrorMessagesConstant;
use App\Http\Controllers\Controller;
use App\Services\ParticipantService;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ParticipantController extends Controller
{
    private $service;

    public function __construct(ParticipantService $service)
    {
        $this->service = $service;
    }

    // Register user to event
    public function register(Request $request, $eventId)
    {
        $data = $this->validate($request, [
            'volunteerTypeId' => 'integer',
            'note' => 'string',
            'transportIn' => 'required|string',
            'transportOut' => 'required|string',
            'audioVisualKnowledgeAgreement' => 'required|accepted',
            'GDPRRegistration' => 'required|accepted',
            'priceId' => 'required|integer',
            'wantBeAnimatorOnPZ' => 'boolean'
        ]);

        $result = $this->service->create($data, $eventId);

        if ($result) {
            return $this->successResponse(201);
        }

        return ErrorMessagesConstant::badAttempt();
    }

    public function userEdit(Request $request, $eventId)
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
    public function subscribe(Request $request, $eventId)
    {
        $result = $this->service->subscribe($eventId);
 
        if ($result) {
            return $this->successResponse();
        }
 
        return ErrorMessagesConstant::badAttempt();
    }

    // Unsubscribe user from event after registration
    public function unsubscribe(Request $request, $eventId)
    {
        $result = $this->service->unsubscribe($eventId);

        if ($result) {
            return $this->successResponse();
        }

        return ErrorMessagesConstant::badAttempt();
    }

    // Admin unsubscribe user from event after registration
    public function adminUnsubscribe(Request $request, $userId, $eventId)
    {
        $result = $this->service->unsubscribe($eventId, $userId);

        if ($result) {
            return $this->successResponse();
        }

        return ErrorMessagesConstant::badAttempt();
    }

    // Admin unsubscribe user from event after registration
    public function adminSubscribe(Request $request, $userId, $eventId)
    {
        $result = $this->service->subscribe($eventId, $userId);

        if ($result) {
            return $this->successResponse();
        }

        return ErrorMessagesConstant::badAttempt();
    }

    public function eventQRCode(Request $request, $eventId)
    {
        $userId = $request->user()->id;
        $path = storage_path()."/app/qrcodes/".Str::random(40).".png";
        $this->service->generateQrCode($eventId, $userId, $path);
        $headers = ['Content-Type' => "image/png"];
        $response = new BinaryFileResponse($path, 200, $headers);
        ob_end_clean();
        return $response;
    }

    public function userEvents()
    {
        $detail = $this->service->userEvents();

        if ($detail) {
            return $this->jsonResponse($detail);
        }

        return ErrorMessagesConstant::notFound();
    }

    public function adminDetail($eventId, $userId)
    {
        $detail = $this->service->adminDetail($eventId, $userId);

        if ($detail) {
            return $this->jsonResponse($detail);
        }

        return ErrorMessagesConstant::notFound();
    }

    public function sync(Request $request)
    {
        $data = $this->validate($request, [
            'participants' => 'array',
            'wrong-payments' => 'array'
        ]);

        $result = $this->service->sync($data, $request->event_id);

        if ($result) {
            return $this->successResponse();
        }

        return ErrorMessagesConstant::badAttempt();
    }

    public function edit(Request $request, $participantId, $eventId)
    {
        $data = $this->validate($request, [
            'volunteerTypeId' => 'integer',
            'paid' => 'integer',
            'adminNote' => 'string',
            'group_name' => 'integer',
            'userId' => 'integer',
            'isLeader' => 'bool',
        ]);

        $result = $this->service->edit($data, $eventId, $participantId);

        if ($result) {
            return $this->successResponse();
        }

        return ErrorMessagesConstant::badAttempt();
    }

    public function list(Request $request, $eventId)
    {
        $filters = $this->validate($request, [
            'filter' => 'string',
            'volunteer' => 'string',
            'sortBy' => 'string',
            'sortDesc' => 'string',
            'group' => 'string',
            'type' => 'string',
            'variant' => 'integer',
        ]);

        $list = $this->service->list($eventId, $filters);

        return $this->jsonResponse($list, 200, 0);
    }

    public function registrationList(Request $request)
    {
        $eventId = $request->event_id;
        $list = $this->service->registrationList($eventId);

        return $this->jsonResponse($list);
    }

    public function detailedRegistrationList(Request $request)
    {
        $eventId = $request->event_id;
        $list = $this->service->detailedRegistrationList($eventId);

        return $this->jsonResponse($list);
    }


    public function generateNameplates(Request $request, $eventId)
    {
        $data = $this->validate($request, [
            'image' => ['required', 'regex:(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})'],
            'image_back' => ['required', 'regex:(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})'],
            'image_volunteer' => ['required', 'regex:(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})'],
            'image_volunteer_back' => ['required', 'regex:(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})']
        ]);

        $result = $this->service->getNameplateDetail($eventId);

        $pdf = PDF::loadView('participants.nameplates', array_merge($data, $result))->setPaper('a4')->setOrientation('landscape');
        return $pdf->download("menovky-$eventId.pdf");
    }
}
