<?php
$this->layout->set_title('Rechercher un clan');
$this->layout->ajouter_javascript('clans.js');
?>

<div id="clans-lister">
	<!-- Menu -->
	<?php $this->load->view('clans/menu_liste', array('lien' => 1)) ?>

	<div class="infos_clans">
		<p class="partisant">
			<img src="<?= img_url('clans/partisant.png') ?>" alt="Manif" >
		</p>
		<p class="description pourpre gras">
			Liste des syndicats, partis politiques et organisations recensés par la bouzopolice.<br><br>

			Certains clans ont choisi l'anonymat et ne figurent donc pas dans cette liste.<br>
			Pour postuler dans un clan invisible tu dois<br>connaître son nom exact et lui envoyer une demande.
		</p>
	</div>

	<div class="espace">
	<!-- Syndicats -->
	<div class="demi_cellule_gris_type1 syndicat">
		<h4>Syndicats d'entreprises</h4>
		<div class="bloc_gris">
			<!-- Message -->
			<p>Syndicats de ton entreprise</p>

			<?php $peut_rejoindre_syndicat = false; ?>

			<?php if ( ! $this->session->userdata('employe')): ?>
				<p class="margin pourpre">Tu dois être employé dans une entreprise pour pouvoir rejoindre un syndicat</p>
			<?php elseif ($this->session->userdata('clan_id')[Bouzouk::Clans_TypeSyndicat]): ?>
				<p class="margin pourpre">Tu es déjà dans un syndicat, si tu veux en rejoindre un autre tu dois d'abord quitter ton clan</p>
			<?php elseif ( ! $this->session->userdata('syndicats_autorises')): ?>
				<p class="margin pourpre">Ton patron n'autorise pas les syndicats dans son entreprise</p>
			<?php elseif ($this->session->userdata('experience') < $this->bouzouk->config('clans_xp_min_rejoindre_syndicat')): ?>
				<p class="margin pourpre">Il te faut au moins <b><?= $this->bouzouk->config('clans_xp_min_rejoindre_syndicat') ?> xp</b> pour pouvoir rejoindre un syndicat</p>
			<?php elseif ($nb_syndicats_entreprise == 0): ?>
				<p class="margin pourpre">
					Il n'y a encore aucun syndicat dans ton entreprise
					<?php if ( ! $this->session->userdata('chef_clan') && $this->session->userdata('experience') >= $this->bouzouk->config('clans_xp_min_creer_syndicat')): ?>
						Tu peux <a href="<?= site_url('clans/creer') ?>">en créer un</a> si ton entreprise l'autorise
					<?php endif; ?>
				</p>
			<?php else: $peut_rejoindre_syndicat = true; ?>
			<?php endif; ?>

			<!-- Liste entreprise -->
			<?php if ($this->session->userdata('entreprise_id')): ?>
				<?php foreach ($clans[Bouzouk::Clans_TypeSyndicat] as $clan): ?>
					<?php if ($clan->entreprise_id != $this->session->userdata('entreprise_id')) continue; ?>

					<div>
						<p class="descript_clan"><span class="nom_clan frameborder_bleu"> <?= form_prep($clan->nom) ?></span><input type="button" value="Infos" class="button fl-droite"></p>

						<div class="invisible infos clearfloat frameborder_bleu">
							<p class="description"><?= nl2br(form_prep($clan->description)) ?></p>
							<p class="chef"><?= form_prep($clan->nom_chef) ?> : <?= profil($clan->chef_id, $clan->chef_pseudo, $clan->chef_rang) ?></p>
							<p class="nb_membres"><?= pluriel($clan->nb_membres, 'membre') ?></p>

							<?php if ( ! $this->session->userdata('clan_id')[$clan->type] && $peut_rejoindre_syndicat): ?>
								<?= form_open('clans/postuler') ?>
									<p>
										<input type="hidden" name="clan_id" value="<?= $clan->id ?>"><br>
										<input type="checkbox" name="invisible" id="invisible_<?= $clan->id ?>"><label for="invisible_<?= $clan->id ?>">Membre invisible</label><br>
										<input type="submit" value="Postuler dans ce clan" class="margin">
									</p>
								</form>
							<?php endif; ?>
							<p class="hr"></p>
							<p class="margin"></p>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>

			<!-- Liste autres entreprises -->
			<p class="margin"></p>
			<p>Syndicats des autres entreprises</p>
			<?php foreach ($clans[Bouzouk::Clans_TypeSyndicat] as $clan): ?>
				<?php if ($clan->entreprise_id == $this->session->userdata('entreprise_id')) continue; ?>

				<div>
					<p class="clearfloat"><span class="nom_clan frameborder_gris"> <?= form_prep($clan->nom) ?></span><input type="button" value="Infos" class="button fl-droite"></p>

					<div class="invisible infos clearfloat frameborder_gris">
						<p class="description"><?= nl2br(form_prep($clan->description)) ?></p>
						<p class="chef"><?= form_prep($clan->nom_chef) ?> : <?= profil($clan->chef_id, $clan->chef_pseudo, $clan->chef_rang) ?></p>
						<p class="nb_membres"><?= pluriel($clan->nb_membres, 'membre') ?></p>
						<p class="nom_entreprises">Entreprise : <?= form_prep($clan->nom_entreprise) ?></p>
						<p class="hr"></p>
						<p class="margin"></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<!-- Postuler clan invisible -->
	<div class="demi_cellule_bleu_type2">
		<h4>Postuler dans un clan caché</h4>
		<div class="bloc_bleu padd_vertical">
			<p class="frameborder_bleu padding">Il y a <?= pluriel($nb_clans_caches, 'clan') ?> en mode invisible</p>

			<?php 
				$options = array(
					Bouzouk::Clans_TypeSyndicat       => false,
					Bouzouk::Clans_TypePartiPolitique => false,
					Bouzouk::Clans_TypeOrganisation   => false
				);
				$nb_options = 0;
				
				if ( ! $this->session->userdata('clan_id')[Bouzouk::Clans_TypeSyndicat] && $this->session->userdata('syndicats_autorises') && $this->session->userdata('experience') >= $this->bouzouk->config('clans_xp_min_rejoindre_syndicat'))
					$options[Bouzouk::Clans_TypeSyndicat] = true;
				
				if ( ! $this->session->userdata('clan_id')[Bouzouk::Clans_TypePartiPolitique] && $this->session->userdata('experience') >= $this->bouzouk->config('clans_xp_min_rejoindre_parti_politique'))
					$options[Bouzouk::Clans_TypePartiPolitique] = true;

				if ( ! $this->session->userdata('clan_id')[Bouzouk::Clans_TypeOrganisation] && $this->session->userdata('experience') >= $this->bouzouk->config('clans_xp_min_rejoindre_organisation'))
					$options[Bouzouk::Clans_TypeOrganisation] = true;

				foreach ($options as $type => $value)
				{
					if ($value)
						$nb_options++;
				}
			?>

			<?php if ($this->session->userdata('nb_clans') >= Bouzouk::Clans_NbClansMax || ($this->session->userdata('nb_clans') == Bouzouk::Clans_NbClansMax - 1 && ! $this->session->userdata('syndicats_autorises'))): ?>
				<p class="margin frameborder_bleu arrondi padding">Tu ne peux rejoindre aucun clan pour le moment</p>
			<?php elseif ($nb_options == 0): ?>
				<p class="margin pourpre frameborder_bleu arrondi padding">Tu n'as pas assez d'expérience pour rejoindre un des types de clans disponibles pour toi</p>
			<?php else: ?>
				<p class="margin pourpre frameborder_bleu arrondi padding">
					Tu peux postuler dans un clan invisible<br>
					en entrant son nom exact
				</p>

				<?= form_open('clans/postuler_invisible') ?>
					<p class="margin frameborder_bleu arrondi padding">
						Nom du clan<br>
						<input type="text" name="nom" size="30" maxlength="30" value="<?= set_value('nom') ?>"><br>

						Type :
						<select name="type">
							<?php if ($options[Bouzouk::Clans_TypeSyndicat]): ?>
								<option value="<?= Bouzouk::Clans_TypeSyndicat ?>"<?= set_value('type') == Bouzouk::Clans_TypeSyndicat ? ' selected' : '' ?>>Syndicat</option>
							<?php endif; ?>

							<?php if ($options[Bouzouk::Clans_TypePartiPolitique]): ?>
								<option value="<?= Bouzouk::Clans_TypePartiPolitique ?>"<?= set_value('type') == Bouzouk::Clans_TypePartiPolitique ? ' selected' : '' ?>>Parti politique</option>
							<?php endif; ?>

							<?php if ($options[Bouzouk::Clans_TypeOrganisation]): ?>
								<option value="<?= Bouzouk::Clans_TypeOrganisation ?>"<?= set_value('type') == Bouzouk::Clans_TypeOrganisation ? ' selected' : '' ?>>Organisation</option>
							<?php endif; ?>
						</select><br><br>

						<input type="checkbox" name="invisible" id="invisible"><label for="invisible">Membre invisible</label><br>
						<input type="submit" value="Envoyer la demande">
					</p>
				</form>
			<?php endif; ?>
		</div>
	</div>

	</div>


	<!-- Partis politiques et organisations -->
	<?php foreach (array(Bouzouk::Clans_TypePartiPolitique => 'Partis politiques', Bouzouk::Clans_TypeOrganisation => 'Organisations') as $type => $nom_type): ?>
	<div class="demi_cellule_bleu_type1">
		<h4><?= $nom_type ?></h4>
		<div class="bloc_bleu padd_vertical">
				<!-- Message -->
				<?php if ($this->session->userdata('clan_id')[$type]): ?>
					<p class="margin pourpre">Tu as déjà un clan de ce type, si tu veux en rejoindre un autre tu dois d'abord quitter ton clan</p>
				<?php elseif ($this->session->userdata('experience') < $this->bouzouk->config('clans_xp_min_rejoindre_'.$types[$type])): ?>
					<p class="margin pourpre">Il te faut au moins <b><?= $this->bouzouk->config('clans_xp_min_rejoindre_'.$types[$type]) ?> xp</b> pour pouvoir rejoindre ce type de clan</p>
				<?php endif; ?>
			
				<!-- Liste -->
				<?php foreach ($clans[$type] as $clan): ?>
					<div>
						<p class="clearfloat<?= $clan->type_officiel != '' ? ' gras' : '' ?>"><span class="nom_clan frameborder_bleu"> <?= form_prep($clan->nom) ?></span><input type="button" value="Infos" class="button fl-droite"></p>

						<div class="invisible infos clearfloat frameborder_bleu">
							<p class="description"><?= nl2br(form_prep($clan->description)) ?></p>
							<p class="chef"><?= form_prep($clan->nom_chef) ?> : <?= profil($clan->chef_id, $clan->chef_pseudo, $clan->chef_rang) ?></p>
							<p class="nb_membres"><?= pluriel($clan->nb_membres, 'membre') ?></p>

							<?php if ( ! $this->session->userdata('clan_id')[$clan->type]): ?>
								<?= form_open('clans/postuler') ?>
									<p>
										<input type="hidden" name="clan_id" value="<?= $clan->id ?>"><br>
										<input type="checkbox" name="invisible" id="invisible_<?= $clan->id ?>"><label for="invisible_<?= $clan->id ?>">Membre invisible</label><br>
										<input type="submit" value="Postuler dans ce clan" class="margin">
									</p>
								</form>
							<?php endif; ?>
							<p class="hr"></p>
							<p class="margin"></p>
						</div>
					</div>
				<?php endforeach; ?>
		</div>
	</div>
	<?php endforeach; ?>

	
</div>
