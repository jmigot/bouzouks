<?php
$this->layout->set_title('Administration - Statistiques');
$this->layout->ajouter_javascript('libs/flotr2.min.js');
$this->layout->ajouter_javascript('staff/statistiques.js');
?>

<div id="staff-statistiques-inscriptions">
	<!-- Menu -->
	<?php $this->load->view('staff/statistiques/menu', array('lien' => 1)) ?>
	
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Statistiques inscriptions</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<div id="donnees" class="invisible"><?= $donnees_graphique ?></div>
			<div id="graphique">
			</div>
		</div>
	</div>
</div>
 
