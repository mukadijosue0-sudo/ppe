<?php
declare(strict_types=1);

use ClasseTechnique\Requete;
use ClasseTechnique\Select;

/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . '/../bootstrap/bootstrap.php';

// Vérification d'un appel AJAX POST sécurisé
Requete::exigerPost();

// Récupération de l'action demandée
// NB : suppose l'existence de Requete::postString(). Si elle n'existe pas
// encore dans votre classe Requete, remplacez par : $_POST['action'] ?? ''
$action = Requete::postString('action');

$select = new Select();

switch ($action) {

    // Recherche des coureurs par préfixe du nom (2 caractères minimum)
    case 'recherche':
        $prefixe = trim(Requete::postString('prefixe'));

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

    // Résultats détaillés d'un coureur
    case 'resultats':
        $idCoureur = Requete::postInt('idCoureur');

        $sql = "SELECT date, saison, distance, place, categorie, placeCategorie, club, temps
                FROM v_resultats_coureur
                WHERE idCoureur = :idCoureur
                ORDER BY date";

        $lesResultats = $select->getRows($sql, ['idCoureur' => $idCoureur]);
        echo json_encode($lesResultats, JSON_UNESCAPED_UNICODE);
        break;

    default:
        http_response_code(400);
        echo json_encode(['erreur' => 'Action inconnue'], JSON_UNESCAPED_UNICODE);
}