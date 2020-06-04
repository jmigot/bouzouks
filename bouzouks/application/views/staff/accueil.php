<?php

if ($this->bouzouk->is_admin())
	$this->layout->set_title('Administration');

else
	$this->layout->set_title('Modération');

?>

	<?php if ($this->bouzouk->is_admin()): ?>
		<div class="demi_cellule_bleu_type1 inline-block">
			<h4>Administration</h4>
			<div class="bloc_bleu">
				<ul>
					<?= $this->lib_staff->lien_admin('Configuration du jeu',           'gerer_config') ?>
					<?= $this->lib_staff->lien_admin('Connexion sous un autre pseudo', 'connexion_bouzouk') ?>
					<?= $this->lib_staff->lien_admin('Envoyer des missives',           'envoyer_missives') ?>
					<?= $this->lib_staff->lien_admin('Envoyer un email',               'envoyer_email') ?>
				</ul>

				<ul>
					<?= $this->lib_staff->lien_admin('Gérer les joueurs',  'gerer_joueurs') ?>
					<?= $this->lib_staff->lien_admin('Gérer les objets',   'gerer_objets') ?>
					<?= $this->lib_staff->lien_admin('Gerer les news',     'gerer_news') ?>
					<?= $this->lib_staff->lien_admin('Gérer le bot IRC',   'gerer_bot_irc') ?>
					<?= $this->lib_staff->lien_admin('Gérer les PNJ',      'gerer_pnj') ?>
					<?= $this->lib_staff->lien_admin('Gérer le site',      'gerer_site') ?>
					<?= $this->lib_staff->lien_admin('Gérer les actions clans', 'gerer_action_clan') ?>
				</ul>
				<ul>
					<?= $this->lib_staff->lien_admin('Gérer les campagnes FaceBook', 'gerer_campagne_fb') ?>
				</ul>

				<ul>
					<?= $this->lib_staff->lien_admin('Gérer Vlux',     'gerer_vlux') ?>
				</ul>
				<ul>
					<?= $this->lib_staff->lien_admin('Event Bouf\'Tête',     'gerer_bouf_tete') ?>
					<?= $this->lib_staff->lien_admin('Event MLBooobZ', 'gerer_event_mlbobz') ?>
				</ul>
			</div>
		</div>
	<?php endif; ?>

	<?php if ($this->bouzouk->is_moderateur()): ?>
		<div class="demi_cellule_bleu_type1 marge_haut marge_gauche">
			<h4>Modération</h4>
			<div class="bloc_bleu">
				<ul>
					<?= $this->lib_staff->lien_admin('Modérer les parrainages',    'moderer_parrainages', null, $alertes) ?>
					<?= $this->lib_staff->lien_admin('Modérer les rumeurs',        'moderer_rumeurs', null, $alertes) ?>
					<?= $this->lib_staff->lien_admin('Modérer les élections',      'moderer_elections') ?>
					<?= $this->lib_staff->lien_admin('Modérer les annonces',       'moderer_annonces') ?>
					<?= $this->lib_staff->lien_admin('Modérer les mendiants',      'moderer_mendiants') ?>
					<?= $this->lib_staff->lien_admin('Modérer les profils',        'moderer_profils') ?>
					<?= $this->lib_staff->lien_admin('Modérer les entreprises',    'moderer_entreprises') ?>
					<?= $this->lib_staff->lien_admin('Modérer le tchat IRC',       'moderer_irc') ?>
					<?= $this->lib_staff->lien_admin('Modérer les missives',       'moderer_missives') ?>
					<?= $this->lib_staff->lien_admin('Modérer le tobozon',         'moderer_tobozon') ?>
					<?= $this->lib_staff->lien_admin('Modérer les enchères clans', 'moderer_clans', null, $alertes) ?>
					<?= $this->lib_staff->lien_admin('Modérer les tchats clans',   'moderer_clans_tchats') ?>
					<?= $this->lib_staff->lien_admin('Modérer les tchats de maps', 'moderer_map_tchats', null, $alertes) ?>
					<?= $this->lib_staff->lien_admin('Convoquer un joueur',		 'convoquer_joueur') ?>
					<?= $this->lib_staff->lien_admin('Historique modération',          'historique_moderation', 'index') ?>
					<?= $this->lib_staff->lien_admin('Historique modération Tobozon',  'historique_moderation', 'tobozon') ?>
				</ul>
			</div>
		</div>
	<?php endif; ?>

	<?php if ($this->bouzouk->is_admin()): ?>
		<div class="demi_cellule_bleu_type1 inline-block marge_haut">
			<h4>Statistiques</h4>
			<div class="bloc_bleu">
				<ul>
					<?= $this->lib_staff->lien_admin('Statistiques',   'statistiques') ?>
					<?= $this->lib_staff->lien_admin('Plus de struls', 'plus_de_struls') ?>
					<?= $this->lib_staff->lien_admin('Dons Paypal',    'dons_paypal') ?>
				</ul>
			</div>
		</div>
	<?php endif; ?>

	<?php if ($this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurMulticomptes)): ?>
		<div class="demi_cellule_bleu_type1 marge_haut marge_gauche">
			<h4>Multicomptes</h4>
			<div class="bloc_bleu">
				<ul>
					<?= $this->lib_staff->lien_admin('Inscriptions',           'multicomptes', 'inscriptions') ?>
					<?= $this->lib_staff->lien_admin('Connexions',             'multicomptes', 'connexions') ?>
					<?= $this->lib_staff->lien_admin('Mots de passe',          'multicomptes', 'mots_de_passe') ?>
					<?= $this->lib_staff->lien_admin('Dates de naissance',     'multicomptes', 'dates_de_naissance') ?>
					<?= $this->lib_staff->lien_admin('Le Pioupiouk Chercheur', 'multicomptes', 'pioupiouk_chercheur') ?>
				</ul>

				<ul>
					<?= $this->lib_staff->lien_admin('Marché noir',        'multicomptes', 'marche_noir') ?>
					<?= $this->lib_staff->lien_admin('Votes élections',    'multicomptes', 'votes') ?>
					<?= $this->lib_staff->lien_admin('Payes employés',     'multicomptes', 'payes_employes') ?>
					<?= $this->lib_staff->lien_admin('Parties de Plouk',   'multicomptes', 'plouk_parties') ?>
				</ul>
			</div>
		</div>
	<?php endif; ?>