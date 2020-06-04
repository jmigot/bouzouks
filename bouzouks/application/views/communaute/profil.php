<?php $this->layout->set_title('Profil bouzouk'); ?>

<div id="communaute-profil">

	<div class="feuille">
		<div class="tete">
		</div>
	<!-- Image perso -->
		<div class="perso">
			<div class="polaroid">
				<p class="image_perso">
					<?php if (in_array($joueur->statut, array(Bouzouk::Joueur_Etudiant,  Bouzouk::Joueur_ChoixPerso))): ?>
						<img src="<?= img_url('perso/corp/etudiant.png') ?>" alt="Perso">
					<?php else: ?>
						<img src="<?= img_url_avatar(100, 100, 0, $joueur->perso, $joueur->utiliser_avatar_toboz, $joueur->id) ?>" alt="Perso">
					<?php endif; ?>
				</p>
				<p class="nom">
					<?= $joueur->pseudo ?>
				</p>
			</div>
			<p class="trombone">
				<img src="<?= img_url('entreprise/trombone.png') ?>" alt="">
			</p>
		</div>
		<div class="social">
			<p class="separation">
				<?= strtotime($joueur->connecte) >= strtotime('-2 MINUTE') ? 'Joueur <span class="vert">Connecté' : 'Joueur <span class="rouge">Hors-ligne' ?></span><br>Dernière activité :<br>le <?= bouzouk_datetime($joueur->connecte, 'court') ?><br>
					<?php if ($joueur->id != $this->session->userdata('id')): ?>
						<!-- Envoyer une missive -->
						<?= form_open('missives/ecrire/'.$joueur->id, array('class' => 'inline-block')) ?>
							<p><input class="bouton_violet" type="submit" value="Lui écrire une missive"></p>
						</form>

						<!-- Ajout ami -->
						<?php if ( ! $this->bouzouk->sont_amis($this->session->userdata('id'), $joueur->id)): ?>
						<?= form_open('amis/ajouter', array('class' => 'inline-block')) ?>
							<p>
								<input class="bouton_violet" type="hidden" name ="ami_id" value="<?= $joueur->id ?>">
								<input class="bouton_violet" type="submit" value="Demander en ami">
							</p>
						</form>
					<?php endif; ?>	
				<?php endif; ?>
			</p>
			<p class="separation">
				<?= $joueur->statut_phrase ?>
				<?php if ($this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurProfils) && $joueur->raison_statut != ''): ?>
					<br><br>Motif : &laquo; <span class="pourpre"><?= $joueur->raison_statut ?></span> &raquo;
				<?php endif; ?>
			</p>
		</div>
		<div class="bloc">
		<!-- Infos joueur -->
			<h2 class="titre gras">Fiche profil de <?= $joueur->pseudo ?></h2>
			<h3 class="sous_titre"><?= $joueur->maire ?> <?= $joueur->rang ?> <?= $joueur->rang_description ?></h3>
			<table>
				<tr>
					<td>Adresse</td>
					<td class="pourpre"><?= form_prep($joueur->adresse) ?></td>
				</tr>
				<tr>
					<td>Expérience</td>
					<td class="pourpre"><?= $joueur->experience ?> xp</td>
				</tr>
				<tr>
					<td>Né depuis</td>
					<td class="pourpre"><?= $joueur->jours_inscription ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<p class="espace"></p>
					</td>
				</tr>
				<tr>
					<td>Mendiant</td>
					<td class="pourpre"><?= $joueur->mendiant ?></td>
				</tr>
				<tr>
					<td>Job</td>
					<td><?= $joueur->job ?><?= $joueur->entreprise ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<p class="espace"></p>
					</td>
				</tr>
				<tr>
					<td>Amis / Filleuls</td>
					<td class="pourpre"><?= pluriel($joueur->nb_amis, 'ami') ?> / <?= pluriel($joueur->nb_filleuls, 'filleul') ?></td>
				</tr>

				<tr>
					<td>Stats Plouk</td>
					<td class="pourpre">Gagnées : <?= $joueur->plouk_stats[0] ?> | Perdues : <?= $joueur->plouk_stats[1] ?> | Egalité : <?= $joueur->plouk_stats[2] ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<p class="espace"></p>
					</td>
				</tr>
				<tr>
					<td>Syndicat</td>
					<td class="pourpre"><?= $joueur->clans[Bouzouk::Clans_TypeSyndicat][0] ?></td>
				</tr>

				<tr>
					<td>Parti politique</td>
					<td class="pourpre"><?= $joueur->clans[Bouzouk::Clans_TypePartiPolitique][0] ?></td>
				</tr>

				<tr>
					<td>Organisation</td>
					<td class="pourpre"><?= $joueur->clans[Bouzouk::Clans_TypeOrganisation][0] ?></td>
				</tr>
			</table>
		</div>
		
	<?php if ($joueur->commentaire != ''): ?>
	<!-- Commentaire -->
		<div class="commentaire">
			<h3 class="gras">Commentaire de <?= $joueur->pseudo ?>:</h3>
			<p><?= $this->lib_parser->remplace_bbcode(nl2br(form_prep($joueur->commentaire))) ?></p>
		</div>
	<?php endif; ?>
