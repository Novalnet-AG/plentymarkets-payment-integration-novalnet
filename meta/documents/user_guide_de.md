<div class="alert alert-warning" role="alert">
   <strong><i>Hinweis:</i></strong> Das Novalnet Plugin wurde für die Verwendung mit dem Onlineshop System CERES entwickelt und funktioniert nur in dieser Struktur oder anderen Tempalte-Plugins. Ein IO Plugin ist erforderlich.
</div>

# Novalnet-Plugin für Plentymarkets:

Das Novalnet Zahlungsmodul für Plentymarkets vereinfacht die tägliche Arbeit aufgrund der Automatisierung des gesamten Zahlungsprozesses, angefangen beim Bezahlvorgang bis hin zum Inkasso. Dieses Plugin/Modul ist entwickelt worden, um Ihren Umsatz aufgrund der Vielzahl an internationalen und lokalen Zahlungsmethoden zu steigern.

Das Plugin ist perfekt auf Plentymarkets und das umfangreiche Serviceangebot der Novalnet AG angepasst. Das vielfältige Angebot an Zahlungsmethoden umfasst zum Beispiel **Kreditkarte**, **Lastschrift SEPA**, **PayPal**, **Kauf auf Rechnung**, **Barzahlen**, **SOFORT-Überweisung**, **iDeal** und viele mehr

## Abschluss eines Novalnet Händlerkontos/Dienstleistungsvertrags:

Sie müssen zuerst einen Dienstleistungsvertrag/ ein Händlerkonto bei Novalnet abschließen/einrichten, bevor Sie das Zahlungsmodul in Plentymarkets installieren können. Sie erhalten nach Abschluss des Vertrags die Daten zur Installation und Konfiguration der Zahlungsmethoden. Bitte kontaktieren Sie Novalnet unter der [sales@novalnet.de](mailto:sales@novalnet.de), um Ihr Händlerkonto bei Novalnet einrichten zu lassen.

## Konfiguration des Novalnet Plugins in Plentymarkets:

Zur Einrichtung rufen Sie den Menüpunkt **Plugins -> Plugin-Übersicht -> Novalnet -> Konfiguration** auf.

### Novalnet Haupteinstellungen

- Geben Sie Ihre Novalnet Login Daten ein, um die Zahlungsmethode in Ihrem Shop sichtbar zu machen.
- Die Eingabefelder **Merchant ID**, **Authentication code**, **Project ID**, **Tariff ID** und **Payment access key** sind Pflichtfelder.
- Diese Daten sind im [Novalnet-Händleradministrationsportal](https://admin.novalnet.de/).
- Um die Einrichtung von Novalnet in Ihrem Shop abzuschließen, bestätigen Sie bitte nach der Eingabe der Daten in den jeweiligen Feldern den Menüpunkt **Enable payment method**.

##### Informationen zu Ihrem Novalnet Händlerkonto:

1. Melden Sie sich an Ihrem Händlerkonto an.
2. Klicken Sie auf den Menüpunkt **PROJEKTE**.
3. Wählen Sie das gewünschte Produkt/Projekt aus.
4. Unter dem Reiter **Parameter Ihres Shops** finden Sie die benötigten Informationen.
5. Wichtiger Hinweis: Evtl. sind mehrere **Tarif-ID´s** vorhanden, wenn Sie mehrere Tarife für Ihre Projekte angelegt haben. Notieren/Kopieren Sie sich die Tarif-ID, welche Sie in Ihrem Shop verwenden wollen.

### Novalnet Einrichtung mit der Installationsbeschreibung.

<table>
    <thead>
        <th>
            Feld
        </th>
        <th>
            Beschreibung
        </th>
    </thead>
    <tbody>
        <tr>
        <td class="th" align=CENTER colspan="2">Allgemeines</td>
        </tr>
        <tr>
            <td><b>Händler-ID</b></td>
            <td>A Die Händler ID wird von der Novalnet AG nach Eröffnung eines Händlerkontos Ihnen zur Verfügung gestellt. Bitte kontaktieren Sie Novalnet unter der <a href="mailto:sales@novalnet.de" target="_blank">sales@novalnet.de</a>, um Ihr Händlerkonto bei Novalnet einrichten zu lassen.</td>
        </tr>
        <tr>
            <td><b>Authentifizierungscode</b></td>
            <td>Der Händler Authenthifizierungscode wird von der Novalnet AG nach Eröffnen eines Händlerkontos Ihnen zur Verfügung gestellt.</td>
        </tr>
        <tr>
            <td><b>Projekt-ID</b></td>
            <td>Die Projekt ID ist eine eindeutige Identifikationsnummer eines angelegten Händlerprojekts. Der Händler kann eine beliebige Anzahl von Projekten im <a href="https://admin.novalnet.de/">Novalnet-Händleradministrationsportal</a> erstellen.</td>
        </tr>
        <tr>
            <td><b>Tarif-ID</b></td>
            <td>Die Tarif ID ist eine eindeutige Identifikationsnummer für jedes angelegte Projekt. Der Händler kann eine beliebige Anzahl von Tarifen im <a href="https://admin.novalnet.de/" target="_blank">Novalnet-Händleradministrationsportal</a> erstellen.</td>
        </tr>
        <tr>
            <td><b>Zahlungs-Zugriffsschlüssel</b></td>
            <td>Dies ist der sichere öffentliche Schlüssel zur Verschlüsselung und Entschlüsselung von Transaktionsparametern. Für alle Transaktionen muss dieser Schlüssel zwingend übermittelt werden.</td>
        </tr>
        <tr>
            <td><b>Zeitlimit der Schnittstelle (in Sekunden)</b></td>
            <td>
                Falls die Verarbeitungszeit der Bestellung das Zeitlimit der Schnittstelle überschreitet, wird die Bestellung nicht ausgeführt.
            </td>
        </tr>
        <tr>
            <td><b>Proxy-Server</b></td>
            <td>
                Geben Sie die IP-Adresse Ihres Proxyservers zusammen mit der Nummer des Ports ein und zwar in folgendem Format: IP-Adresse : Nummer des Ports (falls notwendig)
            </td>
        </tr>
        <tr>
            <td><b>Partner-ID</b></td>
            <td>Geben Sie die Partner-ID der Person / des Unternehmens ein, welche / welches Ihnen Novalnet empfohlen hat</td>
        </tr>
        <tr>
        <td class="th" align=CENTER colspan="2"><b>Zahlungseinstellungen</b></td>
        </tr>
        <tr>
        <td class="th" align=CENTER colspan="2">Allgemeines</td>
        </tr>
        <tr>
            <td><b>Zahlungsart aktivieren</b></td>
            <td>Verwenden Sie diese Option, um Zahlungsarten zu aktivieren / zu deaktivieren.</td>
        </tr>
        <tr>
            <td><b>Testmodus aktivieren</b></td>
            <td>Die Zahlung wird im Testmodus durchgeführt, daher wird der Betrag für diese Transaktion nicht eingezogen.</td>
        </tr>
        <tr>
            <td><b>Zahlungslogo hochladen</b></td>
            <td>The payment method logo will be displayed on the checkout page.</td>
        </tr>
        <tr>
            <td><b>Mindest-Bestellbetrag</b></td>
            <td>Mindest-Bestellbetrag, um diese Zahlungsart anzubieten (in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)</td>
        </tr>
        <tr>
            <td><b>Maximal-Bestellbetrag</b></td>
            <td>Maximal-Bestellbetrag um diese Zahlungsart anzubieten (in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)</td>
        </tr>
        <tr>
            <td><b>Zulässiges Land</b></td>
            <td>Diese Zahlungsart wird für die genannten Länder zugelassen. Geben Sie die Länder in folgendem Format ein: z.B. DE, AT, CH. Falls Sie das Feld leer lassen, werden alle Länder zugelassen.</td>
        </tr>
        <tr>
        <td class="th" align=CENTER colspan="2">Kreditkarte</td>
        </tr>
        <tr>
            <td><b>3D-Secure aktivieren</b></td>
            <td>3D-Secure wird für Kreditkarten aktiviert. Die kartenausgebende Bank fragt vom Käufer ein Passwort ab, welches helfen soll, betrügerische Zahlungen zu verhindern. Dies kann von der kartenausgebenden Bank als Beweis verwendet werden, dass der Käufer tatsächlich der Inhaber der Kreditkarte ist. Damit soll das Risiko von Chargebacks verringert werden.</td>
        </tr>
        <tr>
            <td><b>3D-Secure-Zahlungen unter vorgegebenen Bedingungen durchführen</b></td>
            <td>Wenn 3D-Secure in dem darüberliegenden Feld nicht aktiviert ist, sollen 3D-Secure-Zahlungen nach den Einstellungen zum Modul im <a href="https://admin.novalnet.de/" target="_blank">Novalnet-Händleradministrationsportal</a> unter "3D-Secure-Zahlungen durchführen (gemäß vordefinierten Filtern und Einstellungen)" durchgeführt werden. Wenn die vordefinierten Filter und Einstellungen des Moduls "3D-Secure durchführen" zutreffen, wird die Transaktion als 3D-Secure-Transaktion durchgeführt, ansonsten als Nicht-3D-Secure-Transaktion. Beachten Sie bitte, dass das Modul "3D-Secure-Zahlungen durchführen (gemäß vordefinierten Filtern und Einstellungen)" im <a href="https://admin.novalnet.de/" target="_blank">Novalnet-Händleradministrationsportal</a> konfiguriert sein muss, bevor es hier aktiviert wird. Für weitere Informationen sehen Sie sich bitte die Beschreibung dieses Betrugsprüfungsmoduls an (unter dem Reiter "Betrugsprüfungsmodule" unterhalb des Menüpunkts "Projekte" für das ausgewähte Projekt im <a href="https://admin.novalnet.de/" target="_blank">Novalnet-Händleradministrationsportal</a>) oder kontaktieren Sie das Novalnet-Support-Team.</td>
        </tr>
        <tr>
            <td><b>Bearbeitungsmaßnahme</b></td>
            <td>Zahlung einziehen / Zahlung autorisieren</td>
        </tr>
        <tr>
            <td><b>Mindesttransaktionsbetrag für die Autorisierung</b></td>
            <td>Falls eine Bestellung die angegebene Grenze überschreitet, wird diese Bestellung bis zur manuellen Bestätigung des Händlers auf den Status <b>on-hold</b> gesetzt.Sie können das Feld leer lassen, wenn Sie möchten, dass alle Transaktionen als on hold behandelt werden.</td>
        </tr>
        <tr>
        <td class="th" align=CENTER colspan="2">Lastschrift SEPA</td>
        </tr>
        <tr>
            <td><b>Abstand (in Tagen) bis zum SEPA-Einzugsdatum</b></td>
            <td>Geben Sie die Anzahl der Tage ein, nach denen die Zahlung vorgenommen werden soll (muss zwischen 2 und 14 Tagen liegen).</td>
        </tr>
        <tr>
            <td><b>Bearbeitungsmaßnahme</b></td>
            <td>Zahlung einziehen / Zahlung autorisieren</td>
        </tr>
        <tr>
            <td><b>Mindesttransaktionsbetrag für die Autorisierung</b></td>
            <td>Falls eine Bestellung die angegebene Grenze überschreitet, wird diese Bestellung bis zur manuellen Bestätigung des Händlers auf den Status <b>on-hold</b> gesetzt.Sie können das Feld leer lassen, wenn Sie möchten, dass alle Transaktionen als on hold behandelt werden.</td>
        </tr>
        <tr>
            <td><b>Zahlungsgarantie aktivieren</b></td>
            <td><b>Grundanforderungen für die Zahlungsgarantie:</b><br > -> Zugelassene Staaten: AT, DE, CH. <br > -> Zugelassene Währung: EUR.<br > -> Mindestbetrag der Bestellung >= 9,99 EUR.<br > -> Mindestalter des Endkunden >= 18 Jahre.<br > -> Rechnungsadresse und Lieferadresse müssen übereinstimmen.<br > -> Geschenkgutscheine / Coupons sind nicht erlaubt.</td>
        </tr>
        <tr>
            <td><b>Mindestbestellbetrag (in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)</b></td>
            <td>Diese Einstellung überschreibt die Standardeinstellung für den Mindest-Bestellbetrag. Anmerkung: der Mindest-Bestellbetrag sollte größer oder gleich 9,99 EUR sein.</td>
        </tr>
        <tr>
            <td><b>Zahlung ohne Zahlungsgarantie erzwingen</b></td>
            <td>Falls die Zahlungsgarantie aktiviert ist (wahr), die oben genannten Anforderungen jedoch nicht erfüllt werden, soll die Zahlung ohne Zahlungsgarantie verarbeitet werden.</td>
        </tr>
        <td class="th" align=CENTER colspan="2">Kauf auf Rechnung</td>
        <tr>
            <td><b>Fälligkeitsdatum (in Tagen)</b></td>
            <td>Geben Sie die Anzahl der Tage ein, binnen derer die Zahlung bei Novalnet eingehen soll (muss größer als 7 Tage sein). Falls dieses Feld leer ist, werden 14 Tage als Standard-Zahlungsfrist gesetzt.</td>
        </tr>
        <tr>
            <td><b>Bearbeitungsmaßnahme</b></td>
            <td>Zahlung einziehen / Zahlung autorisieren</td>
        </tr>
        <tr>
            <td><b>Mindesttransaktionsbetrag für die Autorisierung</b></td>
            <td>Falls eine Bestellung die angegebene Grenze überschreitet, wird diese Bestellung bis zur manuellen Bestätigung des Händlers auf den Status <b>on-hold</b> gesetzt.Sie können das Feld leer lassen, wenn Sie möchten, dass alle Transaktionen als on hold behandelt werden.</td>
        </tr>
        <tr>
            <td><b>Zahlungsgarantie aktivieren</b></td>
            <td><b>Grundanforderungen für die Zahlungsgarantie:</b><br > -> Zugelassene Staaten: AT, DE, CH. <br > -> Zugelassene Währung: EUR.<br > -> Mindestbetrag der Bestellung >= 9,99 EUR.<br > -> Mindestalter des Endkunden >= 18 Jahre.<br > -> Rechnungsadresse und Lieferadresse müssen übereinstimmen.<br > -> Geschenkgutscheine / Coupons sind nicht erlaubt.</td>
        </tr>
        <tr>
            <td><b>Mindestbestellbetrag (in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)</b></td>
            <td>Diese Einstellung überschreibt die Standardeinstellung für den Mindest-Bestellbetrag. Anmerkung: der Mindest-Bestellbetrag sollte größer oder gleich 9,99 EUR sein.</td>
        </tr>
        <tr>
            <td><b>Zahlung ohne Zahlungsgarantie erzwingen</b></td>
            <td>Falls die Zahlungsgarantie aktiviert ist (wahr), die oben genannten Anforderungen jedoch nicht erfüllt werden, soll die Zahlung ohne Zahlungsgarantie verarbeitet werden.</td>
        </tr>
        <td class="th" align=CENTER colspan="2">Barzahlen</td>
        <tr>
            <td><b>Verfallsdatum des Zahlscheins (in Tagen)</b></td>
            <td>Geben Sie die Anzahl der Tage ein, um den Betrag in einer Barzahlen-Partnerfiliale in Ihrer Nähe zu bezahlen. Wenn das Feld leer ist, werden standardmäßig 14 Tage als Fälligkeitsdatum gesetzt.</td>
        </tr>
        <td class="th" align=CENTER colspan="2">Paypal</td>
        <tr>
            <td><b>Bearbeitungsmaßnahme</b></td>
            <td>Zahlung einziehen / Zahlung autorisieren</td>
        </tr>
        <tr>
            <td><b>Mindesttransaktionsbetrag für die Autorisierung</b></td>
            <td>Falls der Bestellbetrag das angegebene Limit übersteigt, wird die Transaktion ausgesetzt, bis Sie diese selbst bestätigen. (Für PayPal: Um diese Option zu verwenden, müssen Sie die Option Billing Agreement (Zahlungsvereinbarung) in Ihrem PayPal-Konto aktiviert haben. Kontaktieren Sie dazu bitte Ihren Kundenbetreuer bei PayPal.)</td>
        </tr>
    </tbody>
</table>

## Ereignisaktion für das Bestätigen / Stornieren / Rückerstatten einer Novalnet-Transaktion hinzufügen (Event creation)

Richten Sie eine Ereignisaktion (Event procedure) ein, um Novalnet-Transaktionen zu bestätigen, stornieren oder rückzuerstatten.

##### So richten Sie eine Ereignisaktion (Event procedure) ein:

1. Öffnen Sie das Menü **System » Aufträge » Ereignisaktionen**.
2. Klicken Sie auf **Ereignisaktion hinzufügen**. <br > → Das Fenster **Neue Ereignisaktion erstellen** wird geöffnet.
3. Geben Sie einen Namen ein.
4. Wählen Sie das Ereignis gemäß Tabelle 1-3.
5. **Speichern** Sie die Einstellungen. <br > → Die Ereignisaktion wird angelegt.
6. Nehmen Sie die weiteren Einstellungen gemäß Tabelle 1-3 vor.
7. Setzen Sie ein Häkchen bei **Aktiv**.
8. **Speichern** Sie die Einstellungen. <br > → Die Ereignisaktion wird gespeichert. 

<table>
   <thead>
    </tr>
      <th>
         Einstellung
      </th>
      <th>
         Option
      </th>
      <th>
         Auswahl
      </th>
    </tr>
   </thead>
   <tbody>
      <tr>
         <td><strong>Ereignis</strong></td>
         <td>Wählen Sie das Ereignis (Event), durch das die Versandbestätigung automatisch versendet werden soll.</td>
         <td></td>
      </tr>
      <tr>
         <td><strong>Filter 1</strong></td>
         <td><strong>Auftrag > Zahlungsart</strong></td>
         <td><strong>Plugin: Novalnet Invoice</strong></td>
      </tr>
      <tr>
        <td><strong>Aktion</strong></td>
        <td><strong>Plugin > Novalnet | Bestätigen </strong></td>
        <td></td>
      </tr>
    </tbody>
    <caption>
	Tabelle 1: Ereignisaktion (Event procedure) zum Senden einer automatischen Versandbestätigung
    </caption>
</table>

<table>
   <thead>
    </tr>
      <th>
         Einstellung
      </th>
      <th>
         Option
      </th>
      <th>
         Auswahl
      </th>
    </tr>
   </thead>
   <tbody>
      <tr>
         <td><strong>Ereignis</strong></td>
         <td>Wählen Sie das Ereignis (Event), durch das die Stornierungsbestätigung automatisch versendet werden soll.</td>
         <td></td>
      </tr>
      <tr>
         <td><strong>Filter 1</strong></td>
         <td><strong>Auftrag > Zahlungsart</strong></td>
         <td><strong>Plugin: Novalnet Invoice</strong></td>
      </tr>
      <tr>
        <td><strong>Aktion</strong></td>
        <td><strong>Plugin > Novalnet | Stornieren </strong></td>
        <td></td>
      </tr>
    </tbody>
    <caption>
	Tabelle 2: Ereignisaktion (Event procedure) zum Senden einer automatischen Stornierungsbestätigung
	</caption>
</table>

<table>
   <thead>
    </tr>
      <th>
         Einstellung
      </th>
      <th>
         Option
      </th>
      <th>
         Auswahl
      </th>
    </tr>
   </thead>
   <tbody>
      <tr>
         <td><strong>Ereignis</strong></td>
         <td>Wählen Sie das Ereignis (Event), durch das die Rückerstattungsbestätigung automatisch versendet werden soll.</td>
         <td></td>
      </tr>
      <tr>
         <td><strong>Filter 1</strong></td>
         <td><strong>Auftrag > Zahlungsart</strong></td>
         <td><strong>Plugin: Novalnet Invoice</strong></td>
      </tr>
      <tr>
        <td><strong>Aktion</strong></td>
        <td><strong>Plugin > Novalnet | Rückerstattung </strong></td>
        <td></td>
      </tr>
    </tbody>
    <caption>
	Tabelle 3: Ereignisaktion (Event procedure) zum Senden einer automatischen Rückerstattungsbestätigung
	</caption>
</table>

## Überweisungsdetails der Novalnet AG im Rechnungs-PDF anzeigen

Rechnungen für Bestellungen erstellen und dabei die Überweisungsdetails der Novalnet AG im Rechnungs-PDF anzeigen, außer für Bestellungen, die noch nicht vom Händler bestätigt sind.

## Anzeige der Transaktionsdetails zu Zahlungen auf der Bestellbestätigungsseite.

Um die Transaktionsdetails anzeigen zu lassen, befolgen Sie bitte die folgenden Schritte.

1. Navigieren Sie zum Menüpunkt **CMS » Container-Verknüpfungen**.
2. Navigieren Sie zum Bereich **Novalnet payment details**.
3. Aktivieren Sie das Feld **Order confirmation: Additional payment information**.
4. Drücken Sie auf **Speichern**.<br />→ Die Zahlungsdetails werden danach auf der Bestellbestätigungsseite angezeigt.

## Aktualisierung der Händlerskript-URL

Die Händlerskript-URL wird dazu benötigt, um den Transaktionsstatus in der Datenbank / im System des Händlers aktuell und auf demselben Stand wie bei Novalnet zu halten. Dazu muss die Händlerskript-URL im [Novalnet-Händleradministrationsportal](https://admin.novalnet.de/) eingerichtet werden.

Vom Novalnet-Server wird die Information zu jeder Transaktion und deren Status (durch asynchrone Aufrufe) an den Server des Händlers übertragen.

Konfiguration der Händlerskript URL,

1. Melden Sie sich an Ihrem Händlerkonto im Novalnet Adminportal an.
2. Wählen Sie den Menüpunkt **Projekte** aus.
3. Wählen Sie das gewünschte Projekt aus.
4. Bitte geben Sie unter dem Reiter **Projektübersicht** und Menüpunkt **Händlerskript URL** Ihre Händlerskript URL für Ihren Shop ein.
5. Standardmäßig lautet die Händlerskript URL wie folgt: **URL-Ihrer-Webseite/payment/novalnet/callback**.

## Weitere Informationen

Um mehr über verschiedene Features bei Novalnet zu erfahren, kontaktieren Sie bitte Novalnet unter [sales@novalnet.de](mailto:sales@novalnet.de).
