<?php

include 'lib/lessc.inc.php';
$less = new lessc('css.less');
header("Content-Type:text/css");
echo $less->parse();
?>
