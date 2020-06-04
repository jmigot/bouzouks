<?php $this->layout->set_title("Proposition d'emploi"); ?>

<div id="anpe-voir">
	<!-- Menu -->
	<?php $this->load->view('anpe/menu', array('lien' => 1)) ?>
	
	<!-- En-tete de l'annonce -->
	<div id="en-tete">
		<p class="image"><img src="<?= img_url($job->objet_image_url) ?>" alt="<?= $job->objet ?>"></p>
		<p class="lien"><a href="<?= site_url('anpe/lister') ?>">&lt;&lt; Retour à la liste des annonces</a></p>
		<p class="titre"><?= form_prep($job->nom) ?></p>
		<p class="bloc-gauche">
			<i>Employeur : </i><span class="pourpre"><?= profil($job->chef_id, $job->chef) ?></span><br>
			<i>Date de création : </i><span class="pourpre"><?= bouzouk_date($job->date_creation) ?></span>
		</p>
		<p class="bloc-droite">
			<i>Vente de : </i><span class="pourpre"><?= $job->objet ?></span><br>
			<i>Nombre d'employés : </i><span class="pourpre"><?= $job->nb_employes ?></span><br>
			<i>Syndicats autorisés : </i><span class="pourpre"><?= $job->syndicats_autorises ? 'oui' : 'non' ?></span>
		</p>
	</div>

	<!-- En savoir plus -->
	<div class="cellule_bleu_type1 marge_haut">
		<h4>En savoir plus sur ce job</h4>
		<div class="bloc_bleu">
			<div class="frameborder1 job">
				<div class="frameborder2">
					<p class="highlight">Job proposé</p>
					<p class="contenu"><span class="pourpre"><?= $job->job ?></span></p>
				</div>
			</div>

			<div class="frameborder1 salaire">
				<div class="frameborder2">
					<p class="highlight">Salaire proposé et prime d'incompétence</p>
					<p class="contenu">
						<?= struls($job->salaire) ?> / jour<br>
						<?= struls($job->prime_depart) ?> de prime d'incompétence
					</p>
				</div>
			</div>

			<div class="frameborder1 clearfloat">
				<div class="frameborder2">
					<p class="highlight">Message de l'employeur</p>
					<p class="contenu"><?= nl2br(form_prep($job->message)) ?></p>
				</div>
			</div>

			<?= form_open('anpe/proposer') ?>
				<p><input type="hidden" name="annonce_id" value="<?= $job->annonce_id ?>"></p>
				<p class="droite"><input type="submit" value="Accepter ce job"></p>
			</form>
		</div>
	</div>
</div>