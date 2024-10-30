=== checkrobin für WooCommerce Plug-In ===
Contributors: checkrobin
Tags: checkrobin, woocommerce, parcel, shipping, logistic, delivery, courier, wordpress, plugin
Requires at least: 4.0
Tested up to: 6.0
Stable tag: trunk
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Mit dem Checkrobin-Plugin können Sie Bestelldaten von Ihrem WooCommerce-Shop direkt an Checkrobin übertragen.

== Description ==

checkrobin ist die Versandplattform für deine Pakete. Einfach einloggen und mit wenigen Klicks bündeln wir alle deine Verkaufskanäle und versenden deine Pakete. Egal ob du online über deinen eigenen Shop vertreibst oder über Marktplätze verkaufst, checkrobin integriert alle gängigen Anbieter einfach und flexibel.

Wir arbeiten mit allen großen Logistikern. Nutze immer die aktuellsten checkrobin Versandkonditionen oder kombiniere sie mit den Tarifen deines aktuellen Logistiker-Vertrages und finde so immer die beste Versandlösung für dich.  Unser Kundenservice hat bei Problemen mit Abholung und Zustellung stets ein offenes Ohr für dich und kümmert sich um die Kommunikation mit dem Logistikpartner.

== Installation ==

DE: Installationsanleitung:

1. Laden Sie das angebotene Plug-In herunter
2. Melden Sie sich in WordPress an und wechseln Sie zum Dashboard (Admin-Bereich / Backend)
3. Klicken Sie in der linken Menüleiste auf "Plug-Ins" -> wählen Sie -> "Installieren"
4. Klicken Sie auf den Button oben links "Plug-In hochladen" und klicken Sie anschließend auf "Durchsuchen"
5. Wählen Sie die vorher herunter geladene .ZIP-Datei aus und bestätigen Sie mit "öffnen"
6. Klicken Sie nach abgeschlossener Installation auf "Plug-In aktivieren" (ggf. im Bereich "Plug-Ins" -> "Installierte Plug-Ins" -> "Checkrobin")
7. Im Menüpunkt "Installierte Plug-Ins" finden Sie nun das installierte "checkrobin"-Plugin
8. Klicken Sie am Plugin auf den Link "Einstellungen" und geben Sie anschließend Ihren checkrobin Business Benutzernamen und Ihr Kennwort ein
9. Neu eingehende Bestellungen können von nun an auf der Plugin-Seite (WooCommerce -> Checkrobin) in Ihren checkrobin Business Account übertragen werden

Weitere Informationen zur Installation finden Sie unter:

https://www.checkrobin.com/contentdata/download/anleitung_woocommerce.pdf

------------

EN: Installation instructions:

1. Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->Plugin Name screen to configure the plugin

For further instructions, please see:

https://www.checkrobin.com/contentdata/download/anleitung_woocommerce.pdf

== Frequently Asked Questions ==

= Was ist checkrobin Business? =

checkrobin Business ist ein Versandportal, das sich an kleine und mittlere Unternehmen, Onlineshops und kommerzielle Vielversender richtet.

Durch den einfachen Import von Versanddaten, speziellen und günstigen Konditionen (bei professionellen internationalen Logistik-Partnern wie DPD, GLS oder UPS) sowie durch attraktive Zusatzleistungen (wie einen erhöhten Versicherungswert), können Sie Ihren Versand und Ihre Kosten bequem optimieren. 

Hier erfahren Sie, wie es funktioniert:

https://www.checkrobin.com/de/funktionen

= Muss ich mich registrieren, um checkrobin Business nutzen zu können? =

checkrobin Business richtet sich an kleine und mittlere Unternehmen sowie Onlineshops und kommerzielle Vielversender, die ihr Versandvolumen angeben müssen, um Ihre individuellen Preise zu erhalten.

Daher ist eine Registrierung zwingend erforderlich, um die optimalen Konditionen anbieten zu können.


