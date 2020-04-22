<?php
chdir('../');
//
$kod = '';
$kod .= file_get_contents('javascript/panel_klienta.jcs');

echo $kod;

unset($kod);

?> 
