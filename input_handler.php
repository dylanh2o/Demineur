<?php

class data_handler
{
    public $mode = "";
    public $grille = array();
    public $celluleValeur = array();
    public $celluleMines = array();
    public $celluleVisible = array();
    public $celluleMarquer = array();
    public $difficulte = "facile";
    public $numLigne;
    public $numColonne;
    public $numMines;
    public $caseCliquer;
    public $marqueToggle;

    function __construct()
    {

        if (isset($_POST['mode'])) {
            $this->mode = $_POST['mode'];
            if ($this->mode == "jeu") {
                $this->caseCliquer = $_POST['caseCliquer'];
                $this->numLigne = $_POST['numLigne'];
                $this->numColonne = $_POST['numColonne'];
                $this->numMines = $_POST['numMines'];

                if (!isset($_POST['celluleValeur'])) {
                    $this->grille = unserialize($_POST['grille']);
                    $this->genereValeur();
                    $this->jouerJeu();
                } else {
                    $this->grille = unserialize($_POST['grille']);
                    $this->celluleValeur = unserialize($_POST['celluleValeur']);
                    $this->celluleMines = unserialize($_POST['celluleMines']);
                    $this->celluleVisible = unserialize($_POST['celluleVisible']);
                    $this->celluleMarquer = unserialize($_POST['celluleMarquer']);
                    if (isset($_POST['marqueToggle'])) {
                        $this->marqueToggle = $_POST['marqueToggle'];
                    } else {
                        $this->marqueToggle = false;
                    }
                    $this->jouerJeu();
                }
            }
            if ($this->mode == "commencer") {
                $this->genereGrille();
            }
        } else {
            $this->mode = "nouveau";
        }
    }

    function genereGrille()
    {

        switch ($_POST['difficulte']) {
            case "facile":
                $this->numLigne = "8";
                $this->numColonne = "8";
                $this->numMines = "10";
                break;
            case "difficile":
                $this->numLigne = "16";
                $this->numColonne = "16";
                $this->numMines = "40";
                break;
            case "personnalisée":
                $this->numLigne = $_POST['numLigne'];
                $this->numColonne = $_POST['numColonne'];
                $this->numMines = $_POST['numMines'];
                break;
        }
        // Generate grid)reference array
        for ($x = 10; $x < ($this->numLigne + 10); $x++) {
            for ($y = 10; $y < ($this->numColonne + 10); $y++) {
                array_push($this->grille, $x . $y);
            }
        }
    }

    function genereValeur()
    {

        $this->celluleMines = $this->grille;
        $key = array_search($this->caseCliquer, $this->celluleMines);
        unset($this->celluleMines[$key]);
        shuffle($this->celluleMines);
        $this->celluleMines = array_values(array_slice($this->celluleMines, 0, $this->numMines));


        foreach ($this->grille as $cell) {
            if (!in_array($cell, $this->celluleMines)) {
                $cells_to_check = array();
                $cells_to_check = $this->get_surrounding_cells($cell);
                $number = count(array_intersect($cells_to_check, $this->celluleMines));
                if ($number > 0) {
                    $this->celluleValeur[$cell] = $number;
                }
            }
        }

        $this->procesusCellule($this->caseCliquer);
    }

    function jouerJeu()
    {

        if ($this->celluleMarquer == true) {
            $this->procesusCellule($this->caseCliquer);
            $this->siJeuGagner();
            return;
        } else {
            if (!in_array($this->caseCliquer, $this->celluleMarquer)) {
                if (in_array($this->caseCliquer, $this->celluleMines)) {
                    $this->clickMine();
                    return;
                } elseif (isset($this->celluleValeur[$this->caseCliquer])) {
                    $this->clickNumero();
                    return;
                } else {
                    $this->clickVide();
                    return;
                }
            }
        }
    }

    function clickMine()
    {

        $this->jeuPerdu();
    }

    function clickNumero()
    {

        $this->procesusCellule($this->caseCliquer);
    }

    function clickVide()
    {


        $cells_to_check = $this->get_surrounding_cells($this->caseCliquer);
        $cells_checked = array();
        $this->procesusCellule($this->caseCliquer);
        $x = 1;
        while ($x > 0) {
            $x = 0;
            foreach ($cells_to_check as $cell) {
                $this->procesusCellule($cell);
                // If the cell is empty and it hasn't been checked we add it to checked cells
                // add the surrounding blank cells to the array to be checked. Increasing x so it will loop again.
                if ((!isset($this->cell_values[$cell])) && (!in_array($cell, $cells_checked))) {
                    array_push($cells_checked, $cell);
                    $cells_to_check = array_merge($cells_to_check, $this->get_surrounding_cells($cell));
                    array_diff($cells_to_check, array($cell));
                    $x++;
                }
            }
        }
    }

    function jeuPerdu()
    {

        $this->celluleVisible = $this->grille;
        $this->mode = "jeuPerdu";
    }

    function get_surrounding_cells($cell)
    {

        $cells_to_check = array();
        array_push($cells_to_check, substr($cell, 0, 2) - 1 . substr($cell, 2, 2) - 1);
        array_push($cells_to_check, substr($cell, 0, 2) - 1 . substr($cell, 2, 2));
        array_push($cells_to_check, substr($cell, 0, 2) - 1 . substr($cell, 2, 2) + 1);
        array_push($cells_to_check, substr($cell, 0, 2) . substr($cell, 2, 2) - 1);
        array_push($cells_to_check, substr($cell, 0, 2) . substr($cell, 2, 2) + 1);
        array_push($cells_to_check, substr($cell, 0, 2) + 1 . substr($cell, 2, 2) - 1);
        array_push($cells_to_check, substr($cell, 0, 2) + 1 . substr($cell, 2, 2));
        array_push($cells_to_check, substr($cell, 0, 2) + 1 . substr($cell, 2, 2) + 1);
        $cells_to_check = array_intersect($cells_to_check, $this->grille);
        $cells_to_check = array_diff($cells_to_check, $this->celluleMarquer);
        return $cells_to_check;
    }

    function procesusCellule($cell)
    {

        if (($cell == $this->caseCliquer) && ($this->marqueToggle == true) && (!in_array($this->caseCliquer, $this->celluleMarquer))) {
            array_push($this->marqueToggle, $this->caseCliquer);
            return;
        } elseif (($cell == $this->celluleMarquer) && ($this->marqueToggle == true) && (in_array($this->caseCliquer, $this->celluleMarquer))) {
            $key = array_search($this->celluleMarquer, $this->celluleMarquer);
            unset($this->celluleMarquer[$key]);
            return;
        }

        if (!in_array($cell, $this->celluleMarquer)) {
            array_push($this->celluleVisible, $cell);
            $this->cellullVisible = array_unique($this->celluleVisible);
            $this->siJeuGagner();
        }


    }

    function siJeuGagner()
    {

        if ((isset($_POST)) && ((count($this->grille) - count($this->celluleVisible)) == count($this->celluleMines))) {
            $this->mode = "jeuGagner";
        }
    }

}

$data = new data_handler();
?>