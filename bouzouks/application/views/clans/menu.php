<div class="onglet">
	<div class="menu">
		<a <?= menu_actif($lien, 1) ?>href="<?= site_url('clans/gerer/'.$types[$clan->type]) ?>" title="Le clan">Le clan</a>
		
		<?php if ($this->session->userdata('clan_grade')[$clan->type] >= Bouzouk::Clans_GradeSousChef): ?>
			<a <?= menu_actif($lien, 2) ?>href="<?= site_url('clans/recrutement/'.$types[$clan->type]) ?>" title="Recrutement">Recrutement</a>
			<a <?= menu_actif($lien, 3) ?>href="<?= site_url('clans/historique/'.$types[$clan->type]) ?>" title="Historique">Historique</a>
		<?php endif; ?>
		
		<?php if (isset($espionnage)): ?>
			<a <?= menu_actif($lien, 4) ?>href="<?= site_url('clans/espionnage/'.$types[$clan->type]) ?>" title="Espionnage">Espionnage</a>
		<?php endif; ?>
		
		<?php if ($clan->type == Bouzouk::Clans_TypeSyndicat): ?>
			<a href="<?= site_url('boulot/gerer') ?>" title="Mon boulot">Mon boulot</a>
		<?php endif; ?>
	</div>
	<div class="deco onglet<?= menu_actif() ?>">
	</div>
</div>
