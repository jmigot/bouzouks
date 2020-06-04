<?php
echo form_open('new_dest', $opt_form);
echo form_fieldset('Destination');
echo 'Choix de la destination : '.form_dropdown('destination',$opt_select_dest,'0');
echo form_fieldset_close();
echo form_button('gate_form','Valider','onclick="next_dest(this)"');
echo form_close();
?>
