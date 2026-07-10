<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = t('seo.privacy_title');
$pageDescription = t('privacy.title');

$lang = current_lang();

require __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <article class="card legal-page">
            <?php if ($lang === 'de'): ?>
                <h1>Datenschutzerklärung</h1>

                <p>
                    Der Schutz Ihrer personenbezogenen Daten ist uns ein wichtiges Anliegen. Nachfolgend
                    informieren wir Sie gemäß der Datenschutz-Grundverordnung (DSGVO) und dem
                    Bundesdatenschutzgesetz (BDSG) über die Erhebung, Verarbeitung und Nutzung Ihrer Daten
                    auf dieser Website.
                </p>

                <h2>1. Verantwortlicher</h2>
                <p>
                    Verantwortlich im Sinne des Art. 4 Nr. 7 DSGVO ist:<br>
                    Union de la Guinée Forestière en Allemagne (UGFA)<br>
                    <mark>[Vollständiger Vor- und Nachname der vertretungsberechtigten Person]</mark><br>
                    Leonie-Reygers-Terrasse, 44137 Dortmund, Deutschland<br>
                    Telefon: <a href="tel:+49151926242516">+49 151 926 242 516</a><br>
                    E-Mail: <a href="mailto:contact@guineeforestiere.de">contact@guineeforestiere.de</a>
                </p>

                <h2>2. Allgemeines zur Datenverarbeitung</h2>
                <p>
                    Wir verarbeiten personenbezogene Daten nur, soweit dies zur Bereitstellung einer
                    funktionsfähigen Website sowie unserer Inhalte und Leistungen erforderlich ist. Die
                    Verarbeitung erfolgt regelmäßig nur nach Einwilligung der Nutzer (Art. 6 Abs. 1 lit. a
                    DSGVO), zur Erfüllung eines Vertrags oder vorvertraglicher Maßnahmen (Art. 6 Abs. 1 lit. b
                    DSGVO), zur Erfüllung rechtlicher Verpflichtungen (Art. 6 Abs. 1 lit. c DSGVO) oder auf
                    Grundlage unserer berechtigten Interessen (Art. 6 Abs. 1 lit. f DSGVO).
                </p>

                <h2>3. Bereitstellung der Website und Server-Logfiles</h2>
                <p>
                    Bei jedem Aufruf unserer Website erfasst unser Hosting-Anbieter automatisch Informationen,
                    die Ihr Browser übermittelt (Server-Logfiles): IP-Adresse, Datum und Uhrzeit des Zugriffs,
                    aufgerufene Seite/Datei, übertragene Datenmenge, Referrer-URL, Browsertyp und Betriebssystem.
                    Diese Daten sind technisch erforderlich, um die Website anzuzeigen und die Stabilität und
                    Sicherheit zu gewährleisten. Rechtsgrundlage ist Art. 6 Abs. 1 lit. f DSGVO (berechtigtes
                    Interesse an einem sicheren und störungsfreien Betrieb). Die Logfiles werden aus
                    Sicherheitsgründen für in der Regel bis zu 7 Tage gespeichert und anschließend gelöscht.
                </p>

                <h2>4. Hosting</h2>
                <p>
                    Wir hosten unsere Website bei der ALL-INKL.COM – Neue Medien Münnich, Inhaber René Münnich,
                    Hauptstraße 68, 02742 Friedersdorf, Deutschland. Der Anbieter verarbeitet in unserem Auftrag
                    die vorgenannten Daten. Mit dem Anbieter haben wir einen Vertrag zur Auftragsverarbeitung
                    (AVV) gemäß Art. 28 DSGVO geschlossen. Rechtsgrundlage ist Art. 6 Abs. 1 lit. f DSGVO.
                </p>

                <h2>5. Cookies und Sitzung</h2>
                <p>
                    Diese Website verwendet ausschließlich technisch notwendige Cookies. Wir setzen ein
                    Sitzungs-Cookie, um die Funktionsfähigkeit der Website (u.&nbsp;a. Sicherheit gegen
                    CSRF-Angriffe und Speicherung der gewählten Sprache) zu gewährleisten. Diese Cookies
                    enthalten keine Tracking- oder Analysefunktionen und werden nach Ende der Sitzung bzw.
                    beim Schließen des Browsers gelöscht. Rechtsgrundlage ist § 25 Abs. 2 TDDDG sowie
                    Art. 6 Abs. 1 lit. f DSGVO. Ein Einwilligungsbanner ist für technisch notwendige Cookies
                    nicht erforderlich. Es werden keine Marketing-, Analyse- oder Drittanbieter-Cookies gesetzt.
                </p>

                <h2>6. Kontaktformular und Kontaktaufnahme</h2>
                <p>
                    Wenn Sie uns über das Kontaktformular oder per E-Mail kontaktieren, verarbeiten wir die von
                    Ihnen angegebenen Daten (Name, E-Mail-Adresse, Betreff, Nachricht), um Ihre Anfrage zu
                    bearbeiten. Rechtsgrundlage ist Art. 6 Abs. 1 lit. a DSGVO (Einwilligung) bzw. Art. 6 Abs. 1
                    lit. b DSGVO, sofern Ihre Anfrage der Anbahnung eines Vertragsverhältnisses dient. Die Daten
                    werden gelöscht, sobald die Anfrage abschließend bearbeitet ist und keine gesetzlichen
                    Aufbewahrungspflichten entgegenstehen.
                </p>

                <h2>7. Anmeldung zur Veranstaltung</h2>
                <p>
                    Für die Anmeldung zur Veranstaltung erheben wir Vorname, Name, Wohnsitzland, E-Mail-Adresse,
                    Telefonnummer, ggf. Organisation und die Art der Teilnahme. Diese Daten verarbeiten wir zur
                    Organisation und Durchführung der Veranstaltung sowie zum Versand der Teilnahmebestätigung.
                    Rechtsgrundlage ist Art. 6 Abs. 1 lit. b DSGVO (Durchführung der Teilnahme) bzw. Art. 6
                    Abs. 1 lit. a DSGVO (Einwilligung). Die Daten werden nach Abschluss der Veranstaltung
                    gelöscht, soweit keine gesetzlichen Aufbewahrungsfristen bestehen.
                </p>

                <h2>8. Beiträge / Spenden und Zahlungsabwicklung</h2>
                <p>
                    Bei einer freiwilligen Beteiligung (Beitrag/Spende) verarbeiten wir die von Ihnen
                    angegebenen Daten (z.&nbsp;B. Name, ggf. E-Mail, Telefonnummer, Betrag). Rechtsgrundlage ist
                    Art. 6 Abs. 1 lit. b DSGVO. Für die Zahlungsabwicklung nutzen wir externe Zahlungsdienste;
                    die Zahlungsdaten (z.&nbsp;B. Kreditkarten- oder Kontodaten) werden ausschließlich beim
                    jeweiligen Zahlungsdienstleister eingegeben und verarbeitet – wir erhalten diese Daten nicht.
                </p>
                <ul>
                    <li>
                        <strong>PayPal:</strong> PayPal (Europe) S.à r.l. et Cie, S.C.A., 22–24 Boulevard Royal,
                        L-2449 Luxemburg. Bei Zahlung über PayPal werden Ihre Daten an PayPal übermittelt.
                        Es kann zu einer Übermittlung in Drittländer (u.&nbsp;a. USA) kommen; PayPal stützt
                        diese auf Standardvertragsklauseln. Datenschutzhinweise:
                        <a href="https://www.paypal.com/de/webapps/mpp/ua/privacy-full" target="_blank" rel="noopener noreferrer">paypal.com/de/webapps/mpp/ua/privacy-full</a>.
                    </li>
                    <li>
                        <strong>Stripe (sofern aktiviert):</strong> Stripe Payments Europe, Ltd., 1 Grand Canal
                        Street Lower, Grand Canal Dock, Dublin, Irland. Datenschutzhinweise:
                        <a href="https://stripe.com/de/privacy" target="_blank" rel="noopener noreferrer">stripe.com/de/privacy</a>.
                    </li>
                    <li>
                        <strong>Banküberweisung:</strong> Bei Zahlung per Überweisung verarbeiten wir die aus dem
                        Zahlungsvorgang ersichtlichen Daten (Name, Verwendungszweck) zur Zuordnung des Beitrags.
                    </li>
                </ul>
                <p>
                    Rechtsgrundlage für die Weitergabe an die Zahlungsdienstleister ist Art. 6 Abs. 1 lit. b
                    DSGVO (Vertragserfüllung).
                </p>

                <h2>9. Partner- und Sponsoranfragen</h2>
                <p>
                    Bei einer Partner- oder Sponsoranfrage verarbeiten wir die angegebenen Kontakt- und
                    Organisationsdaten (z.&nbsp;B. Name der Organisation, Ansprechpartner, E-Mail, Telefon,
                    Website, ggf. Logo) zur Bearbeitung und Beantwortung Ihrer Anfrage. Rechtsgrundlage ist
                    Art. 6 Abs. 1 lit. b DSGVO bzw. Art. 6 Abs. 1 lit. a DSGVO.
                </p>

                <h2>10. E-Mail-Versand</h2>
                <p>
                    Bestätigungs- und Benachrichtigungs-E-Mails werden über den E-Mail-Dienst unseres
                    Hosting-Anbieters (ALL-INKL.COM) versendet. Rechtsgrundlage ist Art. 6 Abs. 1 lit. b bzw.
                    lit. f DSGVO.
                </p>

                <h2>11. Speicherdauer</h2>
                <p>
                    Wir speichern personenbezogene Daten nur so lange, wie es für die genannten Zwecke
                    erforderlich ist oder gesetzliche Aufbewahrungsfristen (insbesondere handels- und
                    steuerrechtliche Fristen) dies vorschreiben. Danach werden die Daten gelöscht.
                </p>

                <h2>12. Ihre Rechte</h2>
                <p>Sie haben nach der DSGVO folgende Rechte:</p>
                <ul>
                    <li>Auskunft über die zu Ihrer Person gespeicherten Daten (Art. 15 DSGVO);</li>
                    <li>Berichtigung unrichtiger Daten (Art. 16 DSGVO);</li>
                    <li>Löschung Ihrer Daten (Art. 17 DSGVO);</li>
                    <li>Einschränkung der Verarbeitung (Art. 18 DSGVO);</li>
                    <li>Datenübertragbarkeit (Art. 20 DSGVO);</li>
                    <li>Widerspruch gegen die Verarbeitung (Art. 21 DSGVO);</li>
                    <li>
                        Widerruf einer erteilten Einwilligung mit Wirkung für die Zukunft (Art. 7 Abs. 3 DSGVO).
                    </li>
                </ul>
                <p>
                    Zur Ausübung Ihrer Rechte genügt eine Nachricht an
                    <a href="mailto:contact@guineeforestiere.de">contact@guineeforestiere.de</a>.
                </p>

                <h2>13. Beschwerderecht bei einer Aufsichtsbehörde</h2>
                <p>
                    Sie haben das Recht, sich bei einer Datenschutz-Aufsichtsbehörde zu beschweren. Die für uns
                    zuständige Behörde ist:<br>
                    Landesbeauftragte für Datenschutz und Informationsfreiheit Nordrhein-Westfalen (LDI NRW),
                    Kavalleriestraße 2–4, 40213 Düsseldorf,
                    <a href="https://www.ldi.nrw.de" target="_blank" rel="noopener noreferrer">www.ldi.nrw.de</a>.
                </p>

                <h2>14. SSL-/TLS-Verschlüsselung</h2>
                <p>
                    Diese Website nutzt aus Sicherheitsgründen und zum Schutz der Übertragung vertraulicher
                    Inhalte eine SSL-/TLS-Verschlüsselung. Eine verschlüsselte Verbindung erkennen Sie an
                    „https://“ in der Adresszeile Ihres Browsers.
                </p>

                <h2>15. Aktualität</h2>
                <p>
                    Diese Datenschutzerklärung ist aktuell gültig. Durch die Weiterentwicklung unserer Website
                    oder aufgrund geänderter gesetzlicher Vorgaben kann eine Anpassung erforderlich werden.
                </p>
            <?php else: ?>
                <h1>Politique de confidentialité</h1>

                <p>
                    La protection de vos données personnelles nous tient à cœur. Nous vous informons
                    ci-après, conformément au Règlement général sur la protection des données (RGPD) et à la
                    loi fédérale allemande sur la protection des données (BDSG), de la collecte, du traitement
                    et de l’utilisation de vos données sur ce site.
                </p>

                <h2>1. Responsable du traitement</h2>
                <p>
                    Responsable au sens de l’art. 4 point 7 du RGPD :<br>
                    Union de la Guinée Forestière en Allemagne (UGFA)<br>
                    <mark>[Nom et prénom complets de la personne habilitée à représenter l’initiative]</mark><br>
                    Leonie-Reygers-Terrasse, 44137 Dortmund, Allemagne<br>
                    Téléphone : <a href="tel:+49151926242516">+49 151 926 242 516</a><br>
                    E-mail : <a href="mailto:contact@guineeforestiere.de">contact@guineeforestiere.de</a>
                </p>

                <h2>2. Généralités sur le traitement des données</h2>
                <p>
                    Nous ne traitons des données personnelles que dans la mesure nécessaire à la mise à
                    disposition d’un site fonctionnel ainsi que de nos contenus et prestations. Le traitement
                    a lieu sur la base du consentement (art. 6 §1 a RGPD), de l’exécution d’un contrat ou de
                    mesures précontractuelles (art. 6 §1 b RGPD), du respect d’obligations légales
                    (art. 6 §1 c RGPD) ou de nos intérêts légitimes (art. 6 §1 f RGPD).
                </p>

                <h2>3. Mise à disposition du site et fichiers journaux</h2>
                <p>
                    À chaque consultation du site, notre hébergeur enregistre automatiquement des informations
                    transmises par votre navigateur (fichiers journaux) : adresse IP, date et heure de l’accès,
                    page/fichier consulté, volume de données transféré, URL de référence, type de navigateur et
                    système d’exploitation. Ces données sont techniquement nécessaires pour afficher le site et
                    garantir sa stabilité et sa sécurité. La base juridique est l’art. 6 §1 f RGPD (intérêt
                    légitime à un fonctionnement sûr et sans perturbation). Les fichiers journaux sont en règle
                    générale conservés jusqu’à 7 jours pour des raisons de sécurité, puis supprimés.
                </p>

                <h2>4. Hébergement</h2>
                <p>
                    Nous hébergeons notre site auprès d’ALL-INKL.COM – Neue Medien Münnich, propriétaire René
                    Münnich, Hauptstraße 68, 02742 Friedersdorf, Allemagne. Le prestataire traite les données
                    précitées pour notre compte. Nous avons conclu avec lui un contrat de sous-traitance
                    conformément à l’art. 28 RGPD. La base juridique est l’art. 6 §1 f RGPD.
                </p>

                <h2>5. Cookies et session</h2>
                <p>
                    Ce site utilise exclusivement des cookies techniquement nécessaires. Nous utilisons un
                    cookie de session afin de garantir le fonctionnement du site (notamment la sécurité contre
                    les attaques CSRF et l’enregistrement de la langue choisie). Ces cookies ne contiennent
                    aucune fonction de suivi ou d’analyse et sont supprimés à la fin de la session ou à la
                    fermeture du navigateur. La base juridique est le § 25 al. 2 TDDDG ainsi que l’art. 6 §1 f
                    RGPD. Aucun cookie de marketing, d’analyse ou de tiers n’est utilisé.
                </p>

                <h2>6. Formulaire de contact et prise de contact</h2>
                <p>
                    Lorsque vous nous contactez via le formulaire de contact ou par e-mail, nous traitons les
                    données que vous indiquez (nom, adresse e-mail, objet, message) afin de traiter votre
                    demande. La base juridique est l’art. 6 §1 a RGPD (consentement) ou l’art. 6 §1 b RGPD si
                    votre demande vise la conclusion d’un contrat. Les données sont supprimées dès que la
                    demande a été traitée et qu’aucune obligation légale de conservation ne s’y oppose.
                </p>

                <h2>7. Inscription à l’événement</h2>
                <p>
                    Pour l’inscription à l’événement, nous collectons le prénom, le nom, le pays de résidence,
                    l’adresse e-mail, le numéro de téléphone, éventuellement l’organisation et le type de
                    participation. Nous traitons ces données pour l’organisation et la réalisation de
                    l’événement ainsi que pour l’envoi de la confirmation de participation. La base juridique
                    est l’art. 6 §1 b RGPD ou l’art. 6 §1 a RGPD. Les données sont supprimées après
                    l’événement, sauf obligation légale de conservation.
                </p>

                <h2>8. Contributions / dons et traitement des paiements</h2>
                <p>
                    En cas de participation volontaire (contribution/don), nous traitons les données que vous
                    indiquez (p.&nbsp;ex. nom, e-mail éventuel, téléphone, montant). La base juridique est
                    l’art. 6 §1 b RGPD. Pour le traitement des paiements, nous utilisons des prestataires
                    externes ; les données de paiement (p.&nbsp;ex. carte bancaire ou coordonnées bancaires)
                    sont saisies et traitées uniquement chez le prestataire de paiement concerné — nous ne
                    recevons pas ces données.
                </p>
                <ul>
                    <li>
                        <strong>PayPal :</strong> PayPal (Europe) S.à r.l. et Cie, S.C.A., 22–24 Boulevard Royal,
                        L-2449 Luxembourg. Un transfert vers des pays tiers (notamment les États-Unis) peut avoir
                        lieu ; PayPal s’appuie sur les clauses contractuelles types. Informations :
                        <a href="https://www.paypal.com/fr/webapps/mpp/ua/privacy-full" target="_blank" rel="noopener noreferrer">paypal.com/fr/webapps/mpp/ua/privacy-full</a>.
                    </li>
                    <li>
                        <strong>Stripe (si activé) :</strong> Stripe Payments Europe, Ltd., 1 Grand Canal Street
                        Lower, Grand Canal Dock, Dublin, Irlande. Informations :
                        <a href="https://stripe.com/fr/privacy" target="_blank" rel="noopener noreferrer">stripe.com/fr/privacy</a>.
                    </li>
                    <li>
                        <strong>Virement bancaire :</strong> en cas de paiement par virement, nous traitons les
                        données figurant sur l’opération (nom, motif) pour l’attribution de la contribution.
                    </li>
                </ul>
                <p>
                    La base juridique de la transmission aux prestataires de paiement est l’art. 6 §1 b RGPD
                    (exécution du contrat).
                </p>

                <h2>9. Demandes de partenariat et de sponsoring</h2>
                <p>
                    En cas de demande de partenariat ou de sponsoring, nous traitons les données de contact et
                    d’organisation indiquées (p.&nbsp;ex. nom de l’organisation, interlocuteur, e-mail,
                    téléphone, site web, logo éventuel) afin de traiter et de répondre à votre demande. La base
                    juridique est l’art. 6 §1 b ou l’art. 6 §1 a RGPD.
                </p>

                <h2>10. Envoi d’e-mails</h2>
                <p>
                    Les e-mails de confirmation et de notification sont envoyés via le service de messagerie de
                    notre hébergeur (ALL-INKL.COM). La base juridique est l’art. 6 §1 b ou f RGPD.
                </p>

                <h2>11. Durée de conservation</h2>
                <p>
                    Nous ne conservons les données personnelles que le temps nécessaire aux finalités indiquées
                    ou imposé par les délais légaux de conservation (notamment commerciaux et fiscaux). Les
                    données sont ensuite supprimées.
                </p>

                <h2>12. Vos droits</h2>
                <p>Vous disposez des droits suivants au titre du RGPD :</p>
                <ul>
                    <li>droit d’accès aux données vous concernant (art. 15 RGPD) ;</li>
                    <li>droit de rectification des données inexactes (art. 16 RGPD) ;</li>
                    <li>droit à l’effacement (art. 17 RGPD) ;</li>
                    <li>droit à la limitation du traitement (art. 18 RGPD) ;</li>
                    <li>droit à la portabilité des données (art. 20 RGPD) ;</li>
                    <li>droit d’opposition au traitement (art. 21 RGPD) ;</li>
                    <li>droit de retirer un consentement à tout moment (art. 7 §3 RGPD).</li>
                </ul>
                <p>
                    Pour exercer vos droits, un message à
                    <a href="mailto:contact@guineeforestiere.de">contact@guineeforestiere.de</a> suffit.
                </p>

                <h2>13. Droit de réclamation auprès d’une autorité de contrôle</h2>
                <p>
                    Vous avez le droit d’introduire une réclamation auprès d’une autorité de contrôle de la
                    protection des données. L’autorité compétente pour nous est :<br>
                    Landesbeauftragte für Datenschutz und Informationsfreiheit Nordrhein-Westfalen (LDI NRW),
                    Kavalleriestraße 2–4, 40213 Düsseldorf,
                    <a href="https://www.ldi.nrw.de" target="_blank" rel="noopener noreferrer">www.ldi.nrw.de</a>.
                </p>

                <h2>14. Chiffrement SSL/TLS</h2>
                <p>
                    Pour des raisons de sécurité et afin de protéger la transmission de contenus confidentiels,
                    ce site utilise un chiffrement SSL/TLS. Une connexion chiffrée est reconnaissable au
                    « https:// » dans la barre d’adresse de votre navigateur.
                </p>

                <h2>15. Actualité</h2>
                <p>
                    La présente politique de confidentialité est actuellement en vigueur. Le développement de
                    notre site ou des modifications légales peuvent rendre nécessaire son adaptation.
                </p>
            <?php endif; ?>
        </article>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
