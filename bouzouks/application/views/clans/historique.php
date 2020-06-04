<?php $this->layout->set_title('Historique clan'); ?>

<div id="clans-historique">
	<!-- Menu -->
	<?php $this->load->view('clans/menu', array('lien' => 3)) ?>
		
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Historique du clan des <?= $this->bouzouk->config('clans_historique_duree_retention') ?> derniers jours</h4>
		<div class="bloc_bleu padd_vertical">
			<p class="centre"><?= $pagination ?></p>
			
			<table>
				<tr>
					<th>Date</th>
					<th>Texte</th>
				</tr>

				<?php foreach ($historique as $ligne): ?>
					<tr>
						<td><p class="tab_espace"><?= bouzouk_datetime($ligne->date, 'court') ?></p></td>
						<td><?= $ligne->texte ?></td>
					</tr>
				<?php endforeach; ?>
			</table>

			<p class="centre"><?= $pagination ?></p>
		</div>
	</div>
</div>
 
