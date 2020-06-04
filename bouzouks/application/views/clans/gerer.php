<?php
$this->layout->set_title('Gérer mon clan');
$this->layout->ajouter_javascript('clans.js');
?>

<div id="clans-gerer">
	<!-- Menu -->
	<?php $this->load->view('clans/menu', array('lien' => 1)) ?>

	<?php 
		// ---------- Hook clans ----------
		// Tag MLBiste (MLB)
		if (($tag_mlbiste = $this->bouzouk->clans_tag_mlbiste($clan->id)) != null)
			$this->load->view('clans/tag_mlb', array('tag_mlbiste' => $tag_mlbiste));
	?>

	<?php if (isset($alliances) && ($this->session->userdata('clan_grade')[$clan->type] >= $clan->grade_lancer_actions)): ?>
		<?php foreach ($alliances as $alliance): ?>
			<!-- Proposition d'alliance sur une action -->
			<div class="cellule_bleu_type1 marge_bas">
				<h4>Proposition d'alliance</h4>
				<div class="bloc_bleu centre">
					<p class="fl-gauche margin">
						<img src="<?= img_url('clans/actions/'.$alliance->image) ?>" alt="<?= $alliance->nom_action ?>">
					</p>

					<p class="margin">
						<?= couleur(form_prep($alliance->nom_clan)) ?> te propose une alliance sur l'action <?= couleur($alliance->nom_action) ?>.<br>
					</p>

					<?= form_open('clans/valider_alliance/'.$types[$clan->type]) ?>
						<p>
							<input type="hidden" name="alliance_id" value="<?= $alliance->id ?>">
							<input type="radio" name="decision" value="accepter" id="accepter"><label for="accepter">Accepter</label>
							<input type="radio" name="decision" value="refuser" id="refuser"><label for="refuser">Refuser</label><br>
							<input type="submit" value="Valider">
						</p>
					</form>
				</div>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>

	<!-- Titre -->
	<div class="infos cellule_bleu_type1">
		<h4><?= ($clan->type == Bouzouk::Clans_TypeSyndicat) ? 'Syndicat : ' : ($clan->type == Bouzouk::Clans_TypePartiPolitique ? 'Parti politique : ' : 'Organisation : ') ?>
				<?= form_prep($clan->nom) ?></h4>
		<div class="bloc_bleu padd_vertical">
			<p class="mini_bloc">
				<?= $modes_recrutement[$clan->mode_recrutement] ?>
			</p>
			<p class="mini_bloc heure_serveur">Il est <?= date('H:i') ?></p>
			<p class="margin-mini"><?= nl2br(form_prep($clan->description)) ?></p>

		<!-- Infos du clan -->
		<table class="fl-gauche">
			<tr class="frameborder_bleu">
				<td><p>Créé depuis le</p></td>
				<td><p class="rond_blanc"><?= bouzouk_date($clan->date_creation) ?></p></td>
			</tr>

			<tr>
				<td><p>Points d'action</p></td>
				<td><p class="rond_blanc"><?= pluriel($clan->points_action, 'point') ?></p></td>
			</tr>

			<?php 
			$points_action_disponibles = $this->lib_clans->points_action_disponibles($clan->id, true);
			
			if ($clan->points_action > $points_action_disponibles): ?>
				<tr>
					<td><p>Points misés</p></td>
					<td class="pourpre"><p class="rond_blanc"><?= $clan->points_action - $points_action_disponibles ?> points</p></td>
				</tr>
			<?php endif; ?>

			<tr class="frameborder_bleu">
				<td><p>Membres (actifs)</p></td>
				<td><p class="rond_blanc"><?= pluriel(count($membres), 'membre')." (".$nb_membres_actifs.")" ?></p></td>
			</tr>

			<tr>
				<td><p><?= $clan->nom_chef != '' ? $clan->nom_chef : 'Chef'?></p></td>
				<td><p class="rond_blanc"><?= profil($clan->chef_id, $clan->chef_pseudo, $clan->chef_rang) ?></p></td>
			</tr>

			<tr class="frameborder_bleu">
				<td><p>Donation chef</p></td>
				<td><p class="rond_blanc"><?= pluriel($clan->donation_chef, 'point') ?></p></td>
			</tr>
		</table>

		<!-- Enchère en cours -->
		<div class="enchere frameborder_bleu">
			<p>
				<?php if (isset($enchere)): ?>
					<?php $heures_annulation = $this->lib_clans->heures_annulation($enchere->date); ?>
					<!-- Une enchère est en cours -->
					Une action est actuellement en cours depuis <span class="pourpre"><?= date('H:i', strtotime($enchere->date)) ?></span><br>
					L'action a nécessité <span class="pourpre"><?= $enchere->montant_enchere ?> points</span> d'action<br>

					<?php if ($this->session->userdata('clan_grade')[$clan->type] >= $clan->grade_lancer_actions): ?>
						<?php if ($enchere->clan_id == $clan->id && date('H:i') >= $heures_annulation[0] && date('H:i') <= $heures_annulation[1]): ?>
							Tu peux annuler l'action de <span class="pourpre"><?= $heures_annulation[0] ?></span> à <span class="pourpre"><?= $heures_annulation[1] ?></span>
						<?php elseif ( ! $this->lib_clans->fin_des_encheres($clan->type)): ?>
							<?php if ($enchere->clan_id == $clan->id): ?>
								Tu pourras annuler à partir de <span class="pourpre"><?= $heures_annulation[0] ?></span>
							<?php else: ?>
								Tu as jusqu'à <span class="pourpre"><?= $heures_annulation[0] ?></span> pour enchérir
							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>
				<?php else: ?>
					<!-- Aucune enchère en cours -->
					<?php if ($this->lib_clans->fin_des_encheres($clan->type)): ?>
					Il y a eu aucune enchère pour aujourd'hui<br>
					Tu pourras en lancer une demain
					<?php else: ?>
					Il n'y a encore aucune enchère pour aujourd'hui<br>
					Tu peux lancer une action jusqu'à <span class="pourpre">20h00</span>
					<?php endif; ?>
				<?php endif; ?>
			</p>

			<?php if (isset($enchere) && $this->session->userdata('clan_grade')[$clan->type] >= $clan->grade_lancer_actions): ?>
				<!-- Vue du chef -->
				<?php if ($enchere->clan_id == $clan->id): ?>
					<!-- L'enchère max est celle du clan -->
					<?php if ($enchere->annulee): ?>
						<p class="gras">Tu as annulé l'enchère</p>
					<?php else: ?>
						<span class="pourpre italique gras"><?= $enchere->nom ?> <?= $this->lib_clans->parametres(unserialize($enchere->parametres)) ?></span><br>

						<?php if ($this->lib_clans->fin_des_encheres($clan->type) && date('H:i') > $heures_annulation[1]): ?>
							<span class="pourpre gras">Le clan a remporté les enchères</span><br>
						<?php else: ?>
							<span class="pourpre gras">Le clan est le meilleur enchérisseur</span><br>
						<?php endif; ?>

						<!-- Annuler l'action -->
						<?php if (date('H:i') >= $heures_annulation[0] && date('H:i') <= $heures_annulation[1]): ?>
							<?= form_open('clans/annuler_action/'.$types[$clan->type]) ?>
								<p>
									<input type="submit" value="Annuler l'action">
								</p>
							</form>
						<?php endif; ?>
					<?php endif; ?>
				<?php else: ?>
					<!-- L'enchère max est celle d'un autre clan -->
					<?php if ($enchere->annulee): ?>
						<p class="pourpre gras">L'enchère de l'autre clan a été annulée</p>
					<?php elseif ($this->lib_clans->fin_des_encheres($clan->type)): ?>
						<p class="pourpre gras">Un autre clan a remporté les enchères</p>
					<?php elseif (isset($enchere_clan)): ?>
						<!-- Surenchérir -->
						<?= form_open('clans/surencherir_action/'.$types[$clan->type]) ?>
							<p>
								<span class="pourpre italique gras"><?= $enchere_clan->nom ?> <?= $this->lib_clans->parametres(unserialize($enchere_clan->parametres)) ?></span><br>
								<input type="text" name="cout" value="<?= $cout_surenchere ?>" size="5" maxlength="7" class="centre">
								<input type="submit" value="Surenchérir"<?= $this->lib_clans->action_possible($action_surenchere, $clan, $nb_membres, $enchere) === true ? '' : ' disabled' ?>>
							</p>
						</form>
					<?php endif; ?>
				<?php endif; ?>
			<?php else: ?>
				<!-- Vue des autres membres -->
				<?php if (isset($enchere) && $enchere->clan_id == $clan->id): ?>
					<!-- L'enchère max est celle du clan -->				
					<span class="pourpre italique gras"><?= $enchere->nom ?> <?= $this->lib_clans->parametres(unserialize($enchere->parametres), $this->session->userdata('clan_grade')[$clan->type] >= Bouzouk::Clans_GradeSousChef) ?></span><br>
					
					<?php if ($enchere->annulee): ?>
						<p class="gras">Le clan a annulé son enchère</p>
					<?php elseif ($this->lib_clans->fin_des_encheres($clan->type) && date('H:i') > $heures_annulation[1]): ?>
						<p class="gras">Le clan a remporté les enchères</p>
					<?php else: ?>
						<p class="gras">Le clan est le meilleur enchérisseur</p>
					<?php endif; ?>
				<?php elseif (isset($enchere)): ?>
					<!-- L'enchère max est celle d'un autre clan -->
					<?php if ($enchere->annulee): ?>
						<p class="gras">L'enchère de l'autre clan a été annulée</p>
					<?php elseif ($this->lib_clans->fin_des_encheres($clan->type) && isset($enchere_clan)): ?>
						<p class="gras">Un autre clan a remporté les enchères</p>
					<?php elseif (isset($enchere_clan)): ?>
						<p class="gras">Un autre clan a une meilleure enchère</p>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<p class="clearfloat"></p>
	</div>
	</div>

	<!-- Derniers sujets tobozon -->
	<div class="cellule_gris_type2 news_tobo">
		<h4>News du clan sur le Tobozon</h4>
		<div class="bloc_gris">
			<?php if (isset($forum)): ?>
			<table>
				<?php foreach ($tobozon as $post): ?>
			<tr>
				<td><?= jour_mois_heure_minute($post->date) ?> - <?= profil($post->joueur_id, $post->pseudo) ?> - <a href="<?= site_url('tobozon/viewtopic.php?pid='.$post->id.'#p'.$post->id) ?>"><?= form_prep($post->sujet) ?></a></td>
			</tr>
					<?php endforeach; ?>
			</table>
			<?php else: ?>
				<p class="pourpre centre margin">Le chef de clan n'a pas encore créé de forum dédié au clan sur le Tobozon</p>
			<?php endif; ?>
		</div>
	</div>
	
	<!-- Machine à café -->
	<?php
		$vars = array(
			'url_rafraichir'  => 'webservices/rafraichir_tchat_clan/'.$clan->id,
			'url_poster'      => 'webservices/poster_tchat_clan/'.$clan->id,
			'nb_messages_max' => $this->bouzouk->config('maintenance_tchats_messages_clan'),
			'table_smileys'   => creer_table_smileys('message')
		);
		$this->load->view('machine_a_cafe', $vars);
	?>

	<!-- Liste des actions réalisables -->
	<div class="cellule_bleu_type1">
		<h4>Actions réalisables</h4>
		<div class="bloc_gris actions centre">
			<div class="mini_bloc">
				<?php if ($clan->grade_lancer_actions == 4): ?>
				<p>Le <span class="pourpre">chef</span> peut gérer les actions.</p>
				<?php elseif ($clan->grade_lancer_actions == 3): ?>
				<p>Le <span class="pourpre">chef</span> & <span class="pourpre">les sous-chefs</span> peuvent gérer les actions.</p>
				<?php elseif ($clan->grade_lancer_actions == 2): ?>
				<p>Le <span class="pourpre">chef</span>, <span class="pourpre">les sous-chefs</span> & les <span class="pourpre">membres</span> peuvent gérer les actions.</p>
				<?php endif; ?>
			</div>
		<!-- Liste en images -->
		<?php foreach ($actions as $action): ?>
			<?php 
				$cout_action = ($action->effet == Bouzouk::Clans_EffetDiffere && $cout_surenchere > $action->cout) ? $cout_surenchere : $action->cout;
				$action_possible = $this->lib_clans->action_possible($action, $clan, $nb_membres_actifs, $enchere);
			?>

			<div class="action fl-gauche" id="action_image_<?= $action->id ?>" style="background: url(<?= img_url('clans/actions/'.$action->image) ?>); background-size: 100% 100%;">
				<div class="infos<?= $action_possible === true ? ' possible' : ' impossible' ?>">
					<p class="nom"><?= form_prep($action->nom) ?></p>
					<p class="cout"><?= $cout_action ?> p.a. <?= $action->effet == Bouzouk::Clans_EffetDiffere ? 'mini' : '' ?></p>
				</div>
			</div>
		<?php endforeach; ?>

		<p class="clearfloat"></p>

		<!-- Détails cachés -->
		<?php foreach ($actions as $action): ?>
			<?php
				$cout_action = ($action->effet == Bouzouk::Clans_EffetDiffere && $cout_surenchere > $action->cout) ? $cout_surenchere : $action->cout;
				$action_possible = $this->lib_clans->action_possible($action, $clan, $nb_membres_actifs, $enchere);
			?>

			<div class="details invisible" id="action_details_<?= $action->id ?>">
					<p class="description">
						<b><?= form_prep($action->nom) ?></b> : <?= $action->description ?>
						
						<?php if ($action->nb_membres_min > 0): ?>
							<!-- Condition sur le nombre de membres -->
							<br>Condition : <i>avoir au moins <?= pluriel($action->nb_membres_min, 'membre actif', 'membres actifs') ?> dans le clan</i>
						<?php endif; ?>

						<?php if ($action->nb_allies_min > 0): ?>
							<!-- Condition sur le nombre d'alliés -->
							<br>Condition : <i>avoir au moins <?= pluriel($action->nb_allies_min, 'allié') ?> acceptant l'action avec chacun au moins <?= $action->nb_membres_allies_min ?> membres actifs</i>
						<?php endif; ?>

						<br><br>
						Coût de base : <span class="pourpre"><?= $action->cout ?> points</span>

						<?php if ($action->cout_par_allie > 0): ?>
							<br>
							Coût par alliée : <span class="pourpre"><?= $action->cout_par_allie ?> points</span>
						<?php endif; ?>
						
						<?php if ($action->effet == Bouzouk::Clans_EffetDiffere && $cout_surenchere > 0): ?>
							<br>
							Coût de la surenchère : <span class="pourpre"><?= $cout_action ?> points</span>
						<?php endif; ?>
					</p>
					<p class="effet inline-block padding">Action<br><?= $action->effet == Bouzouk::Clans_EffetDirect ? 'directe' : 'différée' ?></p>
					<?php if ($this->session->userdata('clan_grade')[$clan->type] >= $clan->grade_lancer_actions): ?>
						<!-- Le chef de clan peut faire des choix et lancer des actions -->
						<div class="frameborder_bleu lancement">
							<p class="gauche">Lancement de l'action</p>

							<?php if ($action_possible === true || (in_array($action->id, array(5, 6, 7, 14)) && ! $this->lib_clans->fin_des_encheres($clan->type))): /* Les actions demandant des alliés doivent quand même afficher les options pour ajouter des alliés */ ?>
								<?= form_open('clans/lancer_action/'.$types[$clan->type]) ?>
									<input type="hidden" name="action_id" value="<?= $action->id ?>">
									<input type="hidden" name="clan_type" value="<?= $clan->type ?>">
									<?= $this->lib_clans->html_action($action->id, $clan->id) ?>

									<?php if ($action->effet == Bouzouk::Clans_EffetDiffere): ?>
										Coût enchère : <input type="text" name="cout" value="<?= $cout_action ?>" size="6" maxlength="7" class="centre pourpre"> points d'action<br>
									<?php else: ?>
										<input type="hidden" name="cout" value="<?= $cout_action ?>">
									<?php endif; ?>

									<?php if ($action_possible === true): ?>
										<input type="submit" value="<?= $action->effet == Bouzouk::Clans_EffetDirect ? 'Lancer cette action' : 'Enchérir pour cette action' ?>" class="margin">
									<?php else: ?>
										<p class="rouge_fonce margin"><?= $action_possible ?></p>
									<?php endif; ?>
								</form>
							<?php else: ?>
							<p class="rouge_fonce margin"><?= $action_possible ?></p>
							<?php endif; ?>
						</div>
					<?php elseif ($action_possible !== true): ?>
					<p class="condition rouge_fonce margin centre"><span><?= $action_possible ?></span></p>
					<?php else: ?>
					<p class="condition"><span>Tu n'es pas assez gradé pour lancer une action</span></p>
					<?php endif; ?>
				</div>
		<?php endforeach; ?>		
		</div>		
	</div>

	<!-- Liste des membres -->
	<div class="cellule_gris_type2">
		<h4>Liste des membres</h4>
		<div class="bloc_gris membres">
			<p class="mini_bloc">
				Il y a <?= pluriel($nb_membres, 'membre') ?> dans le clan.
			</p>
			<?= form_open('clans/modifier_membres/'.$types[$clan->type]) ?>
				<table>
					<tr>
						<th>Pseudo</th>
						<th>Grade</th>
						<th>Ancienneté</th>
						<th>Donation</th>

						<?php if ($this->session->userdata('clan_grade')[$clan->type] >= Bouzouk::Clans_GradeSousChef): ?>
							<th>Invisible</th>
							<th>Virer</th>
						<?php endif; ?>
					</tr>

					<?php foreach ($membres as $membre): ?>
						<?php
							if ($membre->invisible && $membre->id != $this->session->userdata('id') && $this->session->userdata('clan_grade')[$clan->type] < Bouzouk::Clans_GradeSousChef)
								continue;
						?>
						
						<tr>
							<td colspan="<?= $this->session->userdata('clan_grade')[$clan->type] >= Bouzouk::Clans_GradeSousChef ? '6' : '4' ?>"><p class="espace"></p></td>
						</tr>

						<tr>
							<td><p class="tab_esp"><?= profil($membre->id, $membre->pseudo, $membre->rang) ?></p></td>
							<td>
								<input type="hidden" name="ids[]" value="<?= $membre->id ?>">

								<?php if ($membre->grade < $this->session->userdata('clan_grade')[$clan->type] && $this->session->userdata('clan_grade')[$clan->type] >= Bouzouk::Clans_GradeSousChef): ?>
									<select name="grades[<?= $membre->id ?>]">
										<?php foreach ($grades as $grade => $nom): ?>
											<?php if ($grade < $this->session->userdata('clan_grade')[$clan->type]): ?>
												<option value="<?= $grade ?>"<?= $membre->grade == $grade ? ' selected' : '' ?>><?= $nom ?></option>
											<?php endif; ?>
										<?php endforeach; ?>
									</select>
								<?php else: ?>
									<!-- Les membres invisibles sont sautés avant -->
									<?= $grades[$membre->grade] ?><?= $membre->invisible ? ' <span class="pourpre">[invis.]</span>' : '' ?>
								<?php endif; ?>
							</td>
							<td><p><?= pluriel($membre->anciennete, 'jour') ?></p></td>
							<td class="pourpre"><p><?= pluriel($membre->donation, 'point') ?></p></td>

							<?php if ($this->session->userdata('clan_grade')[$clan->type] >= Bouzouk::Clans_GradeSousChef): ?>
								<?php if ($membre->grade < $this->session->userdata('clan_grade')[$clan->type]): ?>
									<td><input type="checkbox" name="invisibles[<?= $membre->id ?>]"<?= $membre->invisible ? ' checked' : '' ?>></td>
									<td><input type="checkbox" name="virer[<?= $membre->id ?>]"></td>
								<?php else: ?>
									<td><input type="checkbox" disabled<?= $membre->invisible ? ' checked' : '' ?>></td>
									<td><input type="checkbox" disabled></td>
								<?php endif; ?>
							<?php endif; ?>
						</tr>
					<?php endforeach; ?>
				</table>

				<?php if ($this->session->userdata('clan_grade')[$clan->type] >= Bouzouk::Clans_GradeSousChef): ?>
					<p class="droite"><input type="submit" value="Modifier"></p>
				<?php endif; ?>
			</form>
			<p class="clearfloat"></p>
			<p class="margin centre pourpre">
				Les chefs et les sous-chefs peuvent voir, modifier ou virer chaque membre, y compris les invisibles.
			</p>
		</div>
	</div>

	<!-- Faire une donation de points d'action -->
	<div class="donation">
		<h4>Faire un transfert <?= $statistiques[$clan->type][1] ?> au clan</h4>
		<div class="texte bloc_bleu">
			<p class="mini_bloc">
				Tu as actuellement <span class="pourpre"><?= pluriel($this->session->userdata($statistiques[$clan->type][0]), 'point').' '.$statistiques[$clan->type][1] ?></span>.
			</p>
			<p>
				Tu peux aider ton clan à réaliser des actions sur le site en lui donnant une partie ou la totalité de <?= $statistiques[$clan->type][2] ?> grâce à cet extracteur.<br>
				Si tu donnes tes points tu ne pourras plus les récupérer.
			</p>
		</div>

		<?= form_open('clans/donation/'.$types[$clan->type]) ?>
			<p class="pourpre gras">
				Choisis le nombre de points <?= $statistiques[$clan->type][1] ?> que tu veux donner :
				<input type="text" name="points" maxlength="5" size="6">
				<input type="submit" class="bouton_rouge" value="Donner">
			</p>
		</form>
	</div>

	<!-- Liste des actions lancées -->
	<div class="cellule_gris_type2">
		<h4>Liste des 10 dernières actions lancées par le clan</h4>
		<div class="bloc_gris actions_lancees padd_vertical">
			<table>
				<tr>
					<th>Date début</th>
					<th></th>
					<th>Action</th>
					<th>Coût action</th>
					<th>Effet</th>
					<th>En cours</th>
				</tr>

				<?php foreach ($actions_lancees as $action): ?>

					<tr>
						<td><?= bouzouk_datetime($action->date_debut, 'court') ?></td>
						<td><img src="<?= img_url('clans/actions/'.$action->image) ?>" alt="Image" width="45"></td>
						<td class="pourpre"><?= $action->nom ?> <?= $this->lib_clans->parametres(unserialize($action->parametres)) ?></td>
						<td><?= $action->cout ?> p.a</td>
						<td><?= $action->effet == Bouzouk::Clans_EffetDirect ? 'Direct' : 'Différé' ?></td>
						<td><img src="<?= $action->statut == Bouzouk::Clans_ActionEnCours ? img_url('succes.png') : img_url('echec.png') ?>" alt="En cours" width="20"></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>

	<?php if ($this->session->userdata('clan_grade')[$clan->type] == Bouzouk::Clans_GradeChef): ?>
		<!-- Chef : modifier le clan -->
		<div class="cellule_bleu_type1">
			<h4>Gérer le clan</h4>
			<div class="bloc_bleu modifier">
			<?= form_open('clans/modifier/'.$types[$clan->type]) ?>
				<table class="pourpre">
					<tr>
						<td class="highlight">
								<label for="nom">
									Nom du clan<br>
									[<?= struls($this->bouzouk->config('clans_struls_renommer')) ?> pour modifier]
								</label>
						</td>
						<td><input type="text" name="nom" id="nom" value="<?= form_prep($clan->nom) ?>" maxlength="35" size="40"></td>
					<tr>
						<td class="highlight"><label for="mode_recrutement">Mode de recrutement</label></td>
						<td>
							<select name="mode_recrutement" id="mode_recrutement">
								<option value="<?= Bouzouk::Clans_RecrutementOuvert ?>"<?= $clan->mode_recrutement == Bouzouk::Clans_RecrutementOuvert ? ' selected' : '' ?>>Ouvert (tout le monde peut rejoindre)</option>
								<option value="<?= Bouzouk::Clans_RecrutementFerme ?>"<?= $clan->mode_recrutement == Bouzouk::Clans_RecrutementFerme ? ' selected' : '' ?>>Fermé (un chef doit valider la demande)</option>
								<option value="<?= Bouzouk::Clans_RecrutementInvisible ?>"<?= $clan->mode_recrutement == Bouzouk::Clans_RecrutementInvisible ? ' selected' : '' ?>>Invisible (il faut connaître le nom pour postuler)</option>
							</select>
						</td>
					</tr>
					
					<tr>
						<td class="highlight"><label for="mode_recrutement">Grade minimum pour gérer les actions</label></td>
						<td>
							<select name="grade_lancer_actions" id="grade_lancer_actions">
								<?php foreach ($grades as $grade => $nom): ?>
									<?php if ($grade >= Bouzouk::Clans_GradeMembre): ?>
										<option value="<?= $grade ?>"<?= $clan->grade_lancer_actions == $grade ? ' selected' : '' ?>><?= $nom ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>

					<tr>
						<td class="highlight"><label for="nom_chef">Renommer le chef</label></td>
						<td><input type="text" name="nom_chef" id="nom_chef" value="<?= form_prep($clan->nom_chef) ?>" maxlength="20"></td>
					</tr>

					<tr>
						<td class="highlight"><label for="nom_sous_chefs">Renommer les sous-chefs</label></td>
						<td><input type="text" name="nom_sous_chefs" id="nom_sous_chefs" value="<?= form_prep($clan->nom_sous_chefs) ?>" maxlength="20"></td>
					</tr>

					<tr>
						<td class="highlight"><label for="nom_membres">Renommer les membres</label></td>
						<td><input type="text" name="nom_membres" id="nom_membres" value="<?= form_prep($clan->nom_membres) ?>" maxlength="20"></td>
					</tr>

					<tr>
						<td class="highlight"><label for="nom_tests">Renommer les membres test</label></td>
						<td><input type="text" name="nom_tests" id="nom_tests" value="<?= form_prep($clan->nom_tests) ?>" maxlength="20"></td>
					</tr>

					<tr>
						<td class="highlight"><label for="description">Description du clan</label></td>
						<td>
							<textarea name="description" id="description" class="compte_caracteres" cols="50" rows="6" maxlength="250"><?= form_prep($clan->description) ?></textarea>
							<p id="description_nb_caracteres_restants" class="transparent centre">&nbsp;</p>
						</td>
					</tr>

					<tr>
						<td class="highlight">
							<label for="forum_mode">
								Forum Tobozon<br>
								[<?= struls($this->bouzouk->config('clans_struls_creer_forum')) ?> pour créer]<br>
								[<?= struls($this->bouzouk->config('clans_struls_modifier_forum')) ?> pour modifier]
							</label>
						</td>
						<td>
							Mode du forum :<br>
							<select name="forum_mode" id="forum_mode">
								<option value="0"<?= isset($forum) ? '' : ' selected' ?>>Pas de forum dédié</option>
								<option value="1"<?= isset($forum) && $forum->clan_mode == 1 ? ' selected' : '' ?>>Ouvert : tout le monde peut lire et écrire</option>
								<option value="2"<?= isset($forum) && $forum->clan_mode == 2 ? ' selected' : '' ?>>Fermé : tout le monde peut lire, seuls les membres peuvent écrire</option>
								<option value="3"<?= isset($forum) && $forum->clan_mode == 3 ? ' selected' : '' ?>>Caché : seuls les membres peuvent lire et écrire</option>
							</select><br>
							<span class="rouge">Attention : si tu choisis "Pas de forum dédié" alors ton forum<br>
							sera entièrement supprimé ainsi que tous les posts</span><br><br>

							Description du forum (optionnel) :<br>
							<input type="text" name="forum_description" maxlength="50" size="50" value="<?= isset($forum) ? form_prep($forum->forum_desc) : '' ?>">
						</td>
					</tr>
				</table>

				<p class="centre"><input type="submit" value="Modifier les paramètres du clan"></p>
			</form>
			</div>
		</div>

		<div class="demi_cellule_bleu_type1 gestion">
			<h4>Léguer le clan</h4>
			<div class="bloc_bleu centre padd_vertical">
				<p class="margin">
					Si tu ne choisis aucun successeur, le clan sera légué au plus ancien par date exacte d'entrée. Si aucun membre n'est apte à reprendre le clan, celui-ci sera supprimé.
				</p>
				<?= form_open('clans/leguer/'.$types[$clan->type], array('class' => 'centre')) ?>
					<p class="frameborder_bleu padd_vertical">
						Successeur :
						<select name="joueur_id">
							<option value="">-----</option>
							<?php foreach ($membres_heritage as $membre): ?>
								<option value="<?= $membre->id ?>"><?= $membre->pseudo ?></option>
							<?php endforeach; ?>
						</select><br>
						<input type="submit" name="leguer" value="Léguer">
					</p>
				</form>
			</div>
		</div>

		<div class="demi_cellule_bleu_type1 gestion marge_gauche">
			<h4>Supprimer le clan</h4>
			<div class="bloc_bleu centre padd_vertical">
				<p class="margin">Si tu supprimes le clan, aucun membre ne pourra le reprendre et tu pourras en recréer un autre directement après.</p>
				<?= form_open('clans/supprimer/'.$types[$clan->type], array('class' => 'centre')) ?>
					<input type="submit" name="supprimer" value="Supprimer">
				</form>
			</div>
		</div>
	<?php else: ?>
		<!-- Sous-chefs et membres : quitter le clan -->
		<div class="cellule_bleu_type1">
			<h4>Quitter le clan</h4>
			<div class="bloc_bleu quitter padd_vertical">
				<p class="mini_bloc">
					Les points  d'action donnés à ce clan ne peuvent pas être repris.
				</p>
				<?= form_open('clans/quitter/'.$types[$clan->type], array('class' => 'centre')) ?>
					<label>Si tu quittes le clan, tu pourras en rejoindre un autre ou celui-ci immédiatement après.</label><input type="submit" name="quitter" value="Quitter">
				</form>
			</div>
		</div>
	<?php endif; ?>
</div>