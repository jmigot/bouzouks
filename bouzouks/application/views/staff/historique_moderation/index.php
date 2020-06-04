<?php $this->layout->set_title('Administration - Historique'); ?>

<div id="staff-historique_moderation">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Historique de modération</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>
			
			<p class="centre"><?= $pagination ?></p>

			<table class="lignes">
				<tr>
					<th>Date</th>
					<th>Texte</th>
				</tr>

				<tr>
					<td colspan="2"><p class="hr"></p></td>
				</tr>

				<?php foreach ($historique as $ligne): ?>
					<tr>
						<td><p class="highlight"><?= bouzouk_datetime($ligne->date, 'court', false) ?></p></td>
						<td><?= $ligne->texte ?></td>
					</tr>

					<tr>
						<td colspan="2"><p class="hr"></p></td>
					</tr>
				<?php endforeach; ?>
			</table>

			<p class="centre"><?= $pagination ?></p>
		</div>
	</div>
</div>




