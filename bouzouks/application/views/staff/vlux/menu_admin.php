<nav class="onglet_admin">
	<div class="menu">
		<a <?= menu_actif($lien, 1) ?>href="<?= site_url('staff/gerer_vlux') ?>" title="Gestion Vlux">Vlux</a>
		<a <?= menu_actif($lien, 2) ?>href="<?= site_url('staff/gerer_serveurs') ?>" title="Gestion Serveur">Gestion Serveur</a>
		<a <?= menu_actif($lien, 3) ?>href="<?= site_url('staff/gerer_maps') ?>" title="Gestion Maps">Gestion Maps</a>
		<a <?= menu_actif($lien, 4) ?>href="<?= site_url('staff/gerer_items') ?>" title="Gestion Items">Gestion Items</a>
		<a <?= menu_actif($lien, 6) ?>href="<?= site_url('staff/gerer_batiments') ?>" title="Param Vlux">Gestion Build</a>
		<a <?= menu_actif($lien, 5) ?>href="<?= site_url('staff/gerer_vlux/gestion') ?>" title="Param Vlux">Param Vlux</a>
	</div>
</nav>