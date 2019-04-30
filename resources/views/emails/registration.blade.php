@component('mail::message')

Mily/a {{ $userName }},

prihlasenie na {{ $eventName }} prebehlo uspesne. <br/>

@component('mail::panel')
Stav prihlasenia je:

Udaje pre platbu zalohy (POZOR! Zmena c. uctu!):

Banka: Tatrabanka

IBAN uctu: SK52 1100 0000 0029 4304 5043

Variabilny symbol: {{ $paymentNumber }} (tento VS je platny len a len pre teba a len na tuto put)

Suma: {{ $deposit }} EUR (viac info o prispevkoch tu: https://domcek.org/pages/prispevky)

Sprava pre prijimatela: Prispevok na cinnost
@endcomponent

<pre style="color:black">
Viac info najdeš vo svojom profile

Vezmi si so sebou aj potvrdenie o platbe pre pripad, ze system nedokaze sparovat tvoju platbu.
Ak zaplatis postovou poukazkou tesne pred uvedenym terminom, urcite si vezmi ustrizok (stava sa, ze platby cez postu neprejdu ani za tyzden). Ak mas moznost poslat peniaze cez ucet, vyuzi tuto formu. Platby postovou poukazkou musime parovat rucne.

Dakujeme za tvoje prihlasenie na akciu a tesime sa na teba.
V pripade, ze chces zmenit svoje prilasenie alebo sa odhlásiť, odpis na tento e-mail.
</pre>

<div style="margin-top: 40px;color:black" >
    Pekný deň praje team,
</div>
{{ config('app.name') }}


------
###### V prípade akychkoľvek problémov nás kontakuj na podpora@domcek.org
@endcomponent