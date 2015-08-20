<?php

class Zend_View_Helper_Error extends Zend_View_Helper_Abstract {

    public function Error($message) {
        return "
        <div class='ui-state-error ui-corner-all' style='padding: 0 .7em; width:400px;'>
		<p><span class='fa fa-exclamation-triangle fa-fw' style='float: left; margin-right: .3em;'></span>
		{$message}</p>
                    </div><br>";
    }

}