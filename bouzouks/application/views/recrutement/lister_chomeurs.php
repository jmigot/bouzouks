<?php $this->layout->set_title('Liste des annonces'); ?>

<div id="recrutement-lister">
	<!-- Menu -->
	<?php $this->load->view('entreprises/menu', array('lien' => 3)) ?>
	
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Les annonces des chômeurs</h4>
		<div class="bloc_bleu liste">
			<table>
				<tr>
					<th>Joueur</th>
					<th>Job</th>
					<th>Salaire</th>
					<th>Prime</th>
					<th>Proposer</th>
				</tr>

				<tr>
					<td colspan="5"><p class="hr"></p></td>
				</tr>

				<?php foreach ($annonces as $annonce): ?>
					<?= form_open('recrutement/proposer') ?>
						<input type="hidden" name="joueur_id" value="<?= $annonce->chomeur_id ?>">
						
						<tr>
							<td><?= profil($annonce->chomeur_id, $annonce->chomeur_pseudo) ?></td>
							<td>
								<select name="job_id">
									<option value=""></option>
									<?php foreach ($jobs as $job): ?>
										<?php if ($annonce->chomeur_experience >= $job->experience): ?>
											<option value="<?= $job->id ?>"><?= $job->nom ?></option>
										<?php endif; ?>
									<?php endforeach; ?>
								</select>
							</td>
							<td><input type="text" name="salaire" size="3"> struls</td>
							<td><input type="text" name="prime_depart" size="3"> struls</td>
							<td><input type="submit" value="Proposer"></td>
						</tr>

						<tr>
							<td colspan="5" class="message"><p class="margin"><?= nl2br(form_prep($annonce->message)) ?></p></td>
						</tr>
					</form>
					
					<tr>
						<td colspan="5"><p class="hr"></p></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>

	<div class="marge">
		<p class="pourpre gras droite">En tant que patron tu peux proposer régulièrement tes offres et discuter<br> d'une offre avec un chômeur sur cette machine a café. Tout spam ou abus sera sanctionné.</p>
	</div>

	<!-- Le tchat de l'anpe -->
	<?php
		$vars = array(
			'titre'           => 'La machine à café',
			'url_rafraichir'  => 'webservices/rafraichir_tchat_chomeur',
			'url_poster'      => 'webservices/poster_tchat_chomeur',
			'nb_messages_max' => $this->bouzouk->config('maintenance_tchats_messages')
		);

		if ($this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats))
		{
			$vars['moderation']    = true;
			$vars['url_supprimer'] = 'webservices/supprimer_tchat_chomeurs';
		}

		$this->load->view('machine_a_cafe', $vars);
	?>
	
	<?php if ($this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats)): ?>
		<p class="modo_tchat">
			<input type="button" name="machine_a_cafe_supprimer" value="Supprimer messages">
		</p>
	<?php endif; ?>
</div>
