
"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------

import {getData} from "/composant/fonction/page.js";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------

/*global html2pdf */

const lesCategories = getData("lesCategories");

const lesLignes = document.getElementById('lesLignes');
const modeleLigne = document.getElementById('modeleLigne');
const btnPdf = document.getElementById('btnPdf');

// -----------------------------------------------------------------------------------
// Procédures évènementielles
// -----------------------------------------------------------------------------------

btnPdf.addEventListener("click", () => {
    const element = document.getElementById("pdfContent");

    const opt = {
        margin: 0.5,
        filename: 'categories.pdf',
        image: {type: 'jpeg', quality: 0.98},
        html2canvas: {scale: 2},
        jsPDF: {
            unit: 'in',
            format: 'a4',
            orientation: 'portrait'
        }
    };

    html2pdf().set(opt).from(element).save();
});

// -----------------------------------------------------------------------------------
// Fonctions de traitement
// -----------------------------------------------------------------------------------

/**
 * Crée une ligne représentant une catégorie.
 * @param {Object} categorie
 * @returns {HTMLTableRowElement}
 */
function creerLigneCategorie(categorie) {

    // Cloner le modèle HTML
    const tr = modeleLigne.content.firstElementChild.cloneNode(true);

    // Alimenter les cellules
    tr.querySelector('.col-code').textContent = categorie.id;
    tr.querySelector('.col-nom').textContent = categorie.nom;
    tr.querySelector('.col-age').textContent = categorie.age;
    tr.querySelector('.col-annee').textContent = categorie.annee;
    tr.querySelector('.col-distance').textContent = categorie.distance;

    return tr;
}

// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------

lesLignes.replaceChildren();

for (const categorie of lesCategories) {
    lesLignes.appendChild(creerLigneCategorie(categorie));
}
