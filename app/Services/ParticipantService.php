<?php

namespace App\Services;

use App\Mails\RegistrationMail;
use App\Repositories\EventRepository;
use App\Repositories\GroupRepository;
use App\Repositories\ParticipantRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\VolunteersRepository;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Exception;
use function GuzzleHttp\json_encode;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ParticipantService extends Service
{
    private $REGISTRATION_FEE = 5;

    private $repository;
    private $volunteersRepository;
    private $paymentRepository;
    private $eventRepository;
    private $groupRepository;

    public function __construct(
        ParticipantRepository $repository,
        VolunteersRepository $volunteersRepository,
        PaymentRepository $paymentRepository,
        EventRepository $eventRepository,
        GroupRepository $groupRepository
    ) {
        $this->repository = $repository;
        $this->eventRepository = $eventRepository;
        $this->paymentRepository = $paymentRepository;
        $this->volunteersRepository = $volunteersRepository;
        $this->groupRepository = $groupRepository;
    }

    public function list($eventId, $filters)
    {
        return $this->repository->list($eventId, $filters);
    }

    public function registrationList($eventId)
    {
        return $this->repository->registrationList($eventId);
    }

    public function edit(array $data, $eventId, $participantId)
    {
        $userId = Arr::get($data, 'userId');
        $paid = Arr::get($data, 'paid');
        $adminNote = Arr::get($data, 'adminNote', '');
        $isLeader = Arr::get($data, 'isLeader', false);
        $volunteerTypeId = Arr::get($data, 'volunteerTypeId');
        $group = Arr::get($data, 'group_name');

        try {
            if (! empty($paid)) {
                $this->paymentRepository->edit($userId, $eventId, $paid);
            }

            if (! empty($volunteerTypeId)) {
                $data = [
                    'volunteer_type_id' => $volunteerTypeId,
                    'is_leader' => $isLeader,
                ];
                $this->volunteersRepository->editByUserAndEventId($data, $userId, $eventId);
            } else {
                $this->volunteersRepository->deleteIfExist($userId, $eventId);
            }

            if (! empty($group)) {
                $this->groupRepository->editGroupByParticipantAndEventId($group, $participantId, $eventId);
            } else {
                $this->groupRepository->deleteGroupByParticipantAndEventId($participantId, $eventId);
            }

            $this->repository->edit($participantId, $this->userId(), [
                'admin_note' => $adminNote,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logError('Problem with editing participant with error: '.$e);
        }
    }

    public function userEvents()
    {
        try {
            return $this->repository->userEvents($this->userId());
        } catch (\Exception $e) {
            $this->logError('Problem with getting participant detail with error: '.$e);
        }

        return [];
    }

    public function create(array $data, $eventId)
    {
        $volunteerTypeId = Arr::get($data, 'volunteerTypeId');

        try {
            $event = $this->eventRepository->detail($eventId);
            $eventPrice = $this->eventRepository->eventPriceById($data['priceId'], $eventId);
            $now = Carbon::now();
            $isVolunteer = $volunteerTypeId && $now <= Carbon::parse($event->end_volunteer_registration);

            if ($now >= Carbon::parse($event->start_date)) {
                return false;
            }

            $this->createParticipant($data, $eventId);

            if ($isVolunteer) {
                $this->createVolunteer($volunteerTypeId, $eventId);
            }

            $needPay = $eventPrice->need_pay;

            if ($now >= Carbon::parse($event->end_registration)) {
                // if you register after end of registration you need pay 5 euro fee
                $needPay += $this->REGISTRATION_FEE;
            }
            $paymentNumber = $this->createPayment($needPay, $eventId, $data['priceId']);

            $user = Auth::user();
            $profile = $user->profile()->first();
            $qrCodePath = '/tmp/'.Str::random().'.png';
            $this->generateQrCode($eventId, $user->id, $qrCodePath);

            Mail::to($user->email)->send(new RegistrationMail(
                $eventPrice->deposit,
                $eventPrice->need_pay,
                $profile->first_name,
                $profile->birth_date,
                $paymentNumber,
                $event->name,
                'https://domcek.org/login?next=/user/registrations',
                $qrCodePath,
                $isVolunteer
            ));

            return true;
        } catch (\Exception $e) {
            $this->logError('Problem with creating participant with error: '.$e);
        }

        return false;
    }

    public function unsubscribe($eventId, $userId = null)
    {
        try {
            $this->repository->unsubscribeToEvent($eventId, $userId ?? $this->userId());
        } catch (\Exception $e) {
            $this->logError('Problem with unsubscribe to event with error: '.$e);

            return false;
        }

        return true;
    }

    public function subscribe($eventId, $userId = null)
    {
        try {
            $this->repository->resubscribeToEvent($eventId, $userId ?? $this->userId());
        } catch (Exception $e) {
            $this->logError('Problem with subscribe to event with error: '.$e);

            return false;
        }

        return true;
    }

    public function userEdit($data, $eventId)
    {
        try {
            $this->repository->userEdit([
                'note' => Arr::get($data, 'note'),
                'transport_in' => Arr::get($data, 'transportIn'),
                'transport_out' => Arr::get($data, 'transportOut'),
            ], $this->userId(), $eventId);
        } catch (Exception $e) {
            $this->logError('Problem with user edit participant detail with error: '.$e);

            return false;
        }

        return true;
    }

    public function adminDetail($eventId, $userId)
    {
        try {
            return $this->repository->detail($eventId, $userId);
        } catch (Exception $e) {
            $this->logError('Problem with getting participant detail with error: '.$e);
        }

        return false;
    }

    public function getUserPaymentNumber($eventId, $userId)
    {
        return $this->paymentRepository->findByUserIdAndEventId($eventId, $userId)->payment_number;
    }

    public function generateQrCode($eventId, $userId, $path)
    {
        $paymentNumber = $this->getUserPaymentNumber($eventId, $userId);
        $qrCode = new QrCode(base64_encode($paymentNumber));
        $qrCode->setEncoding('UTF-8');
        $qrCode->setWriterByName('png');
        $qrCode->setSize(300);

        $qrCode->writeFile($path);
    }

    public function detailedRegistrationList($eventId)
    {
        $participants = $this->repository->detailedRegistrationList($eventId);
        $notMatchedPayments = $this->paymentRepository->getNotMatchedPaymentForEvent($eventId);

        return [
            'participants' => $participants,
            'wrong-payments' => $notMatchedPayments,
        ];
    }

    public function sync($data, $eventId)
    {
        $eventPrice = $this->eventRepository->eventPrices([$eventId]);
        if (! empty($eventPrice)) {
            $eventPrice = $eventPrice[0];
        }
        foreach (Arr::get($data, 'participants', []) as $user) {
            if (Arr::get($user, 'was_on_event', null)) {
                try {
                    $transport = Arr::get($user, 'transport_out', null);
                    $userId = $user['user_id'];
                    $payedOnRegistration = $user['on_registration'];
                    // registered before event
                    if (Arr::get($user, 'payment_number', false)) {
                        $this->repository->registerUser($userId, $eventId, $transport, $payedOnRegistration);
                    } else {
                        $exist = $this->repository->participantExist($eventId, $userId);
                        if (! $exist) {
                            // register on event
                            $this->createParticipant([
                                'user_id' => $userId,
                                'transportOut' => $transport,
                                'admin_note' => 'Prihlasený na púti',
                            ], $eventId, true);
                            $this->createPayment($payedOnRegistration, $eventId, empty($eventPrice) ? null : $eventPrice->id, $payedOnRegistration, $userId);
                        } else {
                            $this->repository->registerUser($userId, $eventId, $transport, $payedOnRegistration);
                        }
                    }
                } catch (\Exception $e) {
                    $this->logError('Problem with register user ' + json_encode($user));
                }
            }
        }

        foreach (Arr::get($data, 'wrong-payments', []) as $payment) {
            $userId = Arr::get($payment, 'user_id', false);
            if ($userId) {
                try {
                    $this->paymentRepository->edit($userId, $eventId, $payment['amount']);
                    $this->paymentRepository->deleteWrongPaymentById($payment['id']);
                } catch (\Exception $e) {
                    $this->logError('Problem with edit payment ' + $payment['id'] + 'for user ' + $payment['user_id']);
                }
            }
        }

        return true;
    }

    private function createParticipant($data, $eventId, $wasOnEvent = false)
    {
        $this->repository->create([
            'admin_note' => Arr::get($data, 'admin_note', ''),
            'note' => Arr::get($data, 'note', ''),
            'transport_in' => Arr::get($data, 'transportIn', null),
            'transport_out' => Arr::get($data, 'transportOut', null),
            'user_id' => Arr::get($data, 'user_id', false) ? $data['user_id'] : $this->userId(),
            'event_id' => $eventId,
            'was_on_event' => $wasOnEvent,
            'want_to_be_animator_on_pz' => Arr::get($data, 'wantBeAnimatorOnPZ', null),
        ]);
    }

    private function createVolunteer($volunteerTypeId, $eventId, $wasOnEvent = false)
    {
        $this->volunteersRepository->create([
            'volunteer_type_id' => $volunteerTypeId,
            'event_id' => $eventId,
            'user_id' => $this->userId(),
            'was_on_event' => $wasOnEvent,
        ]);
    }

    private function createPayment($needPay, $eventId, $priceId, $onReg = 0, $userId = false)
    {
        $paymentNumber = $this->paymentRepository->generatePaymentNumber();

        $this->paymentRepository->create([
            'user_id' => $userId ? $userId : $this->userId(),
            'payment_number' => $paymentNumber,
            'event_price_id' => $priceId,
            'paid' => 0,
            'on_registration' => $onReg,
            'need_pay' => $needPay,
            'event_id' => $eventId,
        ]);

        return $paymentNumber;
    }

    public function getNameplateDetail($eventId)
    {
        $volunteers = $this->volunteersRepository->getNameplateDetail($eventId)->toArray();
        $participants = $this->repository->getNameplateDetail($eventId)->toArray();

        return [
            'volunteers' => $this->getPagedNameplatesData($volunteers),
            'participants' => $this->getPagedNameplatesData($participants),
        ];
    }

    private function getPagedNameplatesData($participants)
    {
        $pages = [];
        $allpages = ceil(count($participants) / 9);
        foreach (range(0, $allpages - 1) as $page_num) {
            $from = $page_num * 9;
            if ($page_num == $allpages) {
                $pages[$page_num] = array_slice($participants, $from);
            } else {
                $pages[$page_num] = array_slice($participants, $from, 9);
            }
        }

        return $pages;
    }
}
