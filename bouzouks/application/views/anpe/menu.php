<div class="onglet">
	<div class="menu">
		<a <?= menu_actif($lien, 1) ?>href="<?= site_url('anpe/rechercher') ?>" title="Rechercher une annonce">Recherche</a>
		<a <?= menu_actif($lien, 2) ?>href="<?= site_url('anpe/mes_annonces') ?>" title="Mes annonces">Mes annonces</a>
		<a <?= menu_actif($lien, 3) ?>href="<?= site_url('anpe/machine_a_cafe') ?>" title="Machine à café">Machine à café</a>
	</div>

	<div class="deco onglet<?= menu_actif() ?>">
	</div>
</div>
