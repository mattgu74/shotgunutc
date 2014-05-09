<?php
/*
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" (Revision 42):
 * <matthieu@guffroy.com> wrote this file. As long as you retain this notice you
 * can do whatever you want with this stuff. If we meet some day, and you think
 * this stuff is worth it, you can buy me a beer in return Matthieu Guffroy
 * ----------------------------------------------------------------------------
 */

 /*
 * ----------------------------------------------------------------------------
 * "LICENCE BEERWARE" (Révision 42):
 * <matthieu@guffroy.com> a créé ce fichier. Tant que vous conservez cet avertissement,
 * vous pouvez faire ce que vous voulez de ce truc. Si on se rencontre un jour et
 * que vous pensez que ce truc vaut le coup, vous pouvez me payer une bière en
 * retour. Matthieu Guffroy
 * ----------------------------------------------------------------------------
 */


namespace Shotgunutc;

/*
    This class represent a shotgun Description, all parameters needed for each event.
*/
class Form {
    public $title;   // Titre du formulaire 
    public $action;  // Url de soumission
    public $submit;  // Libelé du bouton de validation
    public $items = Array();   // Liste d'item du Formulaire

    public function renderHtml() {
        echo '<form role="form" action="'.$this->action.'" method="POST">';
        foreach($this->items as $itm) {
            echo $itm->html();
        }
        echo '<button type="submit" class="btn btn-primary">'.$this->submit.'</button></form>';
    }

    public function addItem($item) {
        $this->items[] = $item; 
    }

    public function load() {
        foreach($this->items as $itm) {
            echo $itm->load();
        }
    }
}