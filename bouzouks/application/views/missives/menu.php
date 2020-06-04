<div class="onglet">
	<div class="menu">
		<a <?= menu_actif($lien, 1) ?>href="<?= site_url('missives/recues') ?>" title="Reçues">Reçues</a>
		<a <?= menu_actif($lien, 2) ?>href="<?= site_url('missives/envoyees') ?>" title="Envoyées">Envoyées</a>
		<a <?= menu_actif($lien, 3) ?>href="<?= site_url('missives/ecrire') ?>" title="Ecrire">Ecrire</a>
		<a <?= menu_actif($lien, 4) ?>href="<?= site_url('factures') ?>" title="Factures">Factures</a>
	</div>
	<div class="deco onglet<?= menu_actif() ?>">
	</div>
</div>
