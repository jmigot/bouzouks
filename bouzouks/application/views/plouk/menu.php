<div class="onglet">
	<div class="menu">
		<a <?= menu_actif($lien, 1) ?>href="<?= site_url('plouk') ?>" title="Lister">Liste des parties</a>
		
		<?php if ($this->session->userdata('plouk_id')): ?>
			<a <?= menu_actif($lien, 2) ?>href="<?= site_url('plouk/jouer') ?>" title="Jouer">Jouer</a>
		<?php else: ?>
			<a <?= menu_actif($lien, 2) ?>href="<?= site_url('plouk/creer') ?>" title="Créer">Créer une partie</a>
		<?php endif; ?>
	</div>
	<div class="deco onglet<?= menu_actif() ?>">
	</div>
</div>