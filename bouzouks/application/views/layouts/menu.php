<div class="corde_menu">
	<?php if ($statut == Bouzouk::Joueur_Etudiant): ?>

		<!-- Etudier -->
		<div class="menu">
			<div class="fond_menu">
				<h4 class="titre_orange">Etudier</h4>
				<ul>
					<li><a href="<?= site_url('controuille') ?>">Étudier</a></li>
					<li><a href="<?= site_url('mon_compte') ?>">Mon compte</a></li>
				</ul>
			</div>
			<div class="m_bas">
			</div>
		</div>

	<?php elseif ($statut == Bouzouk::Joueur_ChoixPerso): ?>

		<!-- Choix perso -->
		<div class="menu">
			<div class="fond_menu">
				<h4 class="titre_orange">Choix perso</h4>
				<ul>
					<li><a href="<?= site_url('joueur/choix_perso') ?>">Choisir mon bouzouk</a></li>
					<li><a href="<?= site_url('mon_compte') ?>">Mon compte</a></li>
				</ul>
			</div>
			<div class="m_bas"></div>
		</div>

	<?php elseif ($statut == Bouzouk::Joueur_Actif): ?>

		<!-- Quartier des Pochtrons -->
		<div class="menu">
			<div class="fond_menu">
				<h4 class="titre_orange">Quartier du Pochtron</h4>
				<ul>
					<li><a href="<?= site_url('maison') ?>">Ma maison</a></li>
					<li><a href="<?= site_url('missives') ?>">Boîte à missives <?= $nb_missives_non_lues > 0 ? '<span class="pourpre">('.$nb_missives_non_lues.')</span>' : '' ?></a></li>
					<?php if ($chomeur): ?>
						<!-- Chômeurs -->
						<?php if ($this->session->userdata('experience') >= $this->bouzouk->config('entreprises_xp_creer')): ?>
							<li><a href="<?= site_url('entreprises/creer') ?>">Créer une entreprise</a></li>
						<?php endif; ?>
					
						<li><a href="<?= site_url('anpe') ?>">Trouver un job</a></li>
					<?php elseif ($employe): ?>
						<!-- Employés -->
						<li><a href="<?= site_url('boulot/gerer') ?>">Mon boulot</a></li>
					<?php elseif ($chef_entreprise): ?>
						<!-- Patrons -->
						<li><a href="<?= site_url('entreprises/gerer') ?>">Gestion entreprise</a></li>
					<?php endif; ?>
						<!-- Carte 3D -->
					<?php if($this->bouzouk->is_admin() || $this->bouzouk->is_beta_testeur()): ?>
						<li><a href="<?= site_url('vlux') ?>">Vlurx 3D</a></li>
					<?php endif; ?>
				</ul>
			</div>
			<div class="m_bas">
			</div>
		</div>

		<!-- Quartier Commerçant -->
		<div class="menu">
			<div class="fond_menu">
				<h4 class="titre_orange">Zone Commerciale</h4>
				<ul>
					<li><a href="<?= site_url('magasins/bouffzouk') ?>">Bouffzouk</a></li>
					<li><a href="<?= site_url('magasins/indispenzouk') ?>">Indispenzouk</a></li>
					<li><a href="<?= site_url('magasins/luxezouk') ?>">Luxezouk</a></li>
					<li><a href="<?= site_url('magasins/boostzouk') ?>">Boostzouk</a></li>
					<li><a href="<?= site_url('marche_noir') ?>">Marché noir</a></li>
				</ul>
			</div>
			<div class="m_bas">
			</div>
		</div>

		<!-- Quartier des nigots -->
		<div class="menu">
			<div class="fond_menu">
				<h4 class="titre_orange">Salle des Nigauds</h4>
				<ul>
					<!-- Jeux, classements, rumeurs -->
					<li><a href="<?= site_url('plouk') ?>">Le Plouk</a></li>
					<li><a href="<?= site_url('jeux/bonneteau') ?>">Bonneteau</a></li>
					<li><a href="<?= site_url('jeux/scraplouk') ?>">Pouzzle</a></li>
					<li><a href="<?= site_url('jeux/lohtoh') ?>">Lohtoh</a></li>
				</ul>
			</div>

			<div class="m_bas">
			</div>
		</div>

		<!-- Quartier administratif -->
		<div class="menu">
			<div class="fond_menu">
				<h4 class="titre_orange">Centre Administratif</h4>
				<ul>
					<!-- Elections -->
					<li><a href="<?= site_url('elections') ?>">Élections</a></li>
					
					<!-- Mairie-->
					<li><a href="<?= site_url('mairie') ?>">Mairie</a></li>
					
					<?php if ($this->session->userdata('maire') || $this->bouzouk->is_admin()): ?>
						<!-- Gérer la mairie -->
						<li><a href="<?= site_url('mairie/gerer') ?>">Gestion mairie</a></li>
					<?php endif; ?>

					<!-- Mendier -->
					<li><a href="<?= site_url('mendiants/mendier') ?>">Mendier</a></li>
					<li><a href="<?= site_url('mendiants/liste') ?>">Les mendiants</a></li>
					
					<!-- Liste des habitants -->
					<li><a href="<?= site_url('communaute/lister_bouzouks') ?>">Liste des habitants</a></li>
				</ul>
			</div>
			<div class="m_bas">
			</div>
		</div>

		<!-- Quartier d'la Magouille -->
		<div class="menu">
			<div class="fond_menu">
				<h4 class="titre_orange">Zone 51</h4>
				<ul>
					<!-- Clans -->
					<?php if ($this->session->userdata('clan_id')[Bouzouk::Clans_TypeSyndicat]): ?>
						<li><a href="<?= site_url('clans/gerer/syndicat') ?>">Mon syndicat</a></li>
					<?php endif ?>
					
					<?php if ($this->session->userdata('clan_id')[Bouzouk::Clans_TypePartiPolitique]): ?>
						<li><a href="<?= site_url('clans/gerer/parti_politique') ?>">Mon parti</a></li>
					<?php endif ?>
					
					<?php if ($this->session->userdata('clan_id')[Bouzouk::Clans_TypeOrganisation]): ?>
						<li><a href="<?= site_url('clans/gerer/organisation') ?>">Mon organisation</a></li>
					<?php endif ?>
					
					<!-- Liste des clans -->
					<li><a href="<?= site_url('clans/lister') ?>">Liste des clans</a></li>
				</ul>
			</div>
			<div class="m_bas">
			</div>
		</div>

		<!-- Mes infos -->
		<div class="menu">
			<div class="fond_menu">
				<h4 class="titre_rouge">Mes infos</h4>
				<ul>
					<li><a href="<?= site_url('mon_compte') ?>">Mon compte</a></li>
					<li><a href="<?= site_url('historique') ?>">Historique</a></li>
					<li><a href="<?= site_url('communaute/classements') ?>">Classements</a></li>
					
					<!-- Rumeurs -->
					<?php if ($this->session->userdata('experience') >= $this->bouzouk->config('communaute_xp_proposer_rumeur')): ?>
						<li><a href="<?= site_url('communaute/rumeurs') ?>">Poster une rumeur</a></li>
					<?php endif; ?>
				</ul>
			</div>
			<div class="m_bas">
			</div>
		</div>

	<?php elseif ($statut == Bouzouk::Joueur_Asile): ?>

		<!-- Quartier des Pochtrons -->
		<div class="menu">
			<div class="fond_menu">
				<h4 class="titre_orange">Quartier des Fous</h4>
				<ul>
					<li><a href="<?= site_url('joueur/asile') ?>">Asile</a></li>
				</ul>
			</div>
			<div class="m_bas">
			</div>
		</div>

		<!-- Quartier des nigots -->
		<div class="menu">
			<div class="fond_menu">
				<h4 class="titre_orange">Quartier des Nigots</h4>
				<ul>
					<!-- Jeux, classements, rumeurs -->
					<li><a href="<?= site_url('plouk') ?>">Le Plouk</a></li>
					<li><a href="<?= site_url('jeux/scraplouk') ?>">Pouzzle</a></li>
				</ul>
			</div>
			<div class="m_bas">
			</div>
		</div>

		<!-- Quartier administratif -->
		<div class="menu">
			<div class="fond_menu">
				<h4 class="titre_orange">Quartier Administratif</h4>
				<ul>				
					<!-- Liste des habitants -->
					<li><a href="<?= site_url('communaute/lister_bouzouks') ?>">Liste des habitants</a></li>
				</ul>
			</div>
			<div class="m_bas">
			</div>
		</div>

		<!-- Mes infos -->
		<div class="menu">
			<div class="fond_menu">
				<h4 class="titre_rouge">Mes infos</h4>
				<ul>
					<li><a href="<?= site_url('mon_compte') ?>">Mon compte</a></li>
					<li><a href="<?= site_url('missives') ?>">Boîte à missives <?= $nb_missives_non_lues > 0 ? '<span class="pourpre">('.$nb_missives_non_lues.')</span>' : '' ?></a></li>
					<li><a href="<?= site_url('historique') ?>">Historique</a></li>
					<li><a href="<?= site_url('communaute/classements') ?>">Classements</a></li>
					
					<!-- Rumeurs -->
					<?php if ($this->session->userdata('experience') >= $this->bouzouk->config('communaute_xp_proposer_rumeur')): ?>
						<li><a href="<?= site_url('communaute/rumeurs') ?>">Poster une rumeur</a></li>
					<?php endif; ?>
				</ul>
			</div>
			<div class="m_bas">
			</div>
		</div>

	<?php elseif ($statut == Bouzouk::Joueur_Pause): ?>

		<!-- Quartier des Pochtrons -->
		<div class="menu">
			<div class="fond_menu">
				<h4 class="titre_orange">Quartier des Pochtrons</h4>
				<ul>
					<li><a href="<?= site_url('joueur/en_pause') ?>">Pause</a></li>
				</ul>
			</div>
			<div class="m_bas">
			</div>
		</div>

		<!-- Quartier administratif -->
		<div class="menu">
			<div class="fond_menu">
				<h4 class="titre_orange">Quartier Administratif</h4>
				<ul>				
					<!-- Liste des habitants -->
					<li><a href="<?= site_url('communaute/lister_bouzouks') ?>">Liste des habitants</a></li>
				</ul>
			</div>
			<div class="m_bas">
			</div>
		</div>

		<!-- Mes infos -->
		<div class="menu">
			<div class="fond_menu">
				<h4 class="titre_rouge">Mes infos</h4>
				<ul>
					<li><a href="<?= site_url('mon_compte') ?>">Mon compte</a></li>
					<li><a href="<?= site_url('communaute/classements') ?>">Classements</a></li>
				</ul>
			</div>
			<div class="m_bas">
			</div>
		</div>
		
	<?php elseif ($statut == Bouzouk::Joueur_GameOver): ?>

		<!-- Quartier des Pochtrons -->
		<div class="menu">
			<div class="fond_menu">
				<h4 class="titre_orange">Quartier des Pochtrons</h4>
				<ul>
					<li><a href="<?= site_url('joueur/game_over') ?>">Game over</a></li>
				</ul>
			</div>
			<div class="m_bas">
			</div>
		</div>

		<!-- Mes infos -->
		<div class="menu">
			<div class="fond_menu">
				<h4 class="titre_rouge">Mes infos</h4>
				<ul>
					<li><a href="<?= site_url('mon_compte') ?>">Mon compte</a></li>
				</ul>
			</div>
			<div class="m_bas">
			</div>
		</div>
	<?php endif; ?>

	<?php if ($this->bouzouk->is_journaliste() || $this->bouzouk->is_moderateur()): ?>

		<div class="menu">
			<div class="fond_menu">
				<h4 class="titre_rouge">Gestion</h4>
				<ul>
			
	<?php endif; ?>

	<?php if ($this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurProfils | Bouzouk::Rang_ModerateurTchats)): ?>

		<!-- Tchats asile, chômeur et mendiants -->
		<li><a href="<?= site_url('joueur/asile') ?>">Tchat asile</a></li>

		<?php if ($this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats)): ?>
			<li><a href="<?= site_url('anpe/machine_a_cafe') ?>">Tchat chômeurs</a></li>
			<li><a href="<?= site_url('mendiants/machine_a_cafe') ?>">Tchat mendiants</a></li>
		<?php endif; ?>
		
	<?php endif; ?>

	<?php if ($this->bouzouk->is_journaliste()): ?>

		<!-- Gérer la gazette et les piges -->
		<li><a href="<?= site_url('gazette/gerer') ?>">Gérer la gazette</a></li>
		<li><a href="<?= site_url('piges') ?>">Gérer les piges</a></li>
		
	<?php endif; ?>

	<?php if ($this->bouzouk->is_admin()): ?>
	
		<!-- Administration -->
		<?php $nb_alertes_total = $this->lib_staff->nb_alertes_total(); ?>
		<li><a href="<?= site_url('staff/accueil') ?>">Administration<?= $nb_alertes_total > 0 ? " (<span class='pourpre'>$nb_alertes_total</span>)" : '' ?></a></li>
		
	<?php elseif ($this->bouzouk->is_moderateur()): ?>
	
		<!-- Modération -->
		<?php $nb_alertes_total = $this->lib_staff->nb_alertes_total(); ?>
		<li><a href="<?= site_url('staff/accueil') ?>">Modération<?= $nb_alertes_total > 0 ? " (<span class='pourpre'>$nb_alertes_total</span>)" : '' ?></a></li>
		
	<?php endif; ?>

	<?php if ($this->bouzouk->is_journaliste() || $this->bouzouk->is_moderateur()): ?>

				</ul>
			</div>
		
			<div class="m_bas">
			</div>
		</div>

	<?php endif; ?>
</div>

<div id="lien_struls">
	<a href="<?= site_url('plus_de_struls') ?>"></a>
</div>
