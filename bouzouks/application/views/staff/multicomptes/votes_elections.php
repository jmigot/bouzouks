<?php $this->layout->set_title('Administration - Multicomptes'); ?>

<div id="staff-multicomptes-votes">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Multicomptes - Votes élections</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<p class="margin centre">Les votes par date décroissante</p>

			<table>
				<tr>
					<th>Date</th>
					<th>Joueur</th>
					<th>Candidat</th>
				</tr>

				<tr>
					<td colspan="3"><p class="hr"></p></td>
				</tr>
					
				<?php foreach ($votes as $vote): ?>
					<tr>
						<td class="pourpre"><?= bouzouk_datetime($vote->date, 'court', false) ?></td>
						<td><p class="highlight"><?= profil($vote->joueur_id, $vote->joueur_pseudo) ?></p></td>
						<td><p class="highlight"><?= profil($vote->candidat_id, $vote->candidat_pseudo) ?></p></td>
					</tr>

					<tr>
						<td colspan="3"><p class="hr"></p></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
</div>
 
