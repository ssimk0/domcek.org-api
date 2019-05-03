@component('mail::message')
# Reset Hesla

Ahoj,

zmenili sme tvoje heslo k používateľskému kontu na stránke www.domcek.org. Tvoje nové heslo je: {{$password}}

Pekný deň praje team,

{{ config('app.name') }}

------
###### V prípade akýchkoľvek problémov s používateľským kontom nás kontaktuj na podpora@domcek.org.
@endcomponent
