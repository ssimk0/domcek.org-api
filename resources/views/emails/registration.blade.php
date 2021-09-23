@component('mail::message')

Milý/á {{ $userName }},

prihlásenie na {{ $eventName }} prebehlo úspešne. <br/>
### !!! Až keď zaplatíš a máš potvrdenú platbu mailom si oficiálne prihlásený. !!!

@component('mail::panel')
Údaje pre platbu zálohy:

Banka: Tatrabanka

IBAN účtu: SK52 1100 0000 0029 4304 5043

Variabilný symbol: {{ $paymentNumber }} (tento VS je platný len a len pre teba a len na túto púť)

Účastnícky poplatok: {{ $price }} EUR
Záloha: {{ $deposit }} EUR (viac info o príspevkoch nájdeš tu: https://domcek.org/page/pute/prihlasovanie-6)

Správa pre prijímateľa: Príspevok na činnosť
@endcomponent

<pre style="color:black">
Zálohu, resp. účastnícky poplatok je potrebné uhradiť najneskôr týždeň pred podujatím. Viac informácií nájdeš vo svojom profile.

Prosím, ulož si do mobilu <strong>QR kód</strong>, ktorý nájdeš v prílohe tohto e-mailu (alebo po prihlásení na stránke <a href="https://domcek.org">www.domcek.org</a>, v sekcii "Moje prihlásenia").

Z dôvodu pretrvávajúcich pandemických opatrení je púť <strong>LEN PRE PLNE ZAOČKOVANÝCH</strong>,  nezabudni si so sebou vziať <strong>COVID PASS.</strong>
Ak si ešte <strong>nedovŕšil 18 rokov</strong> nezabudni na <strong>potvrdenie od rodiča</strong>, ktoré ti tu prikladáme.
Taktiež si nezabudni zobrať <strong>spacák a prezuvky.</strong>
<strong>Celé stretnutie</strong> bude prebiehať vo <strong>Vysokej nad Uhom.</strong>
<strong>Strava</strong> bude zabezpečená nasledovne:

@component('mail::table')
|         | Raňajky | Obed  | Večera             |
| ------- |:-------:|:-----:| ------------------:|
| Piatok  | -       | -     | z vlastných zásob  |
| Sobota  | áno     | áno   |   áno              |
| Nedeľa  | áno     | -     |   -                |
@endcomponent

V prípade, že chceš zmeniť svoje prihlásenie alebo sa chceš odhlásiť, môžeš tak urobiť na stránke <a href="https://domcek.org">www.domcek.org</a> v sekcii "Moje prihlásenia".

V prípade, že sa z púte odhlásiš v termíne otvoreného prihlasovania, odoslaním mailu na <a href="mailto:domcek@domcek.org">domcek@domcek.org</a> môžeš požiadať o vrátenie vyplatenej zálohy.

V prípade akýchkoľvek otázok nás môžeš kontaktovať na mailovú adresu <a href="mailto:pute@domcek.org">pute@domcek.org</a> alebo nám napíš na Domčekovkú FB stránku.

Ďakujeme za tvoje prihlásenie na púť a tešíme sa na teba!
</pre>

<div style="margin-top: 40px;color:black" >
Pekný deň praje team,
</div>
Domček


------
###### V prípade akýchkoľvek problémov s používateľským kontom nás kontaktuj na podpora@domcek.org.
###### V prípade akýchkoľvek otázok súvisiacich s tvojou účasťou na podujatí nás kontaktuj na pute@domcek.org.
@endcomponent
