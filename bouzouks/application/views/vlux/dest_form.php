<?php
echo form_open('choix_dest', $opt_form);
echo form_fieldset('Destinations');
echo 'Choix de la destination : '.form_dropdown('destination',$opt_select_dest,'0');
echo form_fieldset_close();
echo form_button('gate_form','Valider','onclick="bt.teleport_request(this)"');
echo form_close();
?>
