@component('mail::message')
# Overenie emailu

Ahoj,

toto je posledný krok k dokončeniu tvojej registrácie. Ak si želáš pokračovať, klikni na

@component('mail::button', ['url' => $url, 'color' => 'primary'])
    Overiť email
@endcomponent

<div style="margin-top: 40px">
Pekný deň praje team,
</div>
Domček

------
###### V prípade, ak si sa neregistroval na stránku https://domcek.org, môžeš tento e-mail ignorovať.
###### V prípade akýchkoľvek problémov s používateľským kontom nás kontaktuj na podpora@domcek.org.
@endcomponent
