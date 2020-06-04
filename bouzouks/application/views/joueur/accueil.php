<?php
$this->layout->set_title('Bienvenue, '.$this->session->userdata('pseudo'));
$this->layout->ajouter_javascript('joueur.js');
?>

<div id="joueur-accueil">

	<?php if ($this->session->userdata('points_action') >= $this->bouzouk->config('joueur_points_action_max')): ?>

		<!-- Distribuer points d'action -->
		<div class="pts-actions msg-attention">
		<h2>Distribuer mes <?= $this->session->userdata('points_action') ?> points d'action</h2>
		<div class="points_action">
			<p>
				Tu as atteint le seuil minimum cumulable de <?= $this->bouzouk->config('joueur_points_action_max') ?> points d'action.<br>
				Tu ne gagneras plus d'autre point tant que tu n'auras pas distribués ceux-ci.
			</p>
			<p class="info_repartition">
				Tu as <span class="gras"><?= $this->session->userdata('points_action') ?> points</span> à distribuer.<br><br>
				Tu peux mettre tous tes points dans une seule statistique.<br><br>
				<span class="rouge_fonce gras"><u>Les points non attribués seront perdus</u></span>.
			</p>

			<?= form_open('joueur/distribuer_points_action') ?>
			<p class="formulaire">
				<label for="force" >Force</label><span class="form"><input type="text" name="force" id="force" size="4" maxlength="4" value="0"> points</span><br />
				<label for="charisme" >Charisme</label><span class="form"><input type="text" name="charisme" id="charisme" size="4" maxlength="4" value="0"> points</span><br />
				<label for="intelligence" >Intelligence</label><span class="form"><input type="text" name="intelligence" id="intelligence" size="4" maxlength="4" value="0"> points</span><br />
				<span class="resultat">Total:</span> <span class="total"> 0</span> <span class="total_maximum"> / <?= $this->session->userdata('points_action') ?></span><input class="bouton_vert" type="submit" value="Distribuer mes points">
			</p>
		</div>
	</div>
	<?php endif; ?>

