<?php

namespace App\Console\Commands;

use App\Mails\InvitationMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEmails extends Command
{
    protected $signature = 'send:email';

    protected $name = 'send email';

    public function handle()
    {
        $when = Carbon::now();
        $users = User::with('profile')->get();
        foreach ($users as $user) {
            $when = $when->addSeconds(30);
            $mail = new InvitationMail();
            Mail::to($user->email)
                ->later($when, $mail);
        }
    }
}
