<?php
declare(strict_types=1);

use ClasseTechnique\Page;

require $_SERVER['DOCUMENT_ROOT'] . "/../bootstrap/bootstrap.php";

// On génère la page.
// Le contenu est dans index.html, et index.js gère la recherche.
$page = new Page();

$page->setTitre("Résultats d'un coureur")
    ->avecJeton()
    ->afficher();