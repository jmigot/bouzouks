<div class="onglet">
	<div class="menu">
		<?php if ($article_id != '0'): ?>
			<a <?= menu_actif($lien, 1) ?>href="<?= site_url('gazette/lire/'.$article_id) ?>" title="Lire">Aperçu</a>
			<a <?= menu_actif($lien, 2) ?>href="<?= site_url('gazette/rediger/'.$article_id) ?>" title="Modifier">Modifier</a>
			<a <?= menu_actif($lien, 3) ?>href="<?= site_url('gazette/historique_article/'.$article_id) ?>" title="Historique">Historique</a>
		<?php endif; ?>
		
		<a class="actif" href="<?= site_url('gazette/gerer') ?>" title="Retour gazette">Retour gazette</a>
	</div>
	<div class="deco onglet<?= menu_actif() ?>">
	</div>
</div>