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

class Field {
	/**
	 * Constructeur.
	 */
	public function __construct($label, $field_name, &$value, $explanation="", $type="text") {
        $this->label = $label;
        $this->value =& $value;
        $this->field_name = $field_name;
        $this->type = $type;
        if($explanation) {
            $this->explanation = '<a href="#" data-toggle="tooltip" title="'.$explanation.'">?</a>';
        } else {
            $this->explanation = "&nbsp;";
        }
	}

    public function html() {
        return '
            <div class="form-group">
                <label for="'.$this->field_name.'">'.$this->label.'</label>
                    '.$this->explanation.'
                <div class="controls">
                    <input type="'.$this->type.'" class="form-control" name="'.$this->field_name.'" value="'.$this->value.'" >                    
                </div>
            </div>';
    }

    public function load() {
        global $_REQUEST;
        $this->value = $_REQUEST[$this->field_name];
    }
}
