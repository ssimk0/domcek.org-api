<?php


namespace App\Http\Controllers\Secure;


use App\Constants\ErrorMessagesConstant;
use App\Http\Controllers\Controller;
use App\Services\EventService;
use Illuminate\Http\Request;

class EventController extends Controller
{
    private $service;

    public function __construct(EventService $service)
    {
        $this->service = $service;
    }

    public function create(Request $request)
    {
        $data = $this->validate($request, [
            'name' => 'required|string',
            'theme' => 'string',
            'type' => 'required|string',
            'prices' => 'required|array',
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d',
            'startRegistration' => 'required|date_format:Y-m-d',
            'endRegistration' => 'required|date_format:Y-m-d',
            'endVolunteerRegistration' => 'required|date_format:Y-m-d',
            'volunteerTypes' => 'required|array',
            'volunteerTypes.*' => 'required|integer',
            'transportTimesIn' => 'array',
            'transportTimesOut' => 'array',
        ]);

        $result = $this->service->createEvent($data);

        if ($result) {
            return $this->successResponse(201);
        }

        return ErrorMessagesConstant::badAttempt();

    }

    public function edit(Request $request, $eventId)
    {
        $data = $this->validate($request, [
            'name' => 'required|string',
            'theme' => 'string',
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d',
            'startRegistration' => 'required|date_format:Y-m-d',
            'endRegistration' => 'required|date_format:Y-m-d',
            'endVolunteerRegistration' => 'required|date_format:Y-m-d',
            'volunteerTypes' => 'array',
            'volunteerTypes.*' => 'integer',
            'transportTimesIn' => 'array',
            'transportTimesOut' => 'array',
        ]);

        $result = $this->service->editEvent($data, $eventId);

        if ($result) {
            return $this->successResponse(200);
        }

        return ErrorMessagesConstant::badAttempt();

    }

    public function list(Request $request)
    {
        $data = $this->validate($request, [
            'size' => 'integer',
            'filter' => 'string'
        ]);

        return $this->jsonResponse($this->service->eventList(
            array_get($data, 'size', 10),
            array_get($data, 'filter', '%')
        ));
    }

    public function availableEvents()
    {
        return $this->jsonResponse($this->service->availableEvents());
    }

    public function detail($eventId)
    {
        $event = $this->service->eventDetail($eventId);


        if ($event) {
            return $this->jsonResponse($event);
        }

        return ErrorMessagesConstant::notFound();
    }


    public function delete($eventId)
    {
        $result = $this->service->delete($eventId);


        if ($result) {
            return $this->successResponse();
        }

        return ErrorMessagesConstant::notFound();
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
}
