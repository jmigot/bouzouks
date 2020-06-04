<?php $this->layout->set_title('Liste des annonces'); ?>

<div id="anpe-lister">
	<!-- Menu -->
	<?php $this->load->view('anpe/menu', array('lien' => 1)) ?>
	
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Résultats de ta recherche</h4>
		<div class="bloc_bleu">
			<p class="centre margin"><?= $pagination ?></p>
			
			<table>
				<tr>
					<th>Entreprise</th>
					<th>Job proposé</th>
					<th>Salaire</th>
					<th>Prime d'incompétence</th>
					<th>Syndicats<br>autorisés</th>
					<th>Détails</th>
				</tr>

				<tr>
					<td colspan="6"><p class="hr"></p></td>
				</tr>

				<?php foreach ($annonces as $annonce): ?>
					<tr>
						<td class="pourpre"><?= form_prep($annonce->entreprise) ?></td>
						<td><p class="job"><?= $annonce->job ?></p></td>
						<td><p class="highlight"><?= struls($annonce->salaire) ?></p></td>
						<td><p class="highlight"><?= struls($annonce->prime_depart) ?></p></td>
						<td><img src="<?= img_url($annonce->syndicats_autorises ? 'valide.png' : 'echec.png') ?>" alt="Syndicat autorisé" width="16"></td>
						<td><a href="<?= site_url('anpe/voir/'.$annonce->id) ?>">Détails</a></td>
					</tr>
					
					<tr>
						<td colspan="6"><p class="hr"></p></td>
					</tr>
				<?php endforeach; ?>
			</table>

			<p class="centre margin"><?= $pagination ?></p>
		</div>
	</div>
</div>