</div>


	<?php if ($this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurMulticomptes)): ?>
		<!-- Connexion -->
	<div class="cellule_gris_type1">
		<h4>Analyse multicompte de <?= $joueur->pseudo ?></h4>
		<div class="bloc_gris">
			<?php if ($this->bouzouk->is_admin()): ?>
				<?= form_open('staff/connexion_bouzouk', array('class' => 'centre margin')) ?>
					<p class="mini_bloc">
						<input type="hidden" name="joueur_id" value="<?= $joueur->id ?>">
						<input type="hidden" name="joueur_id_pseudo" value="<?= $joueur->pseudo ?>">
						<input type="submit" value="Connexion sur ce compte">
					</p>
				</form>
			<?php endif; ?>

			<p class="centre margin">
				<a href="<?= site_url('tobozon/profile.php?id='.$joueur->id) ?>">Profil Toboz</a>
			</p>
			
			<p class="hr"></p>

			<!-- Analyse multicompte -->
			<table>
				<tr>
					<td class="gauche"><p class="highlight">IP inscription</p></td>
					<td class="pourpre"><?= $joueur->ip_inscription ?></td>
				</tr>

				<tr>
					<td class="gauche"><p class="highlight">Date d'inscription</p></td>
					<td class="pourpre"><?= bouzouk_datetime($joueur->date_inscription, 'court', false) ?></td>
				</tr>
				
				<tr>
					<td class="gauche"><p class="highlight">Date de naissance</p></td>
					<td class="pourpre"><?= bouzouk_date($joueur->date_de_naissance, false) ?></td>
				</tr>

				<tr>
					<td class="gauche"><p class="highlight">Mot de passe</p></td>
					<td class="pourpre"><?= $joueur->mot_de_passe ?></td>
				</tr>

				<tr>
					<td class="gauche"><p class="highlight">Email</p></td>
					<td class="pourpre"><?= $joueur->email ?></td>
				</tr>
			
				<?php if ($this->bouzouk->is_admin(Bouzouk::Rang_Admin)): ?>
					<tr>
						<td class="gauche"><p class="highlight">Dons au site</p></td>
						<td class="pourpre">Allopass : <?= round($joueur->dons->total_euros, 1) ?>€ | Paypal : <?= round($joueur->dons_paypal->total_euros, 1) ?>€</td>
					</tr>
				<?php endif; ?>
				
				<tr>
					<td class="gauche"><p class="highlight">Joueurs avec le même mot de passe</p></td>
					<td>
						<?php foreach ($joueur->joueurs_mot_de_passe as $j): ?>
							<?= profil($j->id, $j->pseudo, null, $j->statut) ?>&nbsp;
						<?php endforeach; ?>
					</td>
				</tr>

				<tr>
					<td class="gauche"><p class="highlight">Joueurs avec la même IP d'inscription</p></td>
					<td>
						<?php foreach ($joueur->joueurs_ip_inscription as $j): ?>
							<?= profil($j->id, $j->pseudo, null, $j->statut) ?>&nbsp;
						<?php endforeach; ?>
					</td>
				</tr>

				<tr>
					<td class="gauche"><p class="highlight">Joueurs connectés sur la même IP</p></td>
					<td>
						<?php foreach ($joueur->joueurs_ip_connexion as $j): ?>
							<?= profil($j->id, $j->pseudo, null, $j->statut) ?>&nbsp;
						<?php endforeach; ?>
					</td>
				</tr>

				<tr>
					<td class="gauche"><p class="highlight">Joueurs avec même date de naissance</p></td>
					<td>
						<?php foreach ($joueur->joueurs_date_de_naissance as $j): ?>
							<?= profil($j->id, $j->pseudo, null, $j->statut) ?>&nbsp;
						<?php endforeach; ?>
					</td>
				</tr>
				
				<tr>
					<td class="gauche"><p class="highlight">IPs de connexion (15 derniers jours)</p></td>
					<td class="pourpre">
						<?php foreach ($joueur->ips_connexions as $connexion): ?>
							<?= $connexion->ip ?>&nbsp;&nbsp;&nbsp;
						<?php endforeach; ?>
					</td>
				</tr>

				<tr>
					<td class="gauche"><p class="highlight">Liste des filleuls</p></td>
					<td class="pourpre">
						<?php foreach ($joueur->filleuls as $filleul): ?>
							<?= profil($filleul->id, $filleul->pseudo, null, $filleul->statut) ?><img src="<?= $filleul->filleul_valide ? img_url('valide.png') : img_url('invalide.png') ?>" alt="Validé ou pas encore ? :p" width="15px" class="inline-block">&nbsp;
						<?php endforeach; ?>
					</td>
				</tr>

				<?php if (isset($joueur->parrain)): ?>
					<tr>
						<td class="gauche"><p class="highlight">Parrain</p></td>
						<td class="pourpre">
							<?= profil($joueur->parrain->id, $joueur->parrain->pseudo, null, $joueur->parrain->statut) ?>
						</td>
					</tr>
				<?php endif; ?>
			</table>
		</div>
	</div>

		<!-- Ventes basses marché noir -->
	<div class="cellule_bleu_type2">
		<h4>Ventes basses marché noir de <?= $joueur->pseudo ?></h4>
		<div class="bloc_bleu">
			<table class="marche_noir">
				<tr>
					<th>Date</th>
					<th>Vendeur</th>
					<th>Acheteur</th>
					<th>Objet</th>
					<th>Qté</th>
					<th>Pér.</th>
					<th>Prix</th>
				</tr>

				<?php foreach ($joueur->ventes as $vente): ?>
					<tr>
						<td class="pourpre"><?= bouzouk_datetime($vente->date, 'court', false) ?></td>
						<td><p><?= profil($vente->vendeur_id, $vente->vendeur_pseudo, null, $vente->vendeur_statut) ?></p></td>
						<td><p><?= profil($vente->acheteur_id, $vente->acheteur_pseudo, null, $vente->acheteur_statut) ?></p></td>
						<td><?= $vente->nom ?></td>
						<td><?= $vente->quantite ?></td>
						<td><?= $vente->peremption ?></td>
						<td><?= struls($vente->prix) ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>

		<!-- Parties de Plouk -->
	<div class="cellule_gris_type1">
		<h4>Parties de plouk de <?= $joueur->pseudo ?></h4>
		<div class="bloc_gris">
			<table class="parties">
				<tr>
					<th>Date</th>
					<th>Gagnant</th>
					<th>Contre</th>
					<th>Objet (p.c,p.a)</th>
					<th>Durée</th>
					<th>Tours</th>
					<th>Mdp</th>
					<th>Ab.</th>
					<th>V.</th>
				</tr>

				<?php foreach ($joueur->parties as $partie): ?>

					<tr>
						<td><?= jour_mois_heure_minute($partie->date_debut) ?></td>
						<td><p><?= $partie->gagnant_id == $partie->createur_id ? profil($partie->createur_id, $partie->createur_pseudo) : profil($partie->adversaire_id, $partie->adversaire_pseudo) ?></p></td>
						<td><p><?= $partie->gagnant_id != $partie->createur_id ? profil($partie->createur_id, $partie->createur_pseudo) : profil($partie->adversaire_id, $partie->adversaire_pseudo) ?></p></td>
						<td class="pourpre"><?= isset($partie->objet) ? mb_substr($partie->objet, 0, 12).' ('.$partie->createur_peremption.','.$partie->adversaire_peremption.')' : 'Aucun' ?></td>
						<td class="pourpre"><p><?= strtotime($partie->date_fin) - strtotime($partie->date_debut) ?>s</p></td>
						<td><?= $partie->nb_tours ?></td>
						<td><?= $partie->mot_de_passe != '' ? form_prep($partie->mot_de_passe) : '-' ?></td>
						<td class="pourpre"><?= $partie->abandon ? '<p>oui</p>' : '-' ?></td>
						<td><?= $partie->version ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
	<?php endif; ?>
</div>
