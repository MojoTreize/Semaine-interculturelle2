<?php

declare(strict_types=1);

if (!function_exists('fallback_program_items')) {
    function fallback_program_items(string $lang = 'fr'): array
    {
        $items = [
            ['date' => '2026-07-04', 'start' => '09:00:00', 'end' => '11:00:00', 'type' => 'ceremony', 'location' => 'Dortmund Centrum',
                'title_fr' => 'Ceremonie d\'ouverture officielle', 'title_de' => 'Offizielle Eroffnungszeremonie',
                'desc_fr' => 'Lancement institutionnel de la semaine de dialogue interculturel.',
                'desc_de' => 'Institutioneller Start der interkulturellen Dialogwoche.'],
            ['date' => '2026-07-05', 'start' => '10:00:00', 'end' => '12:00:00', 'type' => 'conference', 'location' => 'Salle Konrad-Adenauer',
                'title_fr' => 'Conference: Simandou 2040 et enjeux locaux', 'title_de' => 'Konferenz: Simandou 2040 und lokale Herausforderungen',
                'desc_fr' => 'Perspectives economiques et environnementales pour la Guinee Forestiere.',
                'desc_de' => 'Wirtschaftliche und okologische Perspektiven fur Guinee Forestiere.'],
            ['date' => '2026-07-06', 'start' => '14:00:00', 'end' => '16:00:00', 'type' => 'workshop', 'location' => 'Innovation Hub Dortmund',
                'title_fr' => 'Atelier jeunesse et entrepreneuriat', 'title_de' => 'Workshop Jugend und Unternehmertum',
                'desc_fr' => 'Formation sur les modeles economiques durables et inclusifs.',
                'desc_de' => 'Training zu nachhaltigen und inklusiven Wirtschaftsmodellen.'],
            ['date' => '2026-07-07', 'start' => '14:00:00', 'end' => '16:00:00', 'type' => 'panel', 'location' => 'Westfalen Forum',
                'title_fr' => 'Panel diaspora-investissement', 'title_de' => 'Diaspora-Investitionspanel',
                'desc_fr' => 'Mecanismes de contribution de la diaspora au developpement regional.',
                'desc_de' => 'Beitragsmechanismen der Diaspora zur regionalen Entwicklung.'],
            ['date' => '2026-07-08', 'start' => '11:00:00', 'end' => '13:00:00', 'type' => 'conference', 'location' => 'Dortmund Centrum',
                'title_fr' => 'Gouvernance locale et transparence', 'title_de' => 'Lokale Governance und Transparenz',
                'desc_fr' => 'Dialogue entre acteurs publics, prives et communautaires.',
                'desc_de' => 'Dialog zwischen offentlichen, privaten und gesellschaftlichen Akteuren.'],
            ['date' => '2026-07-09', 'start' => '13:00:00', 'end' => '18:00:00', 'type' => 'exhibition', 'location' => 'Expo Hall Dortmund',
                'title_fr' => 'Exposition culturelle et economique', 'title_de' => 'Kulturelle und wirtschaftliche Ausstellung',
                'desc_fr' => 'Promotion des initiatives culturelles, artisanales et technologiques.',
                'desc_de' => 'Prasentation kultureller, handwerklicher und technologischer Initiativen.'],
            ['date' => '2026-07-10', 'start' => '10:30:00', 'end' => '12:30:00', 'type' => 'workshop', 'location' => 'Maison des Associations',
                'title_fr' => 'Atelier financement de projets territoriaux', 'title_de' => 'Workshop Finanzierung territorialer Projekte',
                'desc_fr' => 'Montage de projets et partenariats transnationaux.',
                'desc_de' => 'Projektaufbau und transnationale Partnerschaften.'],
            ['date' => '2026-07-11', 'start' => '15:00:00', 'end' => '17:00:00', 'type' => 'panel', 'location' => 'Business Club Dortmund',
                'title_fr' => 'Panel femmes, leadership et innovation', 'title_de' => 'Panel Frauen, Leadership und Innovation',
                'desc_fr' => 'Valorisation du leadership feminin dans la transformation locale.',
                'desc_de' => 'Wertschaetzung weiblicher Fuehrung in der lokalen Transformation.'],
            ['date' => '2026-07-12', 'start' => '18:30:00', 'end' => '21:00:00', 'type' => 'networking', 'location' => 'Business Club Dortmund',
                'title_fr' => 'Soiree networking Afrique-Allemagne', 'title_de' => 'Afrika-Deutschland Networking-Abend',
                'desc_fr' => 'Mise en relation institutions, investisseurs et acteurs de terrain.',
                'desc_de' => 'Vernetzung von Institutionen, Investoren und lokalen Akteuren.'],
            ['date' => '2026-07-13', 'start' => '11:00:00', 'end' => '13:00:00', 'type' => 'ceremony', 'location' => 'Dortmund Centrum',
                'title_fr' => 'Cloture et feuille de route 2026-2030', 'title_de' => 'Abschluss und Roadmap 2026-2030',
                'desc_fr' => 'Presentation des recommandations finales et engagements.',
                'desc_de' => 'Vorstellung der finalen Empfehlungen und Verpflichtungen.'],
        ];

        return array_map(static function (array $item) use ($lang): array {
            return [
                'id' => null,
                'event_date' => $item['date'],
                'start_time' => $item['start'],
                'end_time' => $item['end'],
                'location' => $item['location'],
                'item_type' => $item['type'],
                'title' => $lang === 'de' ? $item['title_de'] : $item['title_fr'],
                'description' => $lang === 'de' ? $item['desc_de'] : $item['desc_fr'],
            ];
        }, $items);
    }
}
