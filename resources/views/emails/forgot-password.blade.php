@component('mail::message')
# Reset Hesla

Ahoj,

dostali sme požiadavku na resetovanie tvojho hesla. Ak si želáš pokračovať, klikni na

@component('mail::button', ['url' => $url, 'color' => 'primary'])
    Reset Hesla
@endcomponent

<div style="margin-top: 40px">
Pekný deň praje team,
</div>
Domček

------
###### V prípade, ak si nežiadal o resetovanie hesla, môžeš tento e-mail ignorovať.
###### V prípade akýchkoľvek problémov s používateľským kontom nás kontaktuj na podpora@domcek.org.
@endcomponent
