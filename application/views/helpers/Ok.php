<?php

class Zend_View_Helper_Ok extends Zend_View_Helper_Abstract {

    public function Ok($message) {
        return "
        <div id='okMessage' class='ui-state-highlight ui-corner-all' style='padding: 0 .7em; width:400px;'>
		<p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: .3em;'></span>
		{$message}</p>
                    </div>
                <script language='javascript'>$(\"#okMessage\").fadeOut(5000)</script><br>";
                
    }

}