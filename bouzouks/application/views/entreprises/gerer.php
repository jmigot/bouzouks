<?php
$this->layout->set_title("Gestion de l'entreprise");
$this->layout->ajouter_javascript('entreprises.js');
?>

<div id="entreprises-gerer">
	<!-- Menu -->
	<?php $this->load->view('entreprises/menu', array('lien' => 1)) ?>

	<!-- Infos de l'entreprise -->
	<div class="infos_gestion">
		<!-- Infos de gauche -->
		<h4><?= form_prep($entreprise->nom) ?></h4>
		<div class="bloc_gris">
			<p class="mini_bloc">
				Classement : <?= isset($entreprise->position) ? '<b>#'.$entreprise->position.'</b> <img src="'.img_url('communaute/'.$entreprise->evolution.'.png').'" alt="Evolution" height="15">' : '<span class="italique">pas encore classé</span>' ?>
			</p>
			<p class="finances frameborder_gris">
				<span class="margin-mini">Argent disponible : <?= struls($entreprise->struls) ?></span>
			</p>
			<p class="patron frameborder_gris">
				Salaire d'hier : <?= struls($entreprise->dernier_salaire) ?>
			</p>
			<p class="finances">
				<span class="margin-mini">Dépenses par jour : <?= struls($total_salaires) ?></span>
			</p>
			<p class="patron">
				Ancienneté : <?= pluriel($entreprise->anciennete_chef, 'jour') ?>
			</p>
			<p class="finances frameborder_gris">
				<span class="margin-mini">Dernière rentrée : <?= struls($entreprise->derniere_rentree) ?></span>
			</p>
			<p class="patron frameborder_gris">
				Bonus patron : <img src="<?= $entreprise->dernier_bonus ? img_url('entreprises/bonus_oui.png') : img_url('entreprises/bonus_non.png') ?>" alt="Bonus" width="15">
			</p>
			<p class="finances">
				<span class="margin-mini">Cumul de la prochaine<br>rentrée d'argent : <?= struls($estimation) ?></span>
			</p>
		</div>
		<!-- Infos du millieu -->
		<div class="stats">
			<p>
				Prix officiel :<br><?= struls($entreprise->prix) ?>
			</p>
			<ul>
				<li><?= pluriel($nb_employes, 'employé') ?></li>
				<li><?= pluriel($nb_syndicats, 'syndicat') ?></li>
				<li><?= $nb_bonus ?> bonus hier</li>
			</ul>
		</div>
		<!-- Image de l'objet -->
		<div class="objet">
			<div class="polaroid">
				<p class="image_objet">
					<img src="<?= img_url($entreprise->image_url) ?>" alt="Image objet">
				<p>
				<p class="nom_objet">
					<?= $entreprise->nom_objet ?>
				</p>
			</div>
			<div class="trombone">
			</div>
		</div>
	</div>

	<!-- Machine à café -->
	<?php
		$vars = array(
			'url_rafraichir' => 'webservices/rafraichir_tchat_entreprise',
			'url_poster'     => 'webservices/poster_tchat_entreprise',
			'nb_messages_max' => $this->bouzouk->config('maintenance_tchats_messages_entreprise')
		);
		$this->load->view('machine_a_cafe', $vars);
	?>

	<!-- Actions de clans en cours -->
	<?php if (isset($pression_syndicale)): ?>
	<div class="action_clan">
		<h4>Pression syndicale</h4>
		<div class="bloc_bleu">
			<p class="margin centre">
				<?= $pression_syndicale->nom_clan ?> a lancé une <?= couleur($pression_syndicale->nom_action) ?> dans ton entreprise le <?= bouzouk_datetime($pression_syndicale->date_debut, 'court') ?>,
				tu ne peux pas baisser les salaires de tes employés (même non syndiqués)
			</p>
		</div>
	</div>
	<?php elseif (isset($soutien_salarial)): ?>
	<div class="action_clan">
		<h4>Soutien salarial</h4>
		<div class="bloc_bleu">
			<p class="margin centre">
				<?= $soutien_salarial->nom_clan ?> a lancé un <?= couleur($soutien_salarial->nom_action) ?> dans ton entreprise le <?= bouzouk_datetime($soutien_salarial->date_debut, 'court') ?>,
				tu ne peux pas baisser le salaire de ton employé <?= profil($soutien_salarial->parametres['joueur_id']) ?>
			</p>
		</div>
	</div>
	<?php elseif (isset($greve_entreprise)): ?>
	<div class="action_clan">
		<h4>Grêve entreprise</h4>
		<div class="bloc_bleu">
			<p class="margin centre">
				<?= $greve_entreprise->nom_clan ?> a lancé une <?= couleur($greve_entreprise->nom_action) ?> le <?= bouzouk_datetime($greve_entreprise->date_debut, 'court') ?>,
				tous les employés de ton entreprise seront improductifs à la prochaine production (y compris les employés non syndiqués)
			</p>
		</div>
	</div>
	<?php elseif (isset($greve_generale)): ?>
	<div class="action_clan">
		<h4>Grêve générale</h4>
		<div class="bloc_bleu">
			<p class="margin centre">
				<?= $greve_generale->nom_clan ?> a lancé une <?= couleur($greve_generale->nom_action) ?> le <?= bouzouk_datetime($greve_generale->date_debut, 'court') ?>,
				tous les employés syndiqués de toutes les entreprises sont improductifs pendant 3 jours
			</p>
		</div>
	</div>
	<?php endif; ?>

	<!-- Liste des employés -->
	<div class="liste_employes cellule_bleu_type1">
		<h4>Liste des employés</h4>
		<?= form_open('entreprises/modifier_employes') ?>
		<div class="bloc_bleu">
			<p class="mini_bloc">
				Tu dois attendre <span class="pourpre"><?= $this->bouzouk->config('entreprises_attente_embauche') ?>h</span> après l'embauche d'un bouzouk pour modifier ses infos.</br>
			</p>
			<div class="explication">
				<p class="pseudo">Pseudo</p>
				<p class="job">Job actuel</p>
				<p class="jours">Ancienneté</p>
				<p class="paye">Salaire (+prime)</p>
				<p class="bonus"><img src="<?= img_url('entreprises/bonus.gif') ?>" alt="Bonus" title="Bonus de production"></p>
				<p class="virer"><img src="<?= img_url('entreprises/virer.gif') ?>" alt="Virer" title="Virer"></p>
				<p><img src="<?= img_url('entreprises/payer.gif') ?>" alt="Payer" title="Payer"></p>
			</div>
			<?php foreach ($employes as $employe): ?>
			<div class="employes">
				<p class="gauche" >
					<?php if ($employe->syndique || $employe->chef_syndicat): ?>
						<span class="icone_blanc"><img src="<?= img_url('entreprises/syndique.png') ?>" alt="Syndiqué" width="12"></span>
					<?php endif; ?>
				</p>
				<p class="pseudo">
					<span class="<?= ($employe->statut == Bouzouk::Joueur_Actif) ? 'espace_tab' : 'highlight_gris' ?> pseudo"><?= profil($employe->id, $employe->pseudo) ?></span>
				</p>
				<p>
					<input type="hidden" name="ids[]" value="<?= $employe->id ?>">

						<?php
							// On regarde si l'employé peut avoir un nouveau job
							$job_atteint = false;
							$job_update = false;

							foreach ($jobs as $job)
							{
								if ($employe->experience + $employe->anciennete < $job->experience)
									break;

								if ($employe->job_id == $job->id)
									$job_atteint = true;

								else if ($job_atteint)
									$job_update = true;
							}
						?>

						<select name="job_ids[<?= $employe->id ?>]"<?= $job_update ? ' class="job_update"' : '' ?>>
							<?php foreach ($jobs as $job): ?>
								<?php if ($employe->experience + $employe->anciennete >= $job->experience): ?>
									<option value="<?= $job->id ?>"<?php if ($employe->job_id == $job->id) echo ' selected' ?>><?= $job->nom ?></option>
								<?php endif; ?>
							<?php endforeach; ?>
						</select>
				</p>
				<p class="jours">
					<?= pluriel($employe->anciennete, 'jour') ?>
				</p>
				<p class="paye">
					<input type="text" name="salaires[<?= $employe->id ?>]" size="3" maxlength="4" value="<?= $employe->salaire ?>"> struls (<?= pluriel($employe->prime_depart, 'strul') ?>)
				</p>
				<p class="check">
					<span class="icone_blanc"><img src="<?= $employe->dernier_bonus ? img_url('entreprises/bonus_oui.png') : img_url('entreprises/bonus_non.png') ?>" alt="Bonus" width="16"></span>
				</p>
				<p class="check">
					<input type="checkbox" name="virer[<?= $employe->id ?>]">
				</p>
				<p class="check">
					<input type="checkbox" name="payer[<?= $employe->id ?>]"<?php if ($employe->payer) echo ' checked' ?>>
				</p>
			</div>
			<?php endforeach; ?>
			<p class="droite"><input class="bouton_violet" type="submit" value="Modifier"></p>
			
			<p class="margin-mini">Une icône <img src="<?= img_url('entreprises/syndique.png') ?>" alt="Syndiqué" width="15"> devant le pseudo indique un employé syndiqué.<br>
			Une icône <img src="<?= img_url('entreprises/bonus_oui.png') ?>" alt="Bonus oui" width="15"> ou <img src="<?= img_url('entreprises/bonus_non.png') ?>" alt="Bonus non" width="15"> indique
			si l'employé a produit <a href="<?= site_url('site/faq/jobs') ?>">un bonus</a> durant la dernière production.
			</p>
		</div>
		</form>
	</div>

	<!-- Communiqués du tableau d'affichage -->
	<div class="communiques demi_cellule_gris_type2">
		<h4>Tableau d'affichage</h4>
		<div class="bloc_gris">
			<p class="margin-mini">Tu peux publier 2 communiqués qui seront visibles par tes employés sur le tableau d'affichage</p>

			<?= form_open('entreprises/modifier_messages') ?>
				<p class="frameborder_gris fl-gauche">
					<span class="margin-mini">Communiqué interne n°1 : </span><span id="message_1_nb_caracteres_restants" class="centre transparent format_1">&nbsp;</span><br>
					<span><textarea name="message_1" rows="3" maxlength="120" class="compte_caracteres bleu"><?= form_prep($entreprise->message_1) ?></textarea></span>
				</p>
				<p class="frameborder_gris fl-gauche">
					<span class="margin-mini">Communiqué interne n°2 : </span><span id="message_2_nb_caracteres_restants" class="centre transparent format_1">&nbsp;</span><br>
					<span><textarea name="message_2" rows="3" maxlength="120" class="compte_caracteres bleu"><?= form_prep($entreprise->message_2) ?></textarea></span>
				</p>
				<p class="boutons fl-gauche">
					<a href="#" class="previsualiser">Prévisualiser</a> <input class="bouton_bleu" type="submit" value="Valider la modification">
				</p>
			</form>
		</div>
	</div>


	<div class="gestion_chef demi_cellule_gris_type2">
		<h4>Comptabilité de l'entreprise</h4>
		<div class="bloc_gris">
			<!-- Injecter des struls dans l'entreprise -->
			<p>
			<?= form_open('entreprises/injecter_struls') ?>
				<label >Injecter des struls</label><span class="form"><input type="text" name="montant" size="4" maxlength="5"> struls</span> <input type="submit" value="Injecter">
			</form>
			</p>
			<!-- Choix du salaire du chef -->
			<p>
			<?= form_open('entreprises/modifier_salaire') ?>
				<label >Fixer mon salaire à</label><span class="form"><input type="text" name="salaire" size="3" maxlength="4" value="<?= $entreprise->salaire_chef ?>"> struls</span> <input type="submit" value="Modifier">
			</form>
			</p>
		</div>
	</div>

	<div class="paperasse demi_cellule_bleu_type1">
		<h4>Paperasse administrative</h4>
		<div class="bloc_bleu">
			<!-- Autres infos -->
			<?= form_open('entreprises/modifier_infos') ?>
				<p class="historique">
					<span class="fl-gauche">
						Donner aux employés l'accès à:<br>
						<input type="checkbox" id="publique" name="publique"<?= $entreprise->historique_publique ? ' checked' : '' ?>><label for="publique">Historique disponible aux employés</label><br>
						<input type="checkbox" id="syndicat" name="syndicat"<?= $entreprise->syndicats_autorises ? ' checked' : '' ?>><label for="syndicat">Syndicats autorisés</label><br>
					</span>
					<span class="fl-droite">
						<input class="bouton_violet" type="submit" value="Modifier l'accès">
					</span>
				</p>
			</form>
			<!-- Changer le nom de l'entreprise -->
			<?= form_open('entreprises/changer_nom') ?>
				<div class="frameborder_bleu">
					<p>Changer le nom de l'entreprise (Prix: <?= struls($this->bouzouk->config('entreprises_prix_changer_nom')) ?>)<br>
						<span class="fl-gauche">
							<input class="violet" type="text" name="nom" maxlength="20">
						</span>
						<span class="fl-droite">
							<input class="bouton_violet fl-droite" type="submit" value="Changer">
						</span>
					</p>
				</div>
			</form>
            <!-- Changer le produit de l'entreprise -->
			<?= form_open('entreprises/changer_produit') ?>
				<div class="frameborder_bleu">
					<p>Changer le produit de l'entreprise (Prix: <?= struls($this->bouzouk->config('entreprises_prix_changement_produit')) ?>)<br>
						<span class="fl-gauche">
							<select name="objet_id" id="objet_id">
                                    <option value="" id="vide.gif">-- Objet qui sera produit --</option>

                                    <!-- Faim -->
                                    <optgroup label="Bouffzouk">
                                    <?php foreach ($objets['faim'] as $objet): ?>
                                        <option value="<?= $objet->id ?>" id="<?= $objet->image_url ?>"><?= $objet->nom.' ('.$objet->nb_entreprises.')' ?></option>
                                    <?php endforeach; ?>
                                    </optgroup>

                                    <!-- Santé -->
                                    <optgroup label="Indispenzouk">
                                    <?php foreach ($objets['sante'] as $objet): ?>
                                        <option value="<?= $objet->id ?>" id="<?= $objet->image_url ?>"><?= $objet->nom.' ('.$objet->nb_entreprises.')' ?></option>
                                    <?php endforeach; ?>
                                    </optgroup>

                                    <!-- Stress -->
                                    <optgroup label="Luxezouk">
                                    <?php foreach ($objets['stress'] as $objet): ?>
                                        <option value="<?= $objet->id ?>" id="<?= $objet->image_url ?>"><?= $objet->nom.' ('.$objet->nb_entreprises.')' ?></option>
                                    <?php endforeach; ?>
                                    </optgroup>
                                </select>
						</span>
						<span class="fl-droite">
							<input class="bouton_violet fl-droite" type="submit" value="Changer">
						</span>
					</p>
				</div>
			</form>
			<!-- Démissionner -->
			<?= form_open('entreprises/demissionner', array('class' => 'demissionner')) ?>
				<p class="demission fl-gauche">
					<span class="texte fl-gauche">
						Si tu démissionnes ton employé ayant le plus d'ancienneté héritera de l'entreprise.
					</span>
					<span class="fl-droite">
						<input class="bouton_violet" type="submit" name="demissionner" value="Démissionner">
					</span>
				</p>
			</form>
		</div>
	</div>
	<!-- Tableau d'affichage -->
	<div class="tableau_affichage invisible">
		<div class="tableau_plein">
			<p class="message_1"><?= nl2br(form_prep($entreprise->message_1)) ?></p>
			<p class="message_2"><?= nl2br(form_prep($entreprise->message_2)) ?></p>
		</div>
	</div>

</div>