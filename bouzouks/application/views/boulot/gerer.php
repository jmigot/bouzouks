<?php
$this->layout->set_title("Bon allez...au boulot !");
$this->layout->ajouter_javascript('boulot.js');
?>

<div id="boulot-gerer">
	<!-- Menu -->
	<?php $this->load->view('boulot/menu', array('lien' => 1)) ?>

	<!-- Infos de l'entreprise -->
	<div class="infos_gestion">
		<!-- Infos de gauche -->
		<h4><?= form_prep($entreprise->nom) ?></h4>
		<div class="bloc_gris">
			<p class="mini_bloc">
				Classement : <?= isset($entreprise->position) ? '<b>#'.$entreprise->position.'</b> <img src="'.img_url('communaute/'.$entreprise->evolution.'.png').'" alt="Evolution" height="15">' : '<span class="italique">pas encore classé</span>' ?> 
			</p>
			<p class="finances frameborder_gris">
				<span class="margin-mini">Mon job: <?= $job->nom ?></span>
			</p>
			<p class="patron frameborder_gris">
				Dernier bonus : <img src="<?= $job->dernier_bonus ? img_url('entreprises/bonus_oui.png') : img_url('entreprises/bonus_non.png') ?>" alt="Bonus" width="15">
			</p>
			<p class="finances">
				<span class="margin-mini">Prime d'incompétence : <?= struls($job->prime_depart) ?></span>
			</p>
			<p class="patron">
				Syndicats : <?= $entreprise->syndicats_autorises ? 'Autorisés' : 'Non autorisés' ?> 
			</p>
			<p class="finances frameborder_gris">
				<span class="margin-mini">Salaire journalier : <?= $job->payer ? struls($job->salaire) : struls(0) ?></span>
			</p>
			<p class="patron frameborder_gris">
				Ancienneté : <?= pluriel($job->anciennete, 'jour') ?> 
			</p>
			<p class="finances">
				<span class="margin-mini">Salaire d'hier : <?= struls($job->dernier_salaire) ?></span>
			</p>
		</div>
		<!-- Infos du millieu -->
		<div class="stats">
			<p>
				Le Patron :<br><?= profil($entreprise->chef_id, $entreprise->pseudo_chef) ?><br>
			</p>
			<ul>
				<li><?= pluriel($nb_syndicats, 'syndicat') ?></li>
				<li><?= pluriel($nb_employes, 'employé') ?></li>
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
			'url_rafraichir'  => 'webservices/rafraichir_tchat_entreprise',
			'url_poster'      => 'webservices/poster_tchat_entreprise',
			'nb_messages_max' => $this->bouzouk->config('maintenance_tchats_messages_entreprise')
		);
		$this->load->view('machine_a_cafe', $vars);
	?>

	<!-- Tableau d'affichage -->
		<div class="tableau_affichage">
			<?php if ($entreprise->message_1 != '' AND $entreprise->message_2 != ''): ?>
				<div class="tableau_plein">
					<p class="message_1"><?= nl2br(form_prep($entreprise->message_1)) ?></p>
					<p class="message_2"><?= nl2br(form_prep($entreprise->message_2)) ?></p>
				</div>
			<?php elseif ($entreprise->message_1 != ''): ?>
				<div class="tableau_1">
					<p class="message_1"><?= nl2br(form_prep($entreprise->message_1)) ?></p>
				</div>
			<?php elseif ($entreprise->message_2 != ''): ?>
				<div class="tableau_2">
					<p class="message_2"><?= nl2br(form_prep($entreprise->message_2)) ?></p>
				</div>
			<?php else: ?>
				<div class="tableau_vide">
				</div>
			<?php endif; ?>
		</div>

	<!-- Liste des employés -->
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Liste des employés</h4>
		<div class="bloc_bleu">
		<table class="liste_bouzouks">
			<tr>
				<?php $i = 0; ?>
				<?php foreach ($employes as $employe): ?>
					<td><?= profil($employe->id, $employe->pseudo, $employe->rang) ?></td>
					<?php if (++$i % 4 == 0): ?>
						</tr>
						<tr>
					<?php endif; ?>
				<?php endforeach; ?>
			</tr>
		</table>
		</div>
	</div>

	<!-- Démissionner -->
	<div class="cellule_gris_type2">
		<h4>Démissionner de l'entreprise</h4>
		<div class="bloc_gris">
			<div class="mini_bloc">
				<?= form_open('boulot/demissionner', array('class' => 'demissionner')) ?>
					<p class="centre marge_bas"><input type="submit" name="demissionner" value="Démissionner"></p>
				</form>
			</div>
			<p class="centre marge_haut marge_bas">
				Si tu démissionnes tu pourras trouver un autre job immédiatement.<br>
				Démissionner fait perdre <span class="pourpre"><?= $this->bouzouk->config('boulot_perte_xp_demission') ?> xp.
			</p>
		</div>
	</div>
</div>
