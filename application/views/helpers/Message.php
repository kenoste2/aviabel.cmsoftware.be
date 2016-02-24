<?php

class Zend_View_Helper_Message extends Zend_View_Helper_Abstract {

    public function Message($message) {
        return "
        <div id='Message' class='ui-state-highlight ui-corner-all' style='padding: 0 .7em;'>
		<p>{$message}</p>
        </div>
        <br>";
                
    }

}