<?php
declare(strict_types=1);

use ClasseMetier\Resultat;
use ClasseTechnique\Requete;

require $_SERVER['DOCUMENT_ROOT'] . '/../bootstrap/bootstrap.php';

// On accepte seulement les appels en POST
Requete::exigerPost();

// Quelle action le JavaScript demande-t-il ?
$action = $_POST['action'] ?? '';

if ($action === 'recherche') {
    // Action 1 : chercher les coureurs dont le nom commence par les lettres saisies
    $debutNom = $_POST['debutNom'] ?? '';
    $coureurs = Resultat::rechercherCoureurs($debutNom);
    echo json_encode($coureurs, JSON_UNESCAPED_UNICODE);

} elseif ($action === 'resultats') {
    // Action 2 : récupérer tous les résultats d'un coureur
    // (int) transforme la valeur reçue en nombre entier
    $idCoureur = (int) ($_POST['idCoureur'] ?? 0);
    $resultats = Resultat::getResultatsCoureur($idCoureur);
    echo json_encode($resultats, JSON_UNESCAPED_UNICODE);

} else {
    // Action inconnue : on répond avec une erreur
    http_response_code(400);
    echo json_encode(['erreur' => 'Action inconnue']);
}