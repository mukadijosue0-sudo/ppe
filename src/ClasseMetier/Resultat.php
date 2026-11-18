<?php
declare(strict_types=1);

namespace ClasseMetier;

use ClasseTechnique\Select;

/**
 * Classe métier : tout ce qui concerne les résultats des courses.
 * C'est ici qu'on écrit le SQL, pas dans les fichiers ajax.
 */
class Resultat
{
    /**
     * Recherche les coureurs dont le nom commence par les lettres saisies.
     *
     * @param string $debutNom les premières lettres du nom (2 lettres minimum)
     * @return array un tableau de coureurs : id, nom, prenom, annee, sexe
     */
    public static function rechercherCoureurs(string $debutNom): array
    {
        // On enlève les espaces au début et à la fin
        $debutNom = trim($debutNom);

        // Moins de 2 lettres : la recherche serait trop large, on ne cherche pas
        if (mb_strlen($debutNom) < 2) {
            return [];
        }

        // Dans un LIKE, deux caractères sont spéciaux :
        //   %  veut dire "n'importe quoi"
        //   _  veut dire "n'importe quelle lettre"
        // Si on laisse l'utilisateur les taper, "%%" ramène tous les coureurs.
        // On met donc un antislash devant pour qu'ils soient cherchés comme de simples caractères.
        $debutNom = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $debutNom);

        // LIKE 'Dup%' : le nom commence par "Dup"
        // LIMIT 30 : on ne renvoie pas plus de 30 coureurs
        $sql = "SELECT id, nom, prenom, annee, sexe
                FROM coureur
                WHERE nom LIKE CONCAT(:debutNom, '%')
                ORDER BY nom, prenom
                LIMIT 30";

        $select = new Select();
        return $select->getRows($sql, ['debutNom' => $debutNom]);
    }

    /**
     * Retourne tous les résultats d'un coureur, du plus ancien au plus récent.
     *
     * @param int $idCoureur l'identifiant du coureur
     * @return array un tableau de résultats : date, saison, distance, place,
     *               categorie, placeCategorie, club, temps
     */
    public static function getResultatsCoureur(int $idCoureur): array
    {
        // On relie la table resultat (r) à la table course (cr)
        // pour avoir aussi la date, la saison et la distance de chaque course
        $sql = "SELECT cr.date, cr.saison, cr.distance,
                       r.place, r.categorie, r.placeCategorie, r.club, r.temps
                FROM resultat r
                INNER JOIN course cr ON cr.id = r.idCourse
                WHERE r.idCoureur = :idCoureur
                ORDER BY cr.date";

        $select = new Select();
        return $select->getRows($sql, ['idCoureur' => $idCoureur]);
    }

    /**
     * Retourne le palmarès : le nombre de victoires de chaque coureur.
     *
     * Chaque paramètre est un filtre. S'il est vide (''), on ne filtre pas.
     *
     * @param string $sexe     'F' (filles), 'M' (garçons) ou '' (tous)
     * @param string $distance '5 km', '10 km' ou '' (toutes)
     * @param string $saison   'printemps', 'ete', 'automne', 'hiver' ou '' (toutes)
     * @return array un tableau de lignes : idCoureur, nom, prenom, nbVictoires
     */
    public static function getPalmares(string $sexe, string $distance, string $saison): array
    {
        // On prépare la liste des conditions et leurs valeurs.
        // Elles s'ajoutent à la requête seulement si le filtre est utilisé.
        $conditions = [];
        $valeurs = [];

        if ($sexe === 'F' || $sexe === 'M') {
            $conditions[] = 't.sexe = :sexe';
            $valeurs['sexe'] = $sexe;
        }

        if ($distance === '5 km' || $distance === '10 km') {
            $conditions[] = 't.distance = :distance';
            $valeurs['distance'] = $distance;
        }

        if (in_array($saison, ['printemps', 'ete', 'automne', 'hiver'])) {
            $conditions[] = 't.saison = :saison';
            $valeurs['saison'] = $saison;
        }

        // On colle les conditions avec AND.
        // Exemple : " AND t.sexe = :sexe AND t.saison = :saison"
        $where = '';
        if (count($conditions) > 0) {
            $where = ' AND ' . implode(' AND ', $conditions);
        }

        // Comment savoir qui a gagné une course ?
        // - sans filtre de sexe : le gagnant est le 1er du classement général
        // - avec un sexe : le gagnant est le 1er parmi les filles (ou les garçons)
        // PARTITION BY dit à SQL de faire un classement séparé pour chaque groupe.
        if ($sexe === 'F' || $sexe === 'M') {
            $groupe = 'r.idCourse, c.sexe';   // un classement par course ET par sexe
        } else {
            $groupe = 'r.idCourse';           // un classement par course
        }

        // La requête fonctionne en 2 étapes :
        // 1- la sous-requête "t" donne à chaque résultat son rang dans sa course
        // 2- la requête principale garde les rangs 1 (les victoires) et les compte
        $sql = "SELECT t.idCoureur, t.nom, t.prenom, COUNT(*) AS nbVictoires
                FROM (
                    SELECT c.id AS idCoureur, c.nom, c.prenom, c.sexe,
                           cr.distance, cr.saison,
                           RANK() OVER (PARTITION BY $groupe ORDER BY r.place) AS rang
                    FROM resultat r
                    INNER JOIN coureur c ON c.id = r.idCoureur
                    INNER JOIN course cr ON cr.id = r.idCourse
                ) t
                WHERE t.rang = 1 $where
                GROUP BY t.idCoureur, t.nom, t.prenom
                ORDER BY nbVictoires DESC, t.nom, t.prenom";

        // On exécute la requête et on renvoie les lignes trouvées
        $select = new Select();
        return $select->getRows($sql, $valeurs);
    }
}