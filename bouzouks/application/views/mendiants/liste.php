 <?php $this->layout->set_title(pluriel($nb_mendiants, 'mendiant recensé', 'mendiants recensés')); ?>

<div id="mendiants-liste">
	<?php 
		// ---------- Hook clans ----------
		// Tag MLBiste (MLB)
		if (($tag_mlbiste = $this->bouzouk->clans_tag_mlbiste()) != null)
			$this->load->view('clans/tag_mlb', array('tag_mlbiste' => $tag_mlbiste));
		
		if ($this->bouzouk->clans_grosse_manif_syndicale() != null)
			$this->load->view('clans/grosse_manif_syndicale');
	?>

	<!-- Si aucun mendiant en vue -->
	<?php if (count($mendiants) == 0): ?>
		<div id="blocage">
			<div class="cellule_bleu_type1 marge_haut">
				<h4>Aucun mendiant trouvé</h4>
				<div class="bloc_bleu">
					<p class="fl-gauche"><img src="<?= img_url('mendiants/aucun_mendiant.gif') ?>" alt="Illustration" class="image"></p>
					<p class="message">Aucun mendiant en vue. Roh ben zut alors...Qu'est-ce que je vais faire de mes sous moi ?</p>
					<p class="clearfloat"></p>
				</div>
			</div>
		</div>
	<?php else: ?>
		<!-- Liste des mendiants -->

			<?php foreach ($mendiants as $mendiant): ?>
				<?= form_open('mendiants/donner', array('class' => 'mendiant')) ?>
					<input type="hidden" name="joueur_id" value="<?= $mendiant->id ?>">
					<div class="bulle">
						<div class="haut">
						</div>
						<p><?= form_prep($mendiant->argument) ?></p>
					</div>
					<div class="perso">
						<?php 
						if ($this->bouzouk->est_infecte($mendiant->id)){ 
							$mendiant->perso = 'zombi/'.$mendiant->perso;
						}
						if($this->bouzouk->est_maudit_mlbobz($mendiant->id)){
							$mendiant->perso = 'rp_zoukette/'.$mendiant->perso;
						}
						?>
						<p><img src="<?= img_url(avatar($mendiant->faim, $mendiant->sante, $mendiant->stress, $mendiant->perso)) ?>" height="100" alt="Image perso"></p>
						<div class="dons">
							<p class="pseudo">
								<?= profil($mendiant->id, $mendiant->pseudo) ?>
							</p>
							<?php if ($mendiant->id != $this->session->userdata('id')): ?>
							<p>
								Lui donner <input type="text" name="montant" size="2" maxlength="3" placeholder="5"> struls
								<span class="donner"><input type="submit" value="Faire un don"></span>
							</p>
							<?php else: ?>
							<p class="marge_haut">C'est toi ! </p>
							<?php endif; ?>
						</div>
					</div>
				</form>
			<?php endforeach; ?>
			<p class="clearfloat"></p>
	<?php endif; ?>

	<!-- Mendiants à qui on a déjà donné -->
	<?php if (count($mendiants_donne) > 0): ?>
		<div class="cellule_gris_type1">
			<h4>Les derniers mendiants à qui tu as donné</h4>
			<div class="bloc_gris">
				<?php foreach ($mendiants_donne as $mendiant): ?>
				<div class="mendiant">
					<div class="bulle">
						<div class="haut">
						</div>
						<p><?= form_prep($mendiant->argument) ?></p>
					</div>
					<div class="perso">
						<?php
							if ($this->bouzouk->est_infecte($mendiant->id)){ 
								$mendiant->perso = 'zombi/'.$mendiant->perso;
							}
							if($this->bouzouk->est_maudit_mlbobz($mendiant->id)){
								$mendiant->perso = 'rp_zoukette/'.$mendiant->perso;
							}
						 ?>
						<p><img src="<?= img_url(avatar($mendiant->faim, $mendiant->sante, $mendiant->stress, $mendiant->perso)) ?>" height="100" alt="Image perso"></p>
						<div class="dons">
							<p class="pseudo">
								<?= profil($mendiant->id, $mendiant->pseudo) ?>
							</p>
							<p class="marge_haut">
								Don de <?= struls($mendiant->montant) ?>
							</p>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
				<p class="clearfloat"></p>
			</div>
		</div>
	<?php endif; ?>
</div>