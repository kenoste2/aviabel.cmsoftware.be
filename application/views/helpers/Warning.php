<?php

class Zend_View_Helper_Warning extends Zend_View_Helper_Abstract {

    public function Warning($message) {
        return "
        <div class='ui-state-highlight ui-corner-all' style='padding: 0 .7em; width:400px;'>
		<p><li class='fa fa-exclamation-triangle fa-fw' style='float: left; margin-right: .3em;'></li>
		{$message}</p>
                    </div><br>";
    }

}