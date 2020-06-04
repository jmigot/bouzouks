<div class="onglet">
	<div class="menu">
		<a <?= menu_actif($lien, 1) ?>href="<?= site_url('entreprises/gerer') ?>" title="Gérer l'entreprise">Entreprise</a>
		<a <?= menu_actif($lien, 2) ?>href="<?= site_url('recrutement') ?>" title="Recrutement">Recrutement</a>
		<a <?= menu_actif($lien, 3) ?>href="<?= site_url('recrutement/lister_chomeurs') ?>" title="Chômeurs">Chômeurs</a>
		<a <?= menu_actif($lien, 4) ?>href="<?= site_url('entreprises/historique') ?>" title="Historique économique">Historique</a>
	</div>
	<div class="deco onglet<?= menu_actif() ?>">
	</div>
</div>