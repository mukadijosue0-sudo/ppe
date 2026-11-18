<?php
declare(strict_types=1);

use ClasseTechnique\Page;

require $_SERVER['DOCUMENT_ROOT'] . "/../bootstrap/bootstrap.php";

// On génère la page.
// Le contenu est dans index.html, et index.js charge le palmarès.
$page = new Page();

$page->setTitre("Palmarès")
    ->avecJeton()
    ->afficher();