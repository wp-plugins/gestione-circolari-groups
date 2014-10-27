=== Gestione Circolari Groups===
Contributors: Scimone Ignazio
Tags: Gestione Circolari, Scuola, Gestione Scuola, Groups
Requires at least: 3.7
Tested up to: 4.0
Stable tag: 2.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Gestione Circolari Scolastiche Groups. 

== Description ==

Gestione delle circolari scolastiche con la possibilit&agrave; di richiedere la firma e l'adesione alle circolari sindacali.
Questa versione utilizza il plugin <a href="http://wordpress.org/plugins/groups/">Groups</a> per la profilatura degli utenti a cui destinare le circolari 
ATTENZIONE!!
Questa versione del plugin deve essere installata in alternativa dell'altro plugin Gestione Circolari con gestione interna dei gruppi.
== Installation ==

Di seguito sono riportati i passi necessari per l'installazione del plugin.


1. Scaricare il plugin dal repository di Wordpress o dal sito di riferimento
2. Attivare il plugin dal menu Plugins
3. Scaricare il plugin Groups dal repository di Wordpress, attivarlo e configurarlo.
4. Creare i gruppi di utenti con il plugin Groups es. Docenti, ATA, Tutti, etc....
5. Impostare i Parametri:
<br />	4.1 <strong>Gruppo Pubblico Circolari</strong>; in genere Tutti, indica la visibilit&agrave; delle corcolari nella gestione della presa visione 
<br />	4.2 <strong>Categoria Circolari</strong>; indica la categoria delle circolari utilizzate nei post, questo parametro permette di fondere le circolari codificate negli articoli a quelli codificati con la gestione delle circoalri 
5. Inserire le circolari, selezionando i destinatari nella finestra dei destinatari, il numero progressivo se diverso da quello proposto, selezionare l'eventuale richiesta di firma, selezionare l'eventuale indicazione di circolare per sciopero 
6. Creare una pagina con lo shortcode [VisCircolari] e collegarla ad una voce di menu
7. inserire il widget che indica, se l'utente &egrave; loggato il numero di circolari da firmare o da prendere in visione <strong>Circolari</strong>.
8. inserire il widget che riproduce la struttura temporale della pubblicazione delle circolari <strong>Navigazione Circolari</strong>.

== Screenshots ==

1. Elenco Circolari
2. Ambiente di creazione/modifica delle circolari con tutte le finestre per la codifcia dei parametri specifici della gestione
3. Finestra di gestione delle circolari da firmare
4. Finestra associata ad ogni circolare per la verifica delle firme/adesioni
5. Finestra lato pubblico con i due widget; Circolari, che riporta il numero di circolari da visionare/firmare. Navigazione Circolari, che implementa il sistema di navigazione per Anno/mese delle circolari
6. Creazione della pagina che conterrà le circolari
7. Pagina con l'elenco delle circolari
8. Visualizzazione di una circolare

== Changelog ==
= 2.0.2 =
- <strong>Risolto</strong> conflitto con Wordfence Security delle TableTools
= 2.0.1 =
- <strong>Sistemato</strong> piccolo bug dello shortcode VisualizzaCircolariHome 
= 2.0 =
- <strong>Sistemati</strong> diversi bug
- <strong>Ottimizzato</strong> il codice per aumentare la velocità del sito con il plugin attivato
- <strong>Aggiunto</strong>aggiunto shortcode VisualizzaCircolariHome da utilizzare nel template Pasw2013 per elencare le circolari provenienti dal plugin. Codice gentilmente fornito da Christian Ghellere
= 1.6 =
- <strong>Sistemati</strong> diversi bug
= 1.5 =
- <strong>Sistemati</strong> diversi bug di visualizzazione delle circolari
- <strong>Migliorata</strong> l'interfaccia sia pubblica che amministrativa
- <strong>Implementata</strong> la possibilita' di inserire la data entro cui firmare le circolari
- <strong>Implementata</strong> la visualizzazione delle circolari relative all'utente per tipologia;Firmate, Non Firmate e Scadute
- <strong>Implementata</strong> la gestione dinamica delle tabelle tramite plugin JQuery con la possibilità di stampare o esportare le tabelle in CSV, Excel e Pdf
- <strong>Modificata</strong> la gestione della numerazione delle circolari per anno scolastico nel formato aaaa/aa
= 1.4 =
- <strong>Sistemato</strong> bug verifica destinatari della circolare
= 1.3 =
- <strong>Implementata</strong> gestione delle circolari protette da password
- <strong>Sistemato</strong> bug che poteva essere generato in fase di verifica dei destinatari della circolare
= 1.2 =
- <strong>Sistemato</strong> bug delle visualizzazione Circolari firmate FrontEnd
= 1.1 =
- <strong>Sistemati</strong> vari bug di visaulizzazione di nel FrontEnd che nel BackEnd
- <strong>Suddiviso</strong> L'elenco delle circolari in Firmate e da Firmare
- <strong>Implementata</strong> la paginazione nelle liste delle circolari Firmate e da Firmare
- <strong>Inserita</strong> nel BackEnd la visualizzazione dell'icona da Firmare da Esprimere adesione
- <strong>Modificata</strong> BackEnd il sistema di Firma e di Espressione adesione, adesso dopo l'operazione si rimane nella circolare
= 1.0 =
- <strong>Prima versione</strong>
 == Upgrade Notice ==
Aggiornare sempre il plugin all'ultima versione fino a che non si arriva ad una versione stabile ed operativa

== Note ==
Versione che utilizza il plugin <a href="http://wordpress.org/plugins/groups/">Groups</a> per la profilatura degli utenti a cui destinare le circolari 
Da utilizzare tenendo in considerazione che potrebbero essere presenti errori e malfunzionamenti. Per segnalare errori o problemi di utilizzo usare l'indirizzo email ignazio.scimone@gmail.com segnalando il sito in cui &egrave; installato il plugin, una breve descrizione del problema riscontrato, la persona di riferimento con indirizzo email.
Non prendo in considerazione richieste non corredate da tutti i dati sopraelencati. 

