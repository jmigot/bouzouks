<?php
$this->layout->set_title('Asile');
?>

<div id="joueur-asile">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Tu es à l'asile</h4>
		<div class="bloc_bleu">
			<!-- Image -->
			<p class="fl-gauche"><img src="<?= img_url('joueur/asile_'.$this->session->userdata('sexe').'.png') ?>" alt=""></p>

			<!-- Raison -->
			<p class="margin">Bienvenue dans l'asile, <?= $this->session->userdata('pseudo') ?>.
				<?php if ($this->session->userdata('raison_statut') != ''): ?>
					<?php if ($profil_moderateur == ''): ?>
						<!-- Envoyé par un clan -->
						<?= $this->session->userdata('raison_statut') ?>
					<?php else: ?>
						Tu as été envoyé à l'asile par <?= $profil_moderateur ?> pour la raison suivante :<br><br>
						
						&laquo; <span class="pourpre"><?= $this->session->userdata('raison_statut') ?></span> &raquo;<br><br>

						Merci donc de relire la <a href="<?= site_url('site/charte') ?>">la charte du jeu</a> si tu veux continuer à jouer à Bouzouks ;)
					<?php endif; ?>
				<?php else: ?>
					Tu es arrivé ici car tu as laissé ton stress monter à <span class="pourpre">100%</span>. Pense donc à utiliser des objets pour faire baisser le stress à l'avenir ;)
				<?php endif; ?>
			</p>

			<!-- Bouton sortir ? -->
			<?php if ($this->session->userdata('statut') == Bouzouk::Joueur_Asile): ?>
				<?php if ($a_fait_son_temps): ?>
					<p>Tu as fait ton temps à l'asile, tu peux en sortir dès maintenant. Attention, si tu restes <span class="pourpre">
					<?= $this->bouzouk->config('maintenance_delai_asile_to_game_over') ?> jours</span> en tout à l'asile, tu passeras en Game Over.</p>

					<?= form_open('joueur/reprendre_asile') ?>
						<p><input type="submit" name="reprendre_asile" value="Sortir de l'asile"></p>
					</form>
				<?php else: ?>
					<p>Tu es chez nous depuis <span class="pourpre"><?= $date_incarceration ?></span>, tu dois attendre d'avoir fait <?= couleur(pluriel($this->session->userdata('duree_asile'), 'heure')) ?> avant de pouvoir en sortir.</p>
				<?php endif; ?>
			<?php endif; ?>
					
					<!-- Bouton recruter ? -->
			<?php if ($this->session->userdata('statut') == Bouzouk::Joueur_Asile && $recrute): ?>
				<p>La <span class="pourpre">Secte du Schnibble</span> te propose de te faire sortir de l'asile. Mais en contrepartie tu devras la rejoindre et vouer un culte au <span class="pourpre">Schnibble</span>.</p>

				<?= form_open('joueur/recrute') ?>
					<p><input type="submit" name="rejoindre_secte" value="Rejoindre la Secte du Schnibble"></p>
				</form>
			<?php endif; ?>
			<p class="clearfloat"></p>
		</div>
	</div>
		
	<!-- Le tchat de l'asile -->
	<?php
		$vars = array(
			'titre'           => 'La machine à pikouz',
			'url_rafraichir'  => 'webservices/rafraichir_tchat_asile',
			'url_poster'      => 'webservices/poster_tchat_asile',
			'nb_messages_max' => $this->bouzouk->config('maintenance_tchats_messages')
		);

		if ($this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats))
		{
			$vars['moderation']    = true;
			$vars['url_supprimer'] = 'webservices/supprimer_tchat_asile';
		}

		$this->load->view('machine_a_cafe', $vars);
	?>

	<?php if ($this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats)): ?>
		<p>
			<input type="button" name="machine_a_cafe_supprimer" value="Supprimer messages">
		</p>
	<?php endif; ?>

	<!-- Liste des aliénés -->
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Liste des aliénés</h4>
		<div class="bloc_bleu">
			<p class="margin italique pourpre">Il y a en tout <?= pluriel(count($alienes), 'aliéné') ?> dans l'asile...</p>

			<table class="liste_bouzouks">
				<tr>
					<?php $i = 0; ?>
					<?php foreach ($alienes as $aliene): ?>
						<td><?= profil($aliene->id, $aliene->pseudo, $aliene->rang) ?></td>
						<?php if (++$i % 4 == 0): ?>
							</tr>
							<tr>
						<?php endif; ?>
					<?php endforeach; ?>
				</tr>
			</table>
		</div>
	</div>
</div>
