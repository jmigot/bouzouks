<?php
$this->layout->set_title('Plouk - Jouer');
$this->layout->ajouter_javascript('plouk.js?v=12');
?>

<div id="plouk-jouer">
	<!-- Menu -->
	<?php $this->load->view('plouk/menu', array('lien' => 2)) ?>

	<div id="plouk_actions">
		<div class="slideThree">
			<input type="checkbox" id="activer_son" checked><label for="activer_son">Effets sonores</label>
		</div>
		<div class="slideThree">
			<input type="checkbox" id="activer_commentaires" checked><label for="activer_commentaires">Commentaires</label>
		</div>
		<!-- Infos Objet-->
		<div class="pari_objet">
			<?php if ($partie->objet_nom != ''): ?>
				<p><span class="gras pourpre">Objet parié</span> : <?= $partie->objet_nom ?></p>
			<?php endif; ?>
		</div>			
		<div class="bouton_alerte">
				<?php if (in_array($this->session->userdata('id'), array($partie->createur_id, $partie->adversaire_id))): ?>
					<input type="button" value="Je suis prêt, moi" id="plouk_commencer" class="invisible">
					<input type="button" value="Déclarer forfait" id="plouk_abandonner" class="invisible">
					<input type="button" value="Adversaire absent" id="plouk_quitter" class="invisible">
				<?php endif; ?>
		</div>
		</div>
		<p id="waiter" class="invisible">
			<img src="<?= img_url('attente.gif') ?>" alt="Attente réseau"><br>
			Attente de connexion réseau
		</p>
		
		<div class="partie">
			<?php if (in_array($this->session->userdata('id'), array($partie->createur_id, $partie->adversaire_id))): ?>
				<div class="fond_joueur centre">
			<?php else: ?>
				<div class="fond_spectateur centre">
			<?php endif; ?>
			
				<p id="partie_id" class="invisible"><?= $partie->id ?></p>

				<!-- Persos -->
				<p id="perso_createur"><img src="<?= img_url('vide.gif') ?>" alt="Perso créateur"></p>
				<p id="perso_adversaire"><img src="<?= img_url('vide.gif') ?>" alt="Perso adversaire"></p>

				<!-- Défausse -->
				<p id="defausse"><img src="<?= img_url('vide.gif') ?>" alt="Carte 1"></p>

				<!-- Tribunes -->
				<p id="tribune_createur"><img src="<?= img_url('vide.gif') ?>" alt="Tribune joueur 1"></p>
				<p id="tribune_adversaire"><img src="<?= img_url('vide.gif') ?>" alt="Tribune joueur 2"></p>

				<!-- Songades -->
				<canvas id="sondages" width="200" height="135">
				</canvas>

				<p id="sondages_createur">-</p>
				<p id="sondages_adversaire">-</p>

				<!-- Messages flash -->
				<p id="plouk_flash"></p>

				<!-- Jouer/Défausser -->
				<p id="jouer"><img src="<?= img_url('./plouk/jouer.png') ?>" alt="Jouer"></p>
				<p id="defausser"><img src="<?= img_url('./plouk/defausser.png') ?>" alt="Défausser"></p>

				<!-- Pseudos -->
				<div class="pseudos">
					<p id="pseudo_createur"><?= $partie->createur_pseudo ?></p>
					<p id="pseudo_adversaire"><?= isset($partie->adversaire_pseudo) ? $partie->adversaire_pseudo : '' ?></p>
				</div>

				<!-- Chronos -->
				<div class="chronos">
					<p id="chrono_createur" class="case_gauche">00 : 00</p>
					<p id="chrono_adversaire" class="case_droite">00 : 00</p>
				</div>

				<!-- Infos -->
				<div class="infos">
					<p id="infos_tours"></p>
				</div>

				<!-- Médiatisations -->
				<div class="mediatisations">
					<p id="mediatisation_createur" class="media_parti_gauche">-</p>
					<p id="mediatisation_adversaire" class="media_parti_droite">-</p>

					<p id="ajout_media_createur" class="ajout_media_gauche invisible">+ <?= $partie->mediatisation ?></p>
					<p id="ajout_media_adversaire" class="ajout_media_droite invisible">+ <?= $partie->mediatisation ?></p>
				</div>
				
				<!-- Partisans -->
				<div class="partisans">
					<p id="partisans_createur" class="media_parti_gauche">-</p>
					<p id="partisans_adversaire" class="media_parti_droite">-</p>

					<p id="ajout_partisans_createur" class="ajout_partisans_gauche invisible">+ <?= $partie->partisans ?></p>
					<p id="ajout_partisans_adversaire" class="ajout_partisans_droite invisible">+ <?= $partie->partisans ?></p>
				</div>

				<!-- Charismes -->
				<div class="charismes">
					<p id="charisme_createur">-</p>
					<p id="charisme_adversaire">-</p>
				</div>

				<!-- Cartes -->
				<div class="cartes">
					<?php if (in_array($this->session->userdata('id'), array($partie->createur_id, $partie->adversaire_id))): ?>
						<p id="carte_1" class="carte"><img src="<?= img_url('vide.gif') ?>" alt="Carte 1" class=""></p>
						<p id="carte_2" class="carte"><img src="<?= img_url('vide.gif') ?>" alt="Carte 2" class=""></p>
						<p id="carte_3" class="carte"><img src="<?= img_url('vide.gif') ?>" alt="Carte 3" class=""></p>
						<p id="carte_4" class="carte"><img src="<?= img_url('vide.gif') ?>" alt="Carte 4" class=""></p>
						<p id="carte_5" class="carte"><img src="<?= img_url('vide.gif') ?>" alt="Carte 5" class=""></p>
						<p id="carte_6" class="carte"><img src="<?= img_url('vide.gif') ?>" alt="Carte 6" class=""></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
		

	<!-- Machine à café -->
	<div class="machine_a_cafe">
		<p class="invisible nb_messages_max"><?= $this->bouzouk->config('plouk_tchat_max_messages') ?></p>
	
		<!-- Messages -->
		<div class="div_messages">
			<p class="titre">La tribune des spectateurs</p>
			<div class="messages">
				<p class="0"></p>
			</div>
			<div class="tableau">
				<div class="connectes">
					<ul class="pseudos">
					</ul>
				</div>
			</div>

		<?php if ( ! $this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats) && $this->session->userdata('interdit_tchat') == 1): ?>
			<p class="rouge centre gras margin">Tu as été temporairement interdit de tchat par un modérateur ou un administrateur.</p>
		<?php elseif ( ! in_array($this->session->userdata('id'), array($partie->createur_id, $partie->adversaire_id)) && $partie->tchat && ! $this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats)): ?>
			<p class="pourpre centre gras margin">Les spectateurs ne sont pas autorisés à tchater sur cette partie.</p>
		<?php else: ?>
			<!-- Formulaire -->
			<div class="zone_saisi">
			<?= form_open('#', array('class' => 'formulaire')) ?>
				<p>
					<input type="text" name="message" id="message" maxlength="150" class="compte_caracteres">
					<span id="message_nb_caracteres_restants" class="format_2 centre transparent">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
					<input type="submit" value="Envoyer">
				</p>
			</div>
			</form>

			<!-- Smileys -->
			<div class="smileys">
				<?= $table_smileys ?>
			</div>
		</div>
		<div class="reparateur">
		</div>
		<?php endif; ?>
	</div>

	<audio id="son_notification">
		<source src="<?= son_url('plouk/notification.mp3') ?>" type="audio/mpeg">
		<source src="<?= son_url('plouk/notification.ogg') ?>" type="audio/ogg">
		Ton navigateur ne supporte pas les formats audios.
	</audio>

	<audio id="son_notification_2">
		<source src="<?= son_url('plouk/notification_2.mp3') ?>" type="audio/mpeg">
		<source src="<?= son_url('plouk/notification_2.ogg') ?>" type="audio/ogg">
		Ton navigateur ne supporte pas les formats audios.
	</audio>

	<audio id="son_flash">
		<source src="<?= son_url('plouk/flash.mp3') ?>" type="audio/mpeg">
		<source src="<?= son_url('plouk/flash.ogg') ?>" type="audio/ogg">
		Ton navigateur ne supporte pas les formats audios.
	</audio>
	
	<audio id="son_jouer">
		<source src="<?= son_url('plouk/jouer.mp3') ?>" type="audio/mpeg">
		<source src="<?= son_url('plouk/jouer.ogg') ?>" type="audio/ogg">
		Ton navigateur ne supporte pas les formats audios.
	</audio>

	<audio id="son_defausse">
		<source src="<?= son_url('plouk/defausse.mp3') ?>" type="audio/mpeg">
		<source src="<?= son_url('plouk/defausse.ogg') ?>" type="audio/ogg">
		Ton navigateur ne supporte pas les formats audios.
	</audio>
	
	<audio id="son_gagne">
		<source src="<?= son_url('plouk/gagne.mp3') ?>" type="audio/mpeg">
		<source src="<?= son_url('plouk/gagne.ogg') ?>" type="audio/ogg">
		Ton navigateur ne supporte pas les formats audios.
	</audio>

	<audio id="son_perdu">
		<source src="<?= son_url('plouk/perdu.mp3') ?>" type="audio/mpeg">
		<source src="<?= son_url('plouk/perdu.ogg') ?>" type="audio/ogg">
		Ton navigateur ne supporte pas les formats audios.
	</audio>

	<audio id="son_charisme_up">
		<source src="<?= son_url('plouk/charisme_up.mp3') ?>" type="audio/mpeg">
		<source src="<?= son_url('plouk/charisme_up.ogg') ?>" type="audio/ogg">
		Ton navigateur ne supporte pas les formats audios.
	</audio>

	<audio id="son_charisme_down">
		<source src="<?= son_url('plouk/charisme_down.mp3') ?>" type="audio/mpeg">
		<source src="<?= son_url('plouk/charisme_down.ogg') ?>" type="audio/ogg">
		Ton navigateur ne supporte pas les formats audios.
	</audio>

	<audio id="son_inversion">
		<source src="<?= son_url('plouk/inversion.mp3') ?>" type="audio/mpeg">
		<source src="<?= son_url('plouk/inversion.ogg') ?>" type="audio/ogg">
		Ton navigateur ne supporte pas les formats audios.
	</audio>
</div>