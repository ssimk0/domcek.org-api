@component('mail::message')
    # Reset Hesla

    Ahoj,
    zmenili sme tvoje heslo k učtu na domčeku tvoje nove heslo je : {{$password}}

    Pekný deň praje team,

    {{ config('app.name') }}

    ------
    ###### V prípade akychkoľvek problémov nás kontakuj na podpora@domcek.org
@endcomponent
