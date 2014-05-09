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
use Shotgunutc\Field;

class BoolField extends Field {

    public function html() {
        if($this->value) {
            $selected = "checked";
        } else {
            $selected = "";
        }
        return '
            <div class="form-group">
                <label for="'.$this->field_name.'">'.$this->label.'</label>
                    '.$this->explanation.'
                <div class="controls">
                    <input type="checkbox" id="'.$this->field_name.'" name="'.$this->field_name.'" value="1" '.$selected.' >          
                </div>
            </div>';
    }

    public function load() {
        global $_REQUEST;
        $this->value = isset($_REQUEST[$this->field_name]);
    }
}
