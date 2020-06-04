<?php
$this->layout->set_title('Historique des amis');
$this->layout->ajouter_javascript('historique.js');
?>

<div id="historique-index">
	<!-- Menu -->
	<?php $this->load->view('historique/menu', array('lien' => 2)) ?>
	
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Historique des <?= $this->bouzouk->config('historique_joueur_duree_retention') ?> derniers jours</h4>
		<div class="bloc_bleu padd_vertical">
			<p class="centre"><?= $pagination ?></p>
			
			<table class="lignes">
				<tr>
					<td class="centre">Date</td>
					<td>Texte</td>
				</tr>
				<?php foreach ($historique as $ligne): ?>
					<tr>
						<td><p class="tab_espace"><?= bouzouk_datetime($ligne->date, 'court') ?></p></td>
						<td><?= profil($ligne->joueur_id, $ligne->pseudo, $ligne->rang).' '.$this->bouzouk->construire_historique($ligne) ?></td>
					</tr>
				<?php endforeach; ?>
			</table>

			<p class="centre"><?= $pagination ?></p>
		</div>
	</div>
</div>
 
