<?php
declare(strict_types=1);

use ClasseMetier\Resultat;
use ClasseTechnique\Requete;

require $_SERVER['DOCUMENT_ROOT'] . '/../bootstrap/bootstrap.php';

// On accepte seulement les appels en POST
Requete::exigerPost();

// Quelle action le JavaScript demande-t-il ?
$action = $_POST['action'] ?? '';

if ($action === 'palmares') {
    // On récupère les 3 filtres envoyés par le JavaScript
    $sexe     = $_POST['sexe'] ?? '';
    $distance = $_POST['distance'] ?? '';
    $saison   = $_POST['saison'] ?? '';

    // La classe métier fait la requête SQL et renvoie le palmarès
    $palmares = Resultat::getPalmares($sexe, $distance, $saison);

    // On renvoie le résultat au JavaScript au format JSON
    echo json_encode($palmares, JSON_UNESCAPED_UNICODE);
} else {
    // Action inconnue : on répond avec une erreur
    http_response_code(400);
    echo json_encode(['erreur' => 'Action inconnue']);
}