"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------

import {appelAjax} from "/composant/fonction/ajax.js";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------

const rechercheNom = document.getElementById("rechercheNom");
const listeCoureurs = document.getElementById("listeCoureurs");
const resultatsCoureur = document.getElementById("resultatsCoureur");
const lignesPalmares = document.getElementById("lignesPalmares");

// Délai (ms) avant de lancer la recherche après la dernière frappe
const delaiRecherche = 300;
let minuteurRecherche = null;

// -----------------------------------------------------------------------------------
// Événements
// -----------------------------------------------------------------------------------

rechercheNom.addEventListener("input", () => {
    clearTimeout(minuteurRecherche);

    const prefixe = rechercheNom.value.trim();

    listeCoureurs.innerHTML = "";
    resultatsCoureur.innerHTML = "";

    // On attend au moins 2 caractères pour éviter une recherche trop large
    if (prefixe.length < 2) {
        return;
    }

    minuteurRecherche = setTimeout(() => rechercherCoureurs(prefixe), delaiRecherche);
});

// -----------------------------------------------------------------------------------
// Fonctions de traitement
// -----------------------------------------------------------------------------------

/**
 * Lance la recherche des coureurs dont le nom commence par le préfixe donné.
 * @param {string} prefixe
 */
function rechercherCoureurs(prefixe) {
    appelAjax({
        url: "ajax/ajax.php",
        data: {action: "recherche", prefixe: prefixe},
        success: (lesCoureurs) => afficherListeCoureurs(lesCoureurs)
    });
}

/**
 * Affiche la liste des coureurs trouvés, chacun cliquable pour voir ses résultats.
 * @param {Array} lesCoureurs
 */
function afficherListeCoureurs(lesCoureurs) {
    listeCoureurs.innerHTML = "";

    if (lesCoureurs.length === 0) {
        const li = document.createElement("li");
        li.className = "list-group-item text-muted";
        li.textContent = "Aucun coureur trouvé.";
        listeCoureurs.appendChild(li);
        return;
    }

    for (const coureur of lesCoureurs) {
        const li = document.createElement("li");
        li.className = "list-group-item list-group-item-action";

        const annee = (coureur.annee && coureur.annee !== "0") ? ` (${coureur.annee})` : "";
        li.textContent = `${coureur.nom} ${coureur.prenom}${annee}`;

        li.addEventListener("click", () => afficherResultats(coureur.id));
        listeCoureurs.appendChild(li);
    }
}

/**
 * Charge et affiche l'ensemble des résultats d'un coureur.
 * @param {number} idCoureur
 */
function afficherResultats(idCoureur) {
    appelAjax({
        url: "ajax/ajax.php",
        data: {action: "resultats", idCoureur: idCoureur},
        success: (lesResultats) => construireTableauResultats(lesResultats)
    });
}

/**
 * Construit et insère le tableau des résultats d'un coureur.
 * @param {Array} lesResultats
 */
function construireTableauResultats(lesResultats) {
    resultatsCoureur.innerHTML = "";

    if (lesResultats.length === 0) {
        resultatsCoureur.textContent = "Aucun résultat trouvé pour ce coureur.";
        return;
    }

    const table = document.createElement("table");
    table.className = "table";

    // En-tête
    const thead = table.createTHead();
    const trHead = thead.insertRow();
    for (const colonne of ["Date", "Saison", "Distance", "Place", "Catégorie", "Place catégorie", "Club", "Temps"]) {
        const th = document.createElement("th");
        th.textContent = colonne;
        trHead.appendChild(th);
    }

    // Corps
    const tbody = table.createTBody();
    for (const resultat of lesResultats) {
        const tr = tbody.insertRow();
        tr.insertCell().textContent = resultat.date;
        tr.insertCell().textContent = resultat.saison;
        tr.insertCell().textContent = resultat.distance;
        tr.insertCell().textContent = resultat.place;
        tr.insertCell().textContent = resultat.categorie;
        tr.insertCell().textContent = resultat.placeCategorie;
        tr.insertCell().textContent = resultat.club ?? "";
        tr.insertCell().textContent = resultat.temps;
    }

    resultatsCoureur.appendChild(table);
}

/**
 * Charge le palmarès en Ajax et l'affiche.
 */
function chargerPalmares() {
    appelAjax({
        url: "ajax/ajax.php",
        data: {action: "palmares"},
        success: (lesPalmares) => afficherPalmares(lesPalmares)
    });
}

/**
 * Affiche le palmarès dans le tableau.
 * @param {Array} lesPalmares
 */
function afficherPalmares(lesPalmares) {
    lignesPalmares.innerHTML = "";

    let rang = 1;
    for (const ligne of lesPalmares) {
        const tr = lignesPalmares.insertRow();
        tr.insertCell().textContent = rang++;
        tr.insertCell().textContent = ligne.nom;
        tr.insertCell().textContent = ligne.prenom;
        tr.insertCell().textContent = ligne.nbVictoires;
    }
}

// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------

chargerPalmares();