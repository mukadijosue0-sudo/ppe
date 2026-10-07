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
            $sql = "SELECT c.id AS idCoureur, c.nom, c.prenom, COUNT(*) AS nbVictoires
                FROM coureur c
                INNER JOIN resultat r ON r.idCoureur = c.id
                WHERE r.place = 1
                GROUP BY c.id, c.nom, c.prenom
                ORDER BY nbVictoires DESC";

            $lesPalmares = $select->getRows($sql);
            echo json_encode($lesPalmares, JSON_UNESCAPED_UNICODE);
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