<nav class="onglet">
	<div class="menu">
		<a <?= menu_actif($lien, 1) ?>href="<?= site_url('vlux') ?>" title="Accueil Vlux">Accueil</a>
		<a <?= menu_actif($lien, 2) ?>href="<?= site_url('vlux/aventure') ?>" title="Mode Aventure">VluxPad</a>
		<a <?= menu_actif($lien, 3) ?>href="<?= site_url('vlux/gestion') ?>" title="Mode Création">Mes maps</a>
	</div>
	<div class="deco onglet<?= menu_actif() ?>">
	</div>
</nav>