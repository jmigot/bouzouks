<?php
	$this->layout->set_title($title);
?>
<div>
	<div class="celulle_bleu_type1 marge_haut">
		<h4>Modif du pixel : <?= ucfirst($pixel->nom) ?></h4>
		<div class="bloc_bleu">
			<?php
				echo form_open('staff/gerer_campagne_fb/modif_pixel/'.$pixel->id);
				echo "Nom de la campagne : ".form_input('nom', set_value('nom', $pixel->nom))."<br/>";
				echo "ID FaceBook du pixel : ".form_input('id_fb', set_value('id_fb', $pixel->id_fb))."<br/>";
				$opt_etat_pixel = array(0=> 'désactivé', 1=> 'activé');
				echo "Statut de la campagne : ".form_dropdown('etat', $opt_etat_pixel, $pixel->etat)."<br/>";
				echo form_submit('modif_pixel', 'Valider');
				echo form_close();
			?>
		</div>
	</div>
</div>