<div class="onglet">
	<div class="menu">
		<a <?= menu_actif($lien, 1) ?>href="<?= site_url('clans/lister') ?>" title="Liste des clans">Liste des clans</a>
		
		<!-- Création de clan -->
		<?php if ($this->session->userdata('nb_clans') < Bouzouk::Clans_NbClansMax && $this->session->userdata('experience') >= $this->bouzouk->config('clans_xp_min_creer_syndicat') &&
			! $this->session->userdata('chef_clan') && ! ($this->session->userdata('nb_clans') == Bouzouk::Clans_NbClansMax - 1 && ! $this->session->userdata('syndicats_autorises'))): ?>
			<a <?= menu_actif($lien, 2) ?>href="<?= site_url('clans/creer') ?>" title="Créer un clan">Créer un clan</a>
		<?php endif; ?>
		
		<a <?= menu_actif($lien, 3) ?>href="<?= site_url('clans/historique_actions') ?>" title="Historique actions">Historique actions</a>
	</div>
	<div class="deco onglet<?= menu_actif() ?>">
	</div>
</div>