<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = t('seo.impressum_title');
$pageDescription = t('impressum.title');

$lang = current_lang();

require __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <article class="card legal-page">
            <?php if ($lang === 'de'): ?>
                <h1>Impressum</h1>

                <h2>Angaben gemäß § 5 DDG</h2>
                <p>
                    Union de la Guinée Forestière en Allemagne (UGFA)<br>
                    Nicht eingetragene Initiative<br>
                    Leonie-Reygers-Terrasse<br>
                    44137 Dortmund<br>
                    Deutschland
                </p>

                <h2>Vertreten durch</h2>
                <p>
                    <mark>[Vollständiger Vor- und Nachname der vertretungsberechtigten Person]</mark>
                </p>

                <h2>Kontakt</h2>
                <p>
                    Telefon: <a href="tel:+49151926242516">+49 151 926 242 516</a><br>
                    E-Mail: <a href="mailto:contact@ugfa-ev.org">contact@ugfa-ev.org</a><br>
                    Web: <a href="https://ugfa-ev.org">ugfa-ev.org</a>
                </p>

                <h2>Verantwortlich für den Inhalt nach § 18 Abs. 2 MStV</h2>
                <p>
                    <mark>[Vollständiger Vor- und Nachname]</mark><br>
                    Leonie-Reygers-Terrasse, 44137 Dortmund
                </p>

                <h2>Haftung für Inhalte</h2>
                <p>
                    Als Diensteanbieter sind wir gemäß § 7 Abs. 1 DDG für eigene Inhalte auf diesen Seiten
                    nach den allgemeinen Gesetzen verantwortlich. Nach §§ 8 bis 10 DDG sind wir als
                    Diensteanbieter jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde
                    Informationen zu überwachen oder nach Umständen zu forschen, die auf eine rechtswidrige
                    Tätigkeit hinweisen. Verpflichtungen zur Entfernung oder Sperrung der Nutzung von
                    Informationen nach den allgemeinen Gesetzen bleiben hiervon unberührt. Eine diesbezügliche
                    Haftung ist jedoch erst ab dem Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung
                    möglich. Bei Bekanntwerden von entsprechenden Rechtsverletzungen werden wir diese Inhalte
                    umgehend entfernen.
                </p>

                <h2>Haftung für Links</h2>
                <p>
                    Unser Angebot enthält Links zu externen Websites Dritter, auf deren Inhalte wir keinen
                    Einfluss haben. Deshalb können wir für diese fremden Inhalte auch keine Gewähr übernehmen.
                    Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der
                    Seiten verantwortlich. Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung auf
                    mögliche Rechtsverstöße überprüft. Rechtswidrige Inhalte waren zum Zeitpunkt der
                    Verlinkung nicht erkennbar. Eine permanente inhaltliche Kontrolle der verlinkten Seiten ist
                    jedoch ohne konkrete Anhaltspunkte einer Rechtsverletzung nicht zumutbar. Bei Bekanntwerden
                    von Rechtsverletzungen werden wir derartige Links umgehend entfernen.
                </p>

                <h2>Urheberrecht</h2>
                <p>
                    Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen
                    dem deutschen Urheberrecht. Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art der
                    Verwertung außerhalb der Grenzen des Urheberrechtes bedürfen der schriftlichen Zustimmung
                    des jeweiligen Autors bzw. Erstellers. Downloads und Kopien dieser Seite sind nur für den
                    privaten, nicht kommerziellen Gebrauch gestattet. Soweit die Inhalte auf dieser Seite nicht
                    vom Betreiber erstellt wurden, werden die Urheberrechte Dritter beachtet.
                </p>

                <h2>Streitschlichtung</h2>
                <p>
                    Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit:
                    <a href="https://ec.europa.eu/consumers/odr/" target="_blank" rel="noopener noreferrer">https://ec.europa.eu/consumers/odr/</a>.
                    Unsere E-Mail-Adresse finden Sie oben im Impressum. Wir sind nicht bereit und nicht
                    verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle
                    teilzunehmen.
                </p>

                <h2>Hosting</h2>
                <p>
                    Diese Website wird bei der ALL-INKL.COM – Neue Medien Münnich, Inhaber René Münnich,
                    Hauptstraße 68, 02742 Friedersdorf, Deutschland, gehostet. Weitere Informationen zur
                    Datenverarbeitung finden Sie in unserer
                    <a href="<?= e(base_url('privacy.php')) ?>">Datenschutzerklärung</a>.
                </p>
            <?php else: ?>
                <h1>Mentions légales / Impressum</h1>

                <h2>Informations conformément au § 5 DDG (loi allemande sur les services numériques)</h2>
                <p>
                    Union de la Guinée Forestière en Allemagne (UGFA)<br>
                    Initiative non enregistrée<br>
                    Leonie-Reygers-Terrasse<br>
                    44137 Dortmund<br>
                    Allemagne
                </p>

                <h2>Représentée par</h2>
                <p>
                    <mark>[Nom et prénom complets de la personne habilitée à représenter l’initiative]</mark>
                </p>

                <h2>Contact</h2>
                <p>
                    Téléphone : <a href="tel:+49151926242516">+49 151 926 242 516</a><br>
                    E-mail : <a href="mailto:contact@ugfa-ev.org">contact@ugfa-ev.org</a><br>
                    Web : <a href="https://ugfa-ev.org">ugfa-ev.org</a>
                </p>

                <h2>Responsable du contenu selon le § 18 al. 2 MStV</h2>
                <p>
                    <mark>[Nom et prénom complets]</mark><br>
                    Leonie-Reygers-Terrasse, 44137 Dortmund
                </p>

                <h2>Responsabilité concernant les contenus</h2>
                <p>
                    En tant que prestataire de services, nous sommes responsables de nos propres contenus sur
                    ces pages conformément au § 7 al. 1 DDG et au droit commun. Conformément aux §§ 8 à 10 DDG,
                    nous ne sommes toutefois pas tenus de surveiller les informations de tiers transmises ou
                    stockées, ni de rechercher des circonstances indiquant une activité illicite. Les
                    obligations de suppression ou de blocage de l’utilisation d’informations en vertu du droit
                    commun restent inchangées. Une responsabilité à cet égard n’est cependant possible qu’à
                    partir du moment où une violation concrète du droit est connue. Dès que de telles
                    violations nous seront signalées, nous supprimerons immédiatement ces contenus.
                </p>

                <h2>Responsabilité concernant les liens</h2>
                <p>
                    Notre offre contient des liens vers des sites web externes de tiers, sur le contenu
                    desquels nous n’avons aucune influence. Nous ne pouvons donc assumer aucune responsabilité
                    pour ces contenus externes. Le fournisseur ou l’exploitant respectif des pages est toujours
                    responsable du contenu des pages liées. Les pages liées ont été vérifiées au moment de
                    l’établissement du lien quant à d’éventuelles violations du droit. Aucun contenu illicite
                    n’était détectable au moment de l’établissement du lien. Dès que des violations du droit
                    nous seront signalées, nous supprimerons immédiatement de tels liens.
                </p>

                <h2>Droit d’auteur</h2>
                <p>
                    Les contenus et œuvres créés par l’exploitant du site sur ces pages sont soumis au droit
                    d’auteur allemand. La reproduction, l’adaptation, la diffusion et toute forme d’exploitation
                    en dehors des limites du droit d’auteur nécessitent l’accord écrit de l’auteur ou du
                    créateur respectif. Les téléchargements et copies de cette page ne sont autorisés que pour
                    un usage privé et non commercial.
                </p>

                <h2>Règlement des litiges</h2>
                <p>
                    La Commission européenne met à disposition une plateforme de règlement en ligne des litiges
                    (RLL) :
                    <a href="https://ec.europa.eu/consumers/odr/" target="_blank" rel="noopener noreferrer">https://ec.europa.eu/consumers/odr/</a>.
                    Vous trouverez notre adresse e-mail ci-dessus. Nous ne sommes ni disposés ni tenus de
                    participer à une procédure de règlement des litiges devant un organe de conciliation des
                    consommateurs.
                </p>

                <h2>Hébergement</h2>
                <p>
                    Ce site est hébergé par ALL-INKL.COM – Neue Medien Münnich, propriétaire René Münnich,
                    Hauptstraße 68, 02742 Friedersdorf, Allemagne. Vous trouverez de plus amples informations
                    sur le traitement des données dans notre
                    <a href="<?= e(base_url('privacy.php')) ?>">politique de confidentialité</a>.
                </p>
            <?php endif; ?>
        </article>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
