<?php
declare(strict_types=1);

use ClasseTechnique\Requete;
use ClasseTechnique\Select;

/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . '/../bootstrap/bootstrap.php';

// Vérification d'un appel AJAX POST sécurisé
Requete::exigerPost();

// Récupération de l'action demandée
$action = $_POST['action'] ?? '';

$select = new Select();

// TEMPORAIRE — à retirer une fois le bug trouvé : affiche la vraie erreur
try {

    switch ($action) {

        // Recherche des coureurs par préfixe du nom (2 caractères minimum)
        case 'recherche':
            $prefixe = trim($_POST['prefixe'] ?? '');

            if (mb_strlen($prefixe) < 2) {
                echo json_encode([], JSON_UNESCAPED_UNICODE);
                break;
            }

            $sql = "SELECT id, nom, prenom, annee, sexe
                FROM coureur
                WHERE nom LIKE CONCAT(:prefixe, '%')
                ORDER BY nom, prenom
                LIMIT 30";

            $lesCoureurs = $select->getRows($sql, ['prefixe' => $prefixe]);
            echo json_encode($lesCoureurs, JSON_UNESCAPED_UNICODE);
            break;

        // Palmarès : nombre de victoires par coureur
        case 'palmares':
            $sexe     = trim($_POST['sexe'] ?? '');
            $distance = trim($_POST['distance'] ?? '');
            $saison   = trim($_POST['saison'] ?? '');
            $annee    = trim($_POST['annee'] ?? '');

            // Sans filtre de sexe, la victoire est la 1re place du classement général ;
            // avec un sexe, c'est la meilleure place parmi les coureurs de ce sexe.
            $partition = ($sexe === '') ? 'r.idCourse' : 'r.idCourse, c.sexe';

            $conditions = [];
            $params = [];
            if ($sexe !== '') {
                $conditions[] = 't.sexe = :sexe';
                $params['sexe'] = $sexe;
            }
            if (in_array($distance, ['5 km', '10 km'], true)) {
                $conditions[] = 't.distance = :distance';
                $params['distance'] = $distance;
            }
            if (in_array($saison, ['printemps', 'ete', 'automne', 'hiver'], true)) {
                $conditions[] = 't.saison = :saison';
                $params['saison'] = $saison;
            }
            if (preg_match('/^\d{4}$/', $annee) === 1) {
                $conditions[] = 't.annee = :annee';
                $params['annee'] = (int) $annee;
            }
            $where = $conditions ? ' AND ' . implode(' AND ', $conditions) : '';

            $sql = "SELECT t.idCoureur, t.nom, t.prenom, COUNT(*) AS nbVictoires
        FROM (
            SELECT c.id AS idCoureur, c.nom, c.prenom, c.sexe, cr.distance,
                   cr.saison, YEAR(cr.date) AS annee,
                   RANK() OVER (PARTITION BY $partition ORDER BY r.place) AS rang
            FROM resultat r
            INNER JOIN coureur c ON c.id = r.idCoureur
            INNER JOIN course cr ON cr.id = r.idCourse
        ) t
        WHERE t.rang = 1 $where
        GROUP BY t.idCoureur, t.nom, t.prenom
        ORDER BY nbVictoires DESC, t.nom, t.prenom";

            echo json_encode($select->getRows($sql, $params), JSON_UNESCAPED_UNICODE);
            break;

        // Résultats détaillés d'un coureur
        case 'resultats':
            $idCoureur = (int) ($_POST['idCoureur'] ?? 0);

            $sql = "SELECT cr.date, cr.saison, cr.distance,
                       r.place, r.categorie, r.placeCategorie, r.club, r.temps
                FROM resultat r
                INNER JOIN course cr ON cr.id = r.idCourse
                WHERE r.idCoureur = :idCoureur
                ORDER BY cr.date";

            $lesResultats = $select->getRows($sql, ['idCoureur' => $idCoureur]);
            echo json_encode($lesResultats, JSON_UNESCAPED_UNICODE);
            break;

        default:
            http_response_code(400);
            echo json_encode(['erreur' => 'Action inconnue'], JSON_UNESCAPED_UNICODE);
    }

} catch (\Throwable $e) {
    // TEMPORAIRE — montre la vraie erreur pour debug
    http_response_code(500);
    echo json_encode([
        'debug_message' => $e->getMessage(),
        'debug_fichier' => $e->getFile(),
        'debug_ligne' => $e->getLine(),
    ], JSON_UNESCAPED_UNICODE);
}
// FIN TEMPORAIRE