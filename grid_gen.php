<?php

class grilleGen
{
    // devlare variables
    public $table_html = "";
    public $pre_table = "";
    public $post_table = "";
    public $color = array(
        1 => "blue",
        2 => "green",
        3 => "red",
        4 => "purple",
        5 => "brown",
        6 => "pink",
        7 => "yellow",
        8 => "red");

    function form_content()
    {
        global $data;

        if (($data->mode == "jeu") || ($data->mode == "nouveau") || ($data->mode == "commencer")) {
            $this->pre_table .= "<form action='index.php' method='post' id='minesweeper'>\n";
        } else {
            $this->post_table .= "<a href='.'>Nouvelle partie</a><br>";
        }

        if ($data->mode == "nouveau") {
            $this->pre_table .= "<select name = 'difficulte'>\n";
            $this->pre_table .= " <option value='facile'>facile</option>\n";
            $this->pre_table .= " <option value='difficile'>difficile</option>\n";
            $this->pre_table .= " <option value='personnalisée'>personnalisée</option>\n";
            $this->pre_table .= "</select>\n";
            $this->pre_table .= "<br>Paramètres: <br>";
            $this->pre_table .= "Lignes: <select name = 'numLigne'>\n";
            $this->options_builder(20);
            $this->pre_table .= "</select>\n";
            $this->pre_table .= "Colonnes: <select name = 'numColonne'>\n";
            $this->options_builder(20);
            $this->pre_table .= "</select>\n";
            $this->pre_table .= "Mines: <select name = 'numMines'>\n";
            $this->options_builder(50);
            $this->pre_table .= "</select>\n";
            $this->pre_table .= "<br><input type='submit' name='mode' value='commencer'>";
        }

        if ($data->mode == "commencer" || $data->mode == "jeu") {
            $this->pre_table .= "<input type='hidden' name='grille' value='" . htmlspecialchars(serialize($data->grille)) . "'>\n";
            $this->pre_table .= "<input type='hidden' name='numColonne' value='" . $data->numColonne . "'>\n";
            $this->pre_table .= "<input type='hidden' name='numLigne' value='" . $data->numLigne . "'>\n";
            $this->pre_table .= "<input type='hidden' name='numMines' value='" . $data->numMines . "'>\n";
            $this->pre_table .= "Mines:" . ($data->numMines - count($data->celluleMarquer)) . "<br>\n";
        }


        if ($data->mode == "jeu") {
            $this->pre_table .= "<input type='hidden' name='mode' value='jeu'>";
            $this->pre_table .= "<input type='hidden' name='celluleValeur' value='" . htmlspecialchars(serialize($data->celluleValeur)) . "'>\n";
            $this->pre_table .= "<input type='hidden' name='celluleMines' value='" . htmlspecialchars(serialize($data->celluleMines)) . "'>\n";
            $this->pre_table .= "<input type='hidden' name='celluleVisible' value='" . htmlspecialchars(serialize($data->celluleVisible)) . "'>\n";
            $this->pre_table .= "<input type='hidden' name='celluleMarquer' value='" . htmlspecialchars(serialize($data->celluleMarquer)) . "'>\n";
            $this->pre_table .= "Mettre Drapeau <input type='checkbox' name='marqueToggle'";
            if ($data->marqueToggle == true) {
                $this->pre_table .= " checked='checked'";
            }
            $this->pre_table .= ">\n";
        }

        if ($data->mode == "commencer") {
            $this->pre_table .= "<input type='hidden' name='mode' value='jeu'>";
            $this->pre_table .= "Mettre Drapeau \n";
        }
        if ($data->mode != "jeuGagner") {
            $this->post_table .= "</form>\n";
        }
        // if the game is over or game has been won, display message
        if ($data->mode == "jeuPerdu") {
            $this->pre_table .= "Tu as perdu :/\n";
        }
        if ($data->mode == "jeuGagner") {
            $this->pre_table .= "Bravo, tu as gagné!\n";

        }
    }

    function options_builder($numero)
    {
        // function to build options up to number specified
        for ($x = 8; $x <= $numero; $x++) {
            $this->pre_table .= " <option value='$x'>$x</option>\n";
        }
    }

    function creeTableau()
    {
        // loop to build grid. Only extra element is to mark block as red if block is submitted
        global $data;
        $this->table_html .= "<table border='1'>\n";
        for ($x = 10; $x < ($data->numLigne + 10); $x++) {
            $this->table_html .= "<tr>\n";
            for ($y = 10; $y < ($data->numColonne + 10); $y++) {
                $block = $x . $y;
                if (($data->mode == "jeuPerdu") && ($data->caseCliquer == $block)) {
                    $extra = " bgcolor='red'";
                } else {
                    $extra = "";
                }
                $this->table_html .= "<td width='18px' height='18px' border='0' align='center'$extra>";
                $this->cell_content($block);
                $this->table_html .= "</td>\n";
            }
            $this->table_html .= "</tr>\n";
        }
        $this->table_html .= "</table>\n";
    }

    function cell_content($block)
    {
        // Case is when grid is just created
        // else if the cell is visible, display it's content, otherwise, create form button
        global $data;
        if ($data->mode == "commencer") {
            $this->table_html .= "<input type='hidden' name='mode' value='jeu'><input type='submit' name='caseCliquer' value='" . $block . "' style='height:18px; width=18px; text-indent:-9999px' />";
        } else {
            if (in_array($block, $data->celluleVisible)) {
                if (array_key_exists($block, $data->celluleValeur)) {
                    $this->couleurNumero($data->celluleValeur[$block]);
                } else {
                    if (in_array($block, $data->celluleMines)) {
                        $this->table_html .= "<strong>*</strong>";
                    } else {
                        $this->table_html .= "";
                    }
                }
            } else {
                $this->table_html .= "<input type='submit' name='caseCliquer' value='" . $block . "' style='height:15px; width=15px; text-indent:-9999px";
                if (in_array($block, $data->celluleMarquer)) {
                    $this->table_html .= "; background:red";
                }
                $this->table_html .= "'/>";
            }
        }
    }

    function couleurNumero($numero)
    {
        $this->table_html .= "<font style='color:" . $this->color[$numero] . "'>$numero</font>";
    }


    function genere()
    {
        global $data;
        $this->form_content();
        echo $this->pre_table;
        if ((!isset($data->mode)) || ($data->mode != "nouveau")) {
            $this->creeTableau();
            echo $this->table_html;
        }
        echo $this->post_table;
    }
}

$grilleGen = new grilleGen();
?>