<div class="onglet">
	<div class="menu">
		<a <?= menu_actif($lien, 1) ?>href="<?= site_url('mon_compte') ?>" title="Mon compte">Mon compte</a>
		
		<?php if ($this->session->userdata('statut') == Bouzouk::Joueur_Actif): ?>
			<a <?= menu_actif($lien, 2) ?>href="<?= site_url('mon_compte/bouzouk') ?>" title="Mon bouzouk">Mon bouzouk</a>
			<a <?= menu_actif($lien, 3) ?>href="<?= site_url('mon_compte/notifications') ?>" title="Mes notifs">Mes notifs</a>
			<a <?= menu_actif($lien, 4) ?>href="<?= site_url('mon_compte/param_map') ?>" title="Paramètres Vlurx 3D">Paramètres Map</a>
		<?php endif; ?>
	</div>

	<div class="deco onglet<?= menu_actif() ?>">
	</div>
</div>