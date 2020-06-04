<?php
$this->layout->set_title('Plouk - Liste');
$this->layout->ajouter_javascript('plouk-index.js');
?>

<div id="plouk-index">
	<!-- Menu -->
	<?php $this->load->view('plouk/menu', array('lien' => 1)) ?>

	<div class="cellule_bleu_type1 marge_bas">
		<h4>Envie de jouer au Plouk ?</h4>
		<div class="bloc_bleu">
			<p class="mini_bloc">
				N'hésites pas à aller dans la <a href="<?= site_url('site/faq/plouk') ?>">FAQ du jeu</a> pour connaître les règles.
			</p>
			<p class="fl-gauche margin"><img src="<?= img_url('plouk/banniere.jpg') ?>" alt="Bannière"></p>
			<p class="margin">
				Si tu n'arrives pas à gagner les élections tu peux toujours t'entraîner à augmenter ta popularité grâce à ce jeu de cartes multijoueur.<br><br>
				Voici la liste des parties disponibles. Tu peux rejoindre une partie en attente afin de tenter ta chance, ou bien simplement être spectateur d'une partie
				déjà commencée.<br><br>
				<span class="rouge">Attention : le Plouk n'est pas fait pour échanger des objets, <a href="<?= site_url('marche_noir') ?>">le marché noir</a> est là pour ça !</span>
			</p>
		</div>
	</div>

	<div class="cellule_gris_type2">
		<h4>Liste des parties en attente</h4>
		<div class="proposees bloc_gris">
			<table>
				<tr>
					<th>Créateur</th>
					<th>Objet</th>
					<th>Chrono / tours</th>
					<th>Cha.</th>
					<th>Méd.</th>
					<th>Par.</th>
					<th>MaC</th>
					<th>Action</th>
					<th></th>
				</tr>
						
				<?php foreach ($parties_attente as $partie): ?>
					<tr>
						<td><?= profil($partie->createur_id, $partie->createur_pseudo) ?></td>
						<td class="pourpre"><?= $partie->objet_nom != '' ? $partie->objet_nom : '<span class="italique">Aucun</span>' ?></td>
						<td class="centre pourpre"><p class="tab_espace"><?= $chronos[$partie->chrono] ?> / <?= $partie->nb_tours ?></p></td>
						<td>
							<img src="<?= img_url('plouk/charisme.gif') ?>" alt="Charisme" title="Charisme">
							<?= $partie->charisme ?>
						</td>
						<td>
							<img src="<?= img_url('plouk/mediatisation.gif') ?>" alt="Médiatisation" title="Médiatisation">
							<?= $partie->mediatisation ?>
						</td>
						<td>
							<img src="<?= img_url('plouk/partisans.gif') ?>" alt="Partisans" title="Partisans">
							<?= $partie->partisans ?>
						</td>
						<td>
							<?php if ( ! $partie->tchat): ?>
								<img src="<?= img_url('succes.png') ?>" alt="Publique" title="Les spectateurs peuvent parler">
							<?php else: ?>
								<img src="<?= img_url('echec.png') ?>" alt="Privée" title="Les spectateurs ne peuvent pas parler">
							<?php endif; ?>
						</td>
						<td>
							<?php if ($partie->createur_id != $this->session->userdata('id')): ?>
								<?= form_open('plouk/rejoindre') ?>
									<input type="hidden" name="partie_id" value="<?= $partie->id ?>">
									<input type="submit" value="Rejoindre">
								</form>
							<?php else: ?>
								<?= form_open('plouk/supprimer') ?>
									<input type="hidden" name="partie_id" value="<?= $partie->id ?>">
									<input type="submit" value="Supprimer">
								</form>
							<?php endif; ?>
						</td>
						<td><?= $partie->mot_de_passe != '' ? '<img src="'.img_url('plouk/cadenas.png').'" alt="Protégé par mot de passe" title="Protégé par mot de passe">' : '' ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>

	<div class="cellule_bleu_type1">
		<h4>Liste des parties en cours</h4>
		<div class="en_cours bloc_bleu">
			<table>
				<tr>
					<th>Créateur</th>
					<th>Adversaire</th>
					<th>Objet mis en jeu</th>
					<th>Tour</th>
					<th>MaC</th>
					<th>Spec.</th>
					<th>Suivre</th>
					<th></th>
				</tr>

				<?php foreach ($parties_en_cours as $partie): ?>
					<?= form_open('plouk/suivre', array('id' => 'partie_'.$partie->id, 'class' => 'suivre')) ?>
						<input type="hidden" name="partie_id" value="<?= $partie->id ?>">
						<input type="hidden" name="prive" id="prive_<?= $partie->id ?>" value="<?= $partie->mot_de_passe != '' ? 1 : 0 ?>">
						<input type="hidden" name="mot_de_passe" id="mot_de_passe_<?= $partie->id ?>" value="">
						
						<tr>
							<td><?= profil($partie->createur_id, $partie->createur_pseudo) ?></td>
							<td><?= profil($partie->adversaire_id, $partie->adversaire_pseudo) ?></td>
							<td class="pourpre"><?= $partie->objet_nom != '' ? $partie->objet_nom : '<span class="italique">Aucun</span>' ?></td>
							<td class="centre pourpre"><p class="tab_espace"><?= $partie->tour_actuel ?> / <?= $partie->nb_tours ?></p></td>
							<td>
								<?php if ( ! $partie->tchat): ?>
									<img src="<?= img_url('succes.png') ?>" alt="Publique" title="Les spectateurs peuvent parler">
								<?php else: ?>
									<img src="<?= img_url('echec.png') ?>" alt="Privée" title="Les spectateurs ne peuvent pas parler">
								<?php endif; ?>
							</td>
							<td><?= isset($partie->nb_connectes) ? $partie->nb_connectes : '0' ?> / <?= $this->bouzouk->config('plouk_max_spectateurs') ?></td>
							<td>
								<?php if ($partie->id != $this->session->userdata('plouk_id')): ?>
									<input type="submit" value="Suivre">
								<?php endif; ?>
							</td>
							<td><?= $partie->mot_de_passe != '' ? '<img src="'.img_url('plouk/cadenas.png').'" alt="Protégé par mot de passe" title="Protégé par mot de passe">' : '' ?></td>
						</tr>
					</form>
				<?php endforeach; ?>
			</table>
		</div>
	</div>

	<div class="cellule_gris_type2 marge_haut">
		<h4>Liste des connectés</h4>
		<div class="bloc_gris">
			<table id="connectes" class="liste_bouzouks">
			</table>
		</div>
	</div>
</div>
