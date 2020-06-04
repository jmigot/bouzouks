<?php
echo form_open('new_gate', $opt_form);
echo form_fieldset('Destination(s)');
echo 'Choix de la destination : '.form_dropdown('destination',$opt_select_dest,'0');
echo form_fieldset_close();
echo form_button('gate_form','Valider','onclick="next_gate(this)"');
echo form_close();
?>
