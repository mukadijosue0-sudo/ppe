"use strict";

// On importe la fonction qui permet d'appeler le serveur (Ajax)
import {appelAjax} from "/composant/fonction/ajax.js";

// -----------------------------------------------------------------------------------
// On récupère les éléments de la page dont on a besoin
// -----------------------------------------------------------------------------------
const rechercheNom = document.getElementById("rechercheNom");           // le champ de saisie
const listeCoureurs = document.getElementById("listeCoureurs");         // la liste des coureurs trouvés
const resultatsCoureur = document.getElementById("resultatsCoureur");   // la zone du tableau de résultats

// -----------------------------------------------------------------------------------
// Quand on tape dans le champ, on lance la recherche
// -----------------------------------------------------------------------------------
rechercheNom.addEventListener("input", () => {
    // On enlève les espaces au début et à la fin
    const debutNom = rechercheNom.value.trim();

    // On vide la liste et le tableau précédents
    listeCoureurs.innerHTML = "";
    resultatsCoureur.innerHTML = "";

    // Il faut au moins 2 lettres pour lancer la recherche
    if (debutNom.length >= 2) {
        rechercherCoureurs(debutNom);
    }
});

// -----------------------------------------------------------------------------------
// Fonctions
// -----------------------------------------------------------------------------------

/**
 * Demande au serveur les coureurs dont le nom commence par les lettres saisies.
 * @param {string} debutNom les premières lettres du nom
 */
function rechercherCoureurs(debutNom) {
    appelAjax({
        url: "ajax/ajax.php",
        data: {action: "recherche", debutNom: debutNom},
        // Quand le serveur répond, on affiche la liste des coureurs
        success: (coureurs) => afficherListeCoureurs(coureurs)
    });
}

/**
 * Affiche la liste des coureurs trouvés. On peut cliquer sur un coureur.
 * @param {Array} coureurs la liste des coureurs reçue du serveur
 */
function afficherListeCoureurs(coureurs) {
    // On vide la liste avant de la remplir
    listeCoureurs.innerHTML = "";

    // Si personne n'a été trouvé, on affiche un message
    if (coureurs.length === 0) {
        const li = document.createElement("li");
        li.className = "list-group-item text-muted";
        li.textContent = "Aucun coureur trouvé.";
        listeCoureurs.appendChild(li);
        return;
    }

    // Pour chaque coureur, on crée une ligne dans la liste
    for (const coureur of coureurs) {
        const li = document.createElement("li");
        li.className = "list-group-item";

        // L'année de naissance est parfois inconnue (vide ou "0") :
        // on l'affiche seulement si on la connaît
        let texte = coureur.nom + " " + coureur.prenom;
        if (coureur.annee && coureur.annee !== "0") {
            texte = texte + " (" + coureur.annee + ")";
        }
        li.textContent = texte;

        // Quand on clique sur le coureur, on le choisit
        li.addEventListener("click", () => choisirCoureur(coureur));

        listeCoureurs.appendChild(li);
    }
}

/**
 * Appelée quand on clique sur un coureur dans la liste.
 * La liste disparaît, le nom du coureur reste dans le champ de recherche
 * et son tableau de résultats s'affiche.
 * @param {Object} coureur le coureur choisi (id, nom, prenom...)
 */
function choisirCoureur(coureur) {
    // On écrit le nom du coureur choisi dans le champ de recherche
    rechercheNom.value = coureur.nom + " " + coureur.prenom;

    // On fait disparaître la liste des coureurs
    listeCoureurs.innerHTML = "";

    // On charge et on affiche ses résultats
    chargerResultats(coureur.id);
}

/**
 * Demande au serveur tous les résultats d'un coureur.
 * @param {number} idCoureur l'identifiant du coureur choisi
 */
function chargerResultats(idCoureur) {
    appelAjax({
        url: "ajax/ajax.php",
        data: {action: "resultats", idCoureur: idCoureur},
        // Quand le serveur répond, on affiche le tableau
        success: (resultats) => afficherResultats(resultats)
    });
}

/**
 * Construit le tableau des résultats d'un coureur.
 * @param {Array} resultats la liste des résultats reçue du serveur
 */
function afficherResultats(resultats) {
    // On vide la zone avant de la remplir
    resultatsCoureur.innerHTML = "";

    // Si le coureur n'a aucun résultat, on affiche un message
    if (resultats.length === 0) {
        resultatsCoureur.textContent = "Aucun résultat trouvé pour ce coureur.";
        return;
    }

    // On crée le tableau
    const table = document.createElement("table");
    table.className = "table";

    // --- L'en-tête : une ligne avec les titres des colonnes ---
    const entete = table.createTHead().insertRow();
    const titres = ["Date", "Saison", "Distance", "Place", "Catégorie", "Place catégorie", "Club", "Temps"];
    for (const titre of titres) {
        const th = document.createElement("th");
        th.textContent = titre;
        entete.appendChild(th);
    }

    // --- Le corps : une ligne par résultat ---
    const corps = table.createTBody();
    for (const resultat of resultats) {
        const ligne = corps.insertRow();
        ligne.insertCell().textContent = resultat.date;
        ligne.insertCell().textContent = resultat.saison;
        ligne.insertCell().textContent = resultat.distance;
        ligne.insertCell().textContent = resultat.place;
        ligne.insertCell().textContent = resultat.categorie;
        ligne.insertCell().textContent = resultat.placeCategorie;
        ligne.insertCell().textContent = resultat.club ?? "";   // le club peut être vide
        ligne.insertCell().textContent = resultat.temps;
    }

    // On place le tableau dans la page
    resultatsCoureur.appendChild(table);
}