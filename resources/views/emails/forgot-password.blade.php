@component('mail::message')
    # Reset Hesla

Ahoj,

dostali sme požiadavku na resetovanie tvojho hesla ak si želáš pokračovať klikni na

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Reset Hesla
@endcomponent

<div style="margin-top: 40px">
    Pekný deň praje team,
</div>
{{ config('app.name') }}

------
###### V prípade ak si nežiadal o resetovanie hesla môžeš tento email ignorovať
###### V prípade akychkoľvek problémov nás kontakuj na podpora@domcek.org
@endcomponent
