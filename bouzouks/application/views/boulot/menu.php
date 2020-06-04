<!-- Menu -->
<div class="onglet">
	<div class="menu">
		<a <?= menu_actif($lien, 1) ?>href="<?= site_url('boulot/gerer') ?>" title="Gérer mon boulot">Mon boulot</a>
		
		<?php if ($entreprise->historique_publique): ?>
			<a <?= menu_actif($lien, 2) ?>href="<?= site_url('boulot/historique') ?>" title="Historique">Historique</a>
		<?php endif; ?>

		<?php if ($entreprise->syndicats_autorises): ?>
			<?php if ($this->session->userdata('clan_id')[Bouzouk::Clans_TypeSyndicat]): ?>
				<a <?= menu_actif($lien, 3) ?>href="<?= site_url('clans/gerer/syndicat') ?>" title="Syndicat">Syndicat</a>
			<?php elseif ($this->session->userdata('experience') >= $this->bouzouk->config('clans_xp_min_rejoindre_syndicat')): ?>
				<a <?= menu_actif($lien, 4) ?>href="<?= site_url('clans/lister') ?>" title="Syndicat">Syndicat</a>
		<?php endif; ?>
	<?php endif; ?>
	</div>

	<div class="deco onglet<?= menu_actif() ?>">
	</div>
</div>