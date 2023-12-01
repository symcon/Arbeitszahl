# Arbeitszahl
Der Wirkungsgrad wird berechnet.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Der Wirkungsgrad für die vergangenen 365 Tage
* Der Wirkungsgrad für die vergangenen 30 Tage

### 2. Voraussetzungen

- IP-Symcon ab Version 6.3

### 3. Software-Installation

* Über den Module Store das 'Jahresarbeitszahl'-Modul installieren.
* Alternativ über das Module Control folgende URL hinzufügen

### 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' kann das 'Jahresarbeitszahl'-Modul mithilfe des Schnellfilters gefunden werden.  
	- Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

__Konfigurationsseite__:

Name                      | Beschreibung
------------------------- | ------------------
Wärmeenergie (kWh)        | Variable, welche als Zähler geloggt ist 
Elektrische Energie (kWh) | Variable, welche als Zähler geloggt ist

### 5. Statusvariablen

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

#### Statusvariablen

Name         | Typ              | Beschreibung
------------ | ---------------- | ------------
MAZ          | Float            | Steht für die Monatsarbeitszahl
JAZ          | Float            | Steht für die Jahresarbeitszahl
Ereignis     | zyklisches Event | Event, damit der Wirkungsgrad um Mitternacht berechnet wird

<!--Im englischen ist die Anzeige SPF Month und SPF Year. SPF steht für sessional performance factor---> 

### 7. PHP-Befehlsreferenz

`boolean ARZ_Calculation(integer $InstanzID);`
Berechnung des Wirkungsgrades

Beispiel:
`ARZ_Calculation(12345);`