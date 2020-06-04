<?php $this->layout->set_title('Choix du prochain maire'); ?>

<div id="elections-lister">
	<?php $this->load->view('elections/avancement') ?>

		<p class="centre a_vote">
			<?php if ($this->session->userdata('a_vote')): ?>
				Tu as voté pour <?= profil($this->session->userdata('vote_candidat_id'), $this->session->userdata('vote_candidat_pseudo')) ?><br>
			<?php elseif ($this->session->userdata('experience') < $this->bouzouk->config('elections_xp_voter')): ?>
				Tu n'as que <span class="pourpre"><?= $this->session->userdata('experience') ?> xp</span>, tu dois cumuler au moins <span class="pourpre">
				<?= $this->bouzouk->config('elections_xp_voter') ?> xp</span> avant de pouvoir voter.
			<?php endif; ?>
		</p> 
		<?php if ($tour == Bouzouk::Elections_Tour1): ?>
			<!-- Liste des candidats -->
			<?php foreach ($candidats as $candidat):
					// Modification de l'affichage du perso des candidats en fonction de l'gupnp_service_info_get_introspection 
			?>

			<div class="candidat">
				<!-- Slogan -->
				<?php
					// ---------- Hook clans ----------
					// Distinction électorale (Parti Politique)
					// -> on regarde si un candidat doit ressortir en bleu
				?>
				<div <?= (isset($distinction_electorale) && $distinction_electorale['joueur_id'] == $candidat->id) ? 'class="affiche_bleu"' : 'class="affiche"' ?>>
					<div class="nom_candidat">
						<?= profil($candidat->id, $candidat->pseudo) ?>
					</div>
					<p class="image_candidat">
						<img src="<?= img_url_avatar($candidat->faim, $candidat->sante, $candidat->stress, $candidat->perso, $candidat->utiliser_avatar_toboz, $candidat->id) ?>" alt="Bouzouk">
					</p>
					<div class="slogan">
						<p class="texte">
							<?= form_prep($candidat->slogan) ?>
						</p>
						<?php if ($candidat->topic_id > 0): ?>
						<p class="programme_electoral">
							<a href="<?= site_url('tobozon/viewtopic.php?id='.$candidat->topic_id) ?>">Programme complet<br>sur votre tobozon</a>
						</p>
						<?php endif; ?>		
					</div>
				</div>	
				

					<!-- Bouton voter/ Résultats -->
					<?php if ($this->session->userdata('a_vote') OR $this->session->userdata('experience') < $this->bouzouk->config('elections_xp_voter')): ?>
							<p class="vote_tour1"><?= pluriel($candidat->votes, 'vote') ?> (<?= $candidat->pourcentage_votes ?>%)</p>
					<?php else: ?>
						<?= form_open('elections/voter') ?>
							<p class="vote_tour1">
								<input type="hidden" class="bouton_rouge" name="joueur_id" value="<?= $candidat->id ?>">
								<input type="submit" class="bouton_rouge" value="Voter pour <?= $candidat->pseudo ?>">
							</p>
						</form>
				
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		<?php else: ?>
			<!-- Liste des candidats -->
			<?php foreach ($candidats as $candidat): ?>
			<div class="zone_candidat">
				<div class="candidat">
					<!-- Slogan -->
					<?php
						// ---------- Hook clans ----------
						// Distinction électorale (Parti Politique)
						// -> on regarde si un candidat doit ressortir en bleu
					?>
					<div <?= (isset($distinction_electorale) && $distinction_electorale['joueur_id'] == $candidat->id) ? 'class="affiche_bleu"' : 'class="affiche"' ?>>
						<div class="nom_candidat">
							<?= profil($candidat->id, $candidat->pseudo) ?>
						</div>
						<p class="image_candidat">
							<img src="<?= img_url_avatar($candidat->faim, $candidat->sante, $candidat->stress, $candidat->perso, $candidat->utiliser_avatar_toboz, $candidat->id) ?>" alt="Bouzouk">
						</p>
						<div class="slogan">
							<p>
								<?= form_prep($candidat->slogan) ?>
							</p>
							<?php if ($candidat->topic_id > 0): ?>
							<p class="programme_electoral">
								<a href="<?= site_url('tobozon/viewtopic.php?id='.$candidat->topic_id) ?>">Programme complet<br>sur votre toboz</a>
							</p>
							<?php endif; ?>		
						</div>
					</div>	
				</div>

				<div class="droite bloc_gris">
					<!-- Bouton voter/ Résultats -->
					<?php if ($this->session->userdata('a_vote') OR $this->session->userdata('experience') < $this->bouzouk->config('elections_xp_voter')): ?>
						<?php if ($tour != Bouzouk::Elections_Tour3): ?>
							<p class="vote_tour2 pourpre mini_bloc"><?= pluriel($candidat->votes, 'vote') ?> (<?= $candidat->pourcentage_votes ?>%)</p>
						<?php else: ?>
							<p class="vote_tour2 pourpre mini_bloc">
								<i>Les résultats sont cachés au 3ème tour</i>
								<?php if ($this->bouzouk->is_admin()): ?>
									<br><?= pluriel($candidat->votes, 'vote') ?> (<?= $candidat->pourcentage_votes ?>%)
								<?php endif; ?>
							</p>
						<?php endif; ?>
						<?php else: ?>
							<?= form_open('elections/voter') ?>
								<p class="vote_tour2 mini_bloc">
									<input type="hidden" name="joueur_id" value="<?= $candidat->id ?>">
									<input type="submit" value="Voter pour <?= $candidat->pseudo ?>">
								</p>
							</form>
						<?php endif; ?>
							
					<div <?= (isset($distinction_electorale) && $distinction_electorale['joueur_id'] == $candidat->id) ? ' class="texte_electorale_bleu"' : ' class="texte_electorale"' ?>>
						<?= nl2br(form_prep($candidat->texte)) ?>
					</div>

				</div>

			</div>
			<?php endforeach; ?>
		<?php endif; ?>
</div>