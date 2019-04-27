@component('mail::message')

Mily/a {{ $user->first_name }},

Potvrdzujeme že sme obdržali tvoju platbu vo výške: {{ $details['amount'] }} EUR



<div style="margin-top: 40px">
Pekný deň praje team,
</div>
{{ config('app.name') }}

------
###### V prípade akychkoľvek problémov nás kontakuj na podpora@domcek.org
@endcomponent