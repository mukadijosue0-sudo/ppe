"use strict";

// On importe la fonction qui permet d'appeler le serveur (Ajax)
import {appelAjax} from "/composant/fonction/ajax.js";

// -----------------------------------------------------------------------------------
// On récupère les éléments de la page dont on a besoin
// -----------------------------------------------------------------------------------
const lignesPalmares = document.getElementById("lignesPalmares");   // le corps du tableau
const filtreSexe = document.getElementById("filtreSexe");           // la liste Catégorie
const filtreDistance = document.getElementById("filtreDistance");   // la liste Distance
const filtreSaison = document.getElementById("filtreSaison");       // la liste Saison

// -----------------------------------------------------------------------------------
// Quand on change un filtre, on recharge le palmarès
// -----------------------------------------------------------------------------------
filtreSexe.addEventListener("change", chargerPalmares);
filtreDistance.addEventListener("change", chargerPalmares);
filtreSaison.addEventListener("change", chargerPalmares);

// -----------------------------------------------------------------------------------
// Fonctions
// -----------------------------------------------------------------------------------

/**
 * Demande le palmarès au serveur, en envoyant les 3 filtres choisis.
 */
function chargerPalmares() {
    appelAjax({
        url: "ajax/ajax.php",
        data: {
            action: "palmares",
            sexe: filtreSexe.value,
            distance: filtreDistance.value,
            saison: filtreSaison.value
        },
        // Quand le serveur a répondu, on affiche le palmarès reçu
        success: (palmares) => afficherPalmares(palmares)
    });
}

/**
 * Remplit le tableau avec le palmarès reçu.
 * @param {Array} palmares la liste des coureurs avec leur nombre de victoires
 */
function afficherPalmares(palmares) {
    // On vide le tableau avant de le remplir
    lignesPalmares.innerHTML = "";

    // Le rang commence à 1 et augmente à chaque ligne
    let rang = 1;

    // Pour chaque coureur, on ajoute une ligne avec 4 cellules
    for (const coureur of palmares) {
        const ligne = lignesPalmares.insertRow();
        ligne.insertCell().textContent = rang;
        ligne.insertCell().textContent = coureur.nom;
        ligne.insertCell().textContent = coureur.prenom;
        ligne.insertCell().textContent = coureur.nbVictoires;
        rang++;
    }
}

// -----------------------------------------------------------------------------------
// Au chargement de la page, on affiche le palmarès complet (sans filtre)
// -----------------------------------------------------------------------------------
chargerPalmares();