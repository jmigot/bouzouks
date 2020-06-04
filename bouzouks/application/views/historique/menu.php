<div class="onglet">
	<div class="menu">
		<a <?= menu_actif($lien, 1) ?>href="<?= site_url('historique') ?>" title="Historique">Historique</a>
		<a <?= menu_actif($lien, 2) ?>href="<?= site_url('historique/amis') ?>" title="Historique amis">Historique amis</a>
		<a <?= menu_actif($lien, 3) ?>href="<?= site_url('historique/notifications') ?>" title="Notifications">Notifications</a>
	</div>

	<div class="deco onglet<?= menu_actif() ?>">
	</div>
</div>