@component('mail::message')

Milý/á {{ $user->first_name }},

potvrdzujeme, že sme obdržali tvoju platbu vo výške: {{ $details['amount'] }} EUR.



<div style="margin-top: 40px">
Pekný deň praje team,
</div>
Domček

------
###### V prípade akýchkoľvek problémov s používateľským kontom nás kontaktuj na podpora@domcek.org.
###### V prípade akýchkoľvek otázok súvisiacich s tvojou účasťou na podujatí nás kontaktuj na registracia@domček.org.
@endcomponent