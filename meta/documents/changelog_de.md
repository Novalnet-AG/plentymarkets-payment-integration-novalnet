# Informationen zum Novalnet Release

## v2.0.10 (31.10.2019)

### Behoben

- Zahlungs-Plugin angepasst, um die Erstellung doppelter Einträge in der Bestellhistorie für dieselbe TID zu vermeiden

### Neu

- Das Geburtsdatumsfeld auf der Checkout-Seite wurde angepasst

## v2.0.9 (13.09.2019)

### Behoben

- Fehler bei der Anzeige der Novalnet-Überweisungsdetails im Rechnungs-PDF mithilfe der Funktion “OrderPdfGeneration”
- Fehler beim Bestätigen von Transaktionen durch den Händler bei Rechnung, Rechnung mit Zahlungsgarantie und Vorkasse

## v2.0.8 (30.08.2019)

### Neu

- Anzeige der Novalnet-Überweisungsdetails im Rechnungs-PDF mithilfe der Funktion “OrderPdfGeneration”

### Entfernt

- Unbenutzte Altdateien früherer Versionen

## v2.0.7 (24.06.2019)

### Behoben

- Verhinderung von Doppelbuchungen durch den Endkunden

## v2.0.6 (24.05.2019)

### Behoben

- Hinzugefügt IO in plugin.json

## v2.0.5 (14.05.2019)

### Verbessert

- Bei Rechnung, Rechnung mit Zahlungsgarantie und Vorkasse wird für On-hold-Transaktionen die Bankverbindung von Novalnet auf der Rechnung angezeigt

## v2.0.4 (24.04.2019)

### Behoben

- Anzeige der Zahlungsarten nur für vordefinierte Länder

### Neu

- Die Zahlungsart wird dem Endkunden abhängig von Mindest- und Höchstbestellwert angezeigt

### Verbessert

- Das Novalnet-Zahlungsmodul wurde ausgiebig  getestet und entsprechend optimiert

## v2.0.3 (02.04.2019)

### Verbessert

- Das Novalnet-Zahlungsmodul wurde ausgiebig  getestet und entsprechend optimiert

## v2.0.2 (19.03.2019)

### Neu

- Auslösen der Autorisierung und des Einzugs für On-hold-Zahlungen (Kreditkarte, SEPA-Lastschrift, SEPA-Lastschrift mit Zahlungsgarantie, Kauf auf Rechnung, Rechnung mit Zahlungsgarantie und PayPal)
- Auslösen einer Rückerstattung für Zahlungen (Kreditkarte, SEPA-Lastschrift, SEPA-Lastschrift mit Zahlungsgarantie, Rechnung mit Zahlungsgarantie, Sofortüberweisung, iDeal, PayPal, eps, giropay und Przelewy24)
- Zahlungen für konfigurierte Länder empfangen
- Das Zahlungslogo wurde angepasst

### Verbessert

- Das Novalnet-Zahlungsmodul wurde ausgiebig  getestet und entsprechend optimiert

### Behoben

- Fehlermeldungsanzeige für abgelehnte Transaktionen
- Problem mit dem Betragsformat in der Callback-Benachrichtigungs-E-Mail

## v2.0.1 (23.01.2019)

### Behoben

- Problem beim Update des Zahlungsplugins über plentyMarketplace

## v2.0.0 (24.12.2018)

### Neu

- Der Status Garantierte Zahlung ausstehend wurde implementiert
- Mindestbetrag für Zahlungen mit Zahlungsgarantie auf 9,99 EUR herabgesetzt
- 3D-Secure Verfahren für Kreditkartenzahlungen basierend auf den Einstellungen im Novalnet Administrationsportal
- Anzeige der Payment Slip für Barzahlen-Zahlungen auf der Erfolgreich-Seite

### Verbessert

- On-hold-Option ist nun für bestimmte Zahlungsarten konfigurierbar Kreditkarte, SEPA-Lastschrift (mit Zahlungsgarantie), Kauf auf Rechnung (mit Zahlungsgarantie), PayPal
- Anlegen einer Bestellung vor Zahlungsaufruf für alle umgeleitete Zahlungsarten (SOFORT-Überweisung, Kreditkarte mit 3D-Secure, PayPal, etc.), um bei Kommunikationsabbrüchen aufgrund eines Timouts, Browserschließung, etc. eine fehlende Bestellung zu vermeiden

### Behoben

- Abgleich von Transaktionsinformationen in der Rechnung

## v1.0.3 (22.08.2018)

### Neu

- Funktion zum Austausch des Novalnet-Logos im Checkout durch eigenes Logo eingebaut

## v1.0.2 (01.06.2018)

### Verbessert

- Angepasstes Zahlungs-Plugin für die neue Konfigurationsstruktur und Unterstützung für mehrere Sprachen.

## v1.0.1 (17.01.2018)

### Verbessert

- Die Fehlermeldung wird ohne Fehlercode angezeigt.

## v1.0.0 (08.12.2017)

- Neuer Release
