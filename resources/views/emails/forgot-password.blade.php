@component('mail::message')
    # Reset Hesla

    Ahoj,
    dostali sme požiadavku na resetovanie tvojho hesla ak si želáš pokračovať klikni na

    @component('mail::button', ['url' => $url])
        Reset Hesla
    @endcomponent

    Pekný deň praje team,

    {{ config('app.name') }}

------
######V prípade ak si nežiadal o resetovanie hesla môžeš tento email ignorovať
@endcomponent