<?php if ( ! $news_only): ?>
	<div class="jauges">
		<div class="stress">
			<p><?= $this->session->userdata('stress') ?>%</p>
			<img src="<?= img_url('jauge_stress.png') ?>" style="margin-top: <?= 30*(100-$this->session->userdata('stress'))/100 ?>px; " alt="Bouzouks.net" width="30" height="30" alt="Barre stress 3">
		</div>
		<p class="stress-nom">Stress</p>
		<div class="faim">
			<p><?= $this->session->userdata('faim') ?>%</p>
			<img src="<?= img_url('jauge_faim.png') ?>" style="margin-top: <?= 30*(100-$this->session->userdata('faim'))/100 ?>px; " alt="Bouzouks.net" width="30" height="30" alt="Barre stress 3">
		</div>
		<p class="faim-nom">Faim</p>
		<div class="sante">
			<p><?= $this->session->userdata('sante') ?>%</p>
			<img src="<?= img_url('jauge_sante.png') ?>" style="margin-top: <?= 30*(100-$this->session->userdata('sante'))/100 ?>px; " alt="Bouzouks.net" width="30" height="30" alt="Barre stress 3">
		</div>
		<p class="sante-nom">Santé</p>
	</div>	

	<div class="missives demi_cellule_bleu_type2">
		<h4>Dernières missives</h4>
		<div class="liste bloc_bleu">
			<table>
					<?php if (count($missives) == 0): ?>
					<tr>
						<td><p><i>Tu n'as reçu aucune missive, personne n'a pensé à toi même pas le percepteur !</i></td>
					</tr>
					<?php else: ?>
						<?php foreach ($missives as $missive): ?>
					<tr>
						<td><p><?= jour_mois($missive->date_envoi) ?> - <?= profil($missive->expediteur_id, $missive->expediteur, $missive->expediteur_rang) ?> - <a href="<?= site_url('missives/lire_recue/'.$missive->id) ?>"<?php if ($missive->lue == 0) echo ' class="gras"'; ?>><?= form_prep($missive->objet) ?></a></p></td>
					</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tr>
			</table>
		</div>
	</div>

	<div class="citation">
		<p>
			<?= $citation ?>
		</p>
	</div>	

	<div class="fiche marge_haut">
		<p class="personnage">
			<img src="<?= img_url('perso/corp/'.$this->session->userdata('perso').'.png') ?>" width="207" alt="Perso">
		</p>
		<h4 class="titre_boulot">Ma carrière</h4>
		<div class="boulot bloc_bleu">
			<p class="mini_bloc">Fortune : <?= struls($fortune) ?></p>
			<table>
				<tr>
					<td>
						<p>Statut : 
						<?php if ($this->session->userdata('chef_entreprise')): ?>
							Chef d'entreprise
						<?php elseif ($this->session->userdata('employe')): ?>
							Employé
						<?php else: ?>
							Chômeur
						<?php endif; ?>
						</p>
					</td>
				</tr>
				<tr>
					<td>
						<p>
						<?php if ($this->session->userdata('chef_entreprise')): ?>
							Nombre d'employé : <?= $this->session->userdata('nb_employe'); ?>
						<?php elseif ($this->session->userdata('entreprise_id') !== false): ?>
							Job : <?= $nom_job ?>
						<?php endif; ?>
						</p>
					</td>
				</tr>
			</table>
		</div>

		<!-- Clans -->
		<h4 class="titre_clans titre_boulot">Mes clans</h4>
		<div class="boulot bloc_bleu">
			<!-- Aucun clan -->
			<?php if ( ! $this->session->userdata('clan_id')[Bouzouk::Clans_TypeSyndicat] && ! $this->session->userdata('clan_id')[Bouzouk::Clans_TypePartiPolitique] && ! $this->session->userdata('clan_id')[Bouzouk::Clans_TypeOrganisation]): ?>
				<p class="padding">
					Tu n'es dans aucun clan...Tu te la joues cowboy solitaire ?!
				</p>
			<!-- Au moins un clan -->
			<?php else: ?>
				<table>
					<!-- Syndicat -->
					<tr>
						<td>
							<?php if ($this->session->userdata('clan_id')[Bouzouk::Clans_TypeSyndicat]): ?>
								<a href="<?= site_url('clans/gerer/syndicat') ?>"><?= $clans[Bouzouk::Clans_TypeSyndicat] ?></a>
							<?php else: ?>
								Aucun syndicat
							<?php endif; ?>
						</td>
					</tr>

					<!-- Parti politique -->
					<tr>
						<td>
							<?php if ($this->session->userdata('clan_id')[Bouzouk::Clans_TypePartiPolitique]): ?>
								<a href="<?= site_url('clans/gerer/parti_politique') ?>"><?= $clans[Bouzouk::Clans_TypePartiPolitique] ?></a>
							<?php else: ?>
								Aucun parti politique
							<?php endif; ?>
						</td>
					</tr>

					<!-- Organisation -->
					<tr>
						<td>
							<?php if ($this->session->userdata('clan_id')[Bouzouk::Clans_TypeOrganisation]): ?>
								<a href="<?= site_url('clans/gerer/organisation') ?>"><?= $clans[Bouzouk::Clans_TypeOrganisation] ?></a>
							<?php else: ?>
								Aucune organisation
							<?php endif; ?>
						</td>
					</tr>
				</table>
			<?php endif; ?>
		</div>
		<div class="stats">
			<h4>MES STATS</h4>
			<ul>
				<li>Expérience: <?= $this->session->userdata('experience') ?> xp</li>
				<li>Force : <?= pluriel($this->session->userdata('force'), 'pt') ?></li>
				<li>Charisme : <?= pluriel($this->session->userdata('charisme'), 'pt') ?></li>
				<li>Intelligence : <?= pluriel($this->session->userdata('intelligence'), 'pt') ?></li>
				<li>Points d’action : <?= pluriel($this->session->userdata('points_action'), 'pt') ?></li>
			</ul>
		</div>
	</div>

	<div class="toboz marge_haut">
		<h4>Actualités du toboz <?= $nb_signalements > 0 ? ' (<a href="'.site_url('tobozon/admin_reports.php').'" title="Signalements"  class="signal">'.$nb_signalements.'</a>)' : '' ?></h4>
		<div class="liste bloc_bleu">
			<table>
				<?php foreach ($tobozon as $post): ?>
				<tr>
					<td><p><?= jour_mois_heure_minute($post->date) ?> - <?= profil($post->joueur_id, $post->pseudo) ?> - <a href="<?= site_url('tobozon/viewtopic.php?pid='.$post->id.'#p'.$post->id) ?>"><?= form_prep($post->sujet) ?></a></p></td>
				</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>

	<div class="journal marge_haut">
		<h4>Dernier article de la gazette</h4>
		<div class="resume bloc_bleu">
		<p class="titre">
			<?= form_prep($gazette->titre) ?>
		</p>
		<p class="news">
			<?php
				$texte = form_prep($gazette->resume);
				$texte = preg_replace_callback('#{(.+)\|(\d+)}#Ui', create_function('$matches', 'return profil($matches[2], $matches[1]);'), $texte);
				echo $this->lib_parser->remplace_bbcode(nl2br($texte), 'gazette');
			?>
		</p>
		<p class="auteur">
			Par <?= profil($gazette->auteur_id, $gazette->pseudo) ?> - <a href="<?= site_url('gazette') ?>">Lire la suite</a> -

				<?php if (isset($gazette->nb_commentaires)): ?>
					<a href="<?= site_url('tobozon/viewtopic.php?id='.$gazette->topic_id) ?>"><?= pluriel($gazette->nb_commentaires, 'commentaire') ?></a>
				<?php endif; ?>
		</p>
		</div>
		<div class="tv">
			<p>
				<img src="<?= img_url('tv_hs.png') ?>" alt="Programme TV">
			</p>
		</div>
	</div>
<?php endif; ?>

<!-- News -->	
	<?php foreach ($news as $new): ?>
	<div class="cellule_gris_type2 marge_haut">
		<h4><?= form_prep($new->titre) ?></h4>
		<div class="bloc_gris">
				<p class="mini_bloc">
					Par <?= profil($new->auteur_id, $new->auteur, $new->auteur_rang) ?>, <?= bouzouk_datetime($new->date) ?>
				</p>
				<p class="margin-mini">
					<?= nl2br(remplace_smileys($new->texte)) ?>
				</p>
		</div>
	</div>
	<?php endforeach; ?>

	<!-- Pagination -->
	<p class="centre padding clearfloat"><?= $pagination ?></p>

</div>



