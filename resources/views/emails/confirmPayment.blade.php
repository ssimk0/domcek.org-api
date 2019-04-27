@component('mail::message')
    Mily/a {{ $user->first_name }},

    Potvrdzujeme že sme obdržali tvoju platbu vo výške: {{ $details['amount'] }}

    Registračný tím Domčeka

    ------
    ###### V prípade akychkoľvek problémov nás kontakuj na podpora@domcek.org
@endcomponent