<?php
declare(strict_types=1);

use ClasseTechnique\Page;

/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . "/../bootstrap/bootstrap.php";

// génération de la page (le palmarès est chargé en Ajax par index.js)
$page = new Page();

$page->setTitre("Résultats et palmarès")
    ->afficher();