Sollten Sie nicht regelmäßig versenden, empfehlen wir Ihnen unsere Vergleichsplattform, auf der Sie ohne Registrierung problemlos Pakete versenden können.

Hier können Sie sich kostenlos registrieren:

https://www.checkrobin.com/

= Ist checkrobin Business kostenpflichtig? =

Die Registrierung und Nutzung von checkrobin Business in der Basisversion ist kostenlos.

Sie bekommen ausschließlich die beauftragten Versanddienstleistungen verrechnet.

Hier erfahren Sie mehr über unsere Preise:

https://www.checkrobin.com/de/preise


== Screenshots ==

1. checkrobin, die Versandplattform für deine Pakete
2. Wir arbeiten mit den größten Partnern
3. Günstige Versandkonditionen über die checkrobin Tarife
4. Einfache und übersichtliche Verwaltung deiner Pakete


== Changelog ==

= 0.0.13 =
* [Fixed] Added a missing commata in Activator.php, for each CRATE TABLE sql statement, after UNIQUE KEY id (id) definition -> "UNIQUE KEY id (id),". This error prevented the creation of db tables the plugin needs to work correctly. Please deaktivate and re-activate your plugin once to make sure these changes get applyed. 

= 0.0.12 =
* [Added] Added new functionality that allows to cancel, resend and archive orders (parcel drafts). 
* [Enhancement] Plugin own css better encapsulated

= 0.0.11 =
* [Added] Tested and added WooCommerce "WC tested up to"-Tag (Support testet up to 5.4.1)
* [Added] New Functionality, to define order status to trigger sync!
* [Added] Added missing datatabe assets
* [Added] Added method getLastCronRunDate + show last cron run date in settings page
* [Added] Added support for basic roles and capabilities management: introduced custom capability "edit_checkrobin" (Note: capability is initialy ONLY added to user role "administrator".)
* [Enhancement] Added seperate menu for checkrobin plugin

= 0.0.10 =
* [Fixed] Removed frontend assets request to missing file /plugin.min.js

= 0.0.9 =
* [Enhancement] If an order contains a maximum of 1 product, the dimensions of this product are now used as the basis for the dimensions of the outer box
* [Changed] The menu item "Settings" has been renamed to "Checkrobin Settings"
* [Changed] Updated /doc

= 0.0.8 =
* [Enhancement] Translate Plugin Title and Description, optimize i18n
* [Changed] Headline of Tracking-List Page

= 0.0.7 =
* [Added] Compatibility to Wordpress 5.5+ and WooCommerce 4.6+
* [Changed] Fixed date comparison; send orders also if plugin-activation-date is the same as order-date

= 0.0.6 =
* [Added] First release version for wordpress repository

= 0.0.5 =
* [Fixed] Avoid settings link in module overview overloading other modules settings links

= 0.0.4 =
* [Added] count(): Parameter must be an array or an object that implements Countable as of as of PHP 7.2
* [Added] Updating composer/installers (v1.5.0 => v1.7.0)

= 0.0.3 =
* [Added] Added defined check and prefix to constants.php
* [Added] Re-Worked Failsave email

= 0.0.2 =
* [Added] Disabled Failsave email
* [Added] Make sure default timezone is set

= 0.0.1 =
* [Added] Started Changelog


Note:
- Also see changelog.txt in plugin folder

== Upgrade Notice ==

Upgrade-Verfahren:

- Erstellen Sie vor dem Aktualisieren immer eine Sicherungskopie Ihrer Datenbank
- Kopieren Sie die neue Plugin-Version in Ihr Verzeichnis "/ wp-content / plugins /" oder installieren Sie das Plugin direkt über den Bildschirm "WordPress-Plugins"
- Weitere Informationen finden Sie in der Installationsanleitung

Upgrade procedure:

- Always create a backup of your database before updating 
- Copy the new plugin version into your `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly
- Also, for further informations, please see installation instructions
