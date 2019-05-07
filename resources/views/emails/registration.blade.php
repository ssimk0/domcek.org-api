@component('mail::message')

Milý/á {{ $userName }},

prihlásenie na {{ $eventName }} prebehlo úspešne. <br/>

@component('mail::panel')
Údaje pre platbu zálohy:

Banka: Tatrabanka

IBAN účtu: SK52 1100 0000 0029 4304 5043

Variabilný symbol: {{ $paymentNumber }} (tento VS je platný len a len pre teba a len na túto púť)

Suma: {{ $deposit }} EUR (viac info o príspevkoch nájdeš tu: https://domcek.org/pages/prispevky)

Správa pre prijímateľa: Príspevok na činnosť
@endcomponent

<pre style="color:black">
Viac info nájdeš vo svojom profile.

Vezmi si so sebou aj <strong>QR kod</strong> ktory najdes v prilohe emailu, alebo na stranke po prihlásení v sekcii "Moje prihlásenia"
a <strong>potvrdenie o platbe</strong> pre prípad, že systém nedokáže spárovať tvoju platbu.
Ak zaplatíš poštovou poukážkou, určite so sebou prines aj ústrižok (stáva sa, že platby cez poštu neprejdú ani za týždeň). Ak máš možnosť uhradiť účastnícky poplatok cez internet banking, využi prosím túto možnosť.

Ďakujeme za tvoje prihlásenie na púť a tešíme sa na teba.

V prípade, že sa z púte odhlásiš v termíne otvoreného prihlasovania, odoslaním mailu na <a href="mailto:registrácia@domcek.org">registrácia@domcek.org</a> môžeš požiadať o vrátenie vyplatenej zálohy.
V prípade, že chceš zmeniť svoje prihlásenie alebo sa chceš odhlásiť, môžeš tak urobiť na stráke v sekcii "Moje prihlásenia".
</pre>

<div style="margin-top: 40px;color:black" >
Pekný deň praje team,
</div>
Domček


------
###### V prípade akýchkoľvek problémov s používateľským kontom nás kontaktuj na podpora@domcek.org.
###### V prípade akýchkoľvek otázok súvisiacich s tvojou účasťou na podujatí nás kontaktuj na registracia@domček.org.
@endcomponent
