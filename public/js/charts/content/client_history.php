<?php
session_start();
?>
<chart>
<chart_type>line</chart_type>
   <chart_data>
      <row>
         <null/>
	<?php foreach ($_SESSION['date'] as $value) print "<string>{$value}</string>"; ?>
      </row>
      <row>
         <string>Aangemaakt</string>
	<?php foreach ($_SESSION['counter'] as $value) print "<number>{$value}</number>"; ?>
      </row>
   </chart_data>
</chart>

