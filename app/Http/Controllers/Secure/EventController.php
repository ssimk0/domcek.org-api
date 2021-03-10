<?php

namespace App\Http\Controllers\Secure;

use App\Constants\ErrorMessagesConstant;
use App\Http\Controllers\Controller;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response;

class EventController extends Controller
{
    private $service;

    public function __construct(EventService $service)
    {
        $this->service = $service;
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'theme' => 'nullable|string',
            'type' => 'required|string',
            'prices' => 'required|array',
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d',
            'startRegistration' => 'required|date_format:Y-m-d',
            'endRegistration' => 'required|date_format:Y-m-d',
            'endVolunteerRegistration' => 'required|date_format:Y-m-d',
            'volunteerTypes' => 'required|array',
            'volunteerTypes.*' => 'required|integer',
            'transportTimesIn' => 'nullable|array',
            'transportTimesOut' => 'nullable|array',
        ]);

        $result = $this->service->createEvent($data);

        if ($result) {
            return $this->successResponse(201);
        }

        return ErrorMessagesConstant::badAttempt();
    }

    public function edit(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'theme' => 'nullable|string',
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d',
            'startRegistration' => 'required|date_format:Y-m-d',
            'endRegistration' => 'required|date_format:Y-m-d',
            'endVolunteerRegistration' => 'required|date_format:Y-m-d',
            'volunteerTypes' => 'nullable|array',
            'volunteerTypes.*' => 'nullable|integer',
            'transportTimesIn' => 'nullable|array',
            'transportTimesOut' => 'nullable|array',
        ]);

        $result = $this->service->editEvent($data, $id);

        if ($result) {
            return $this->successResponse(200);
        }

        return ErrorMessagesConstant::badAttempt();
    }

    public function list(Request $request)
    {
        $data = $request->validate([
            'size' => 'nullable|integer',
            'filter' => 'nullable|string',
        ]);

        return $this->jsonResponse($this->service->eventList(
            Arr::get($data, 'size', 10),
            Arr::get($data, 'filter', '%')
        ));
    }

    public function availableEvents()
    {
        return $this->jsonResponse($this->service->availableEvents());
    }

    public function detail($id)
    {
        $event = $this->service->eventDetail($id);

        if ($event) {
            return $this->jsonResponse($event);
        }

        return ErrorMessagesConstant::notFound();
    }

    public function statsFile($id)
    {
        $content = $this->service->detailedStatsFile($id);

        $headers = ['Content-type'=>'text/plain',
            'Content-Disposition'=>sprintf('attachment; filename="%s"', 'stats.txt'),
        ];

        return Response::make($content, 200, $headers);
    }

    public function delete($id)
    {
        $result = $this->service->delete($id);

        if ($result) {
            return $this->successResponse();
        }

        return ErrorMessagesConstant::notFound();
    }
}
