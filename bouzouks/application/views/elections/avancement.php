<p class="retour"><a href="<?= site_url('communaute/classements_elections') ?>">Voir les résultats des élections précédentes</a></p>

<?php 
	// ---------- Hook clans ----------
	// Tag MLBiste (MLB)
	if (($tag_mlbiste = $this->bouzouk->clans_tag_mlbiste()) != null)
		$this->load->view('clans/tag_mlb', array('tag_mlbiste' => $tag_mlbiste));

	if ($this->bouzouk->clans_grosse_manif_syndicale() != null)
		$this->load->view('clans/grosse_manif_syndicale');
?>

<div id="elections-avancement">
	<div class="centre margin avancement">
		<p class="inline-block pourpre tour_0 <?= $tour == Bouzouk::Elections_Candidater ? 'actif' : 'inactif' ?>">Candidatures</p>
		<p class="inline-block pourpre tour_1 <?= $tour == Bouzouk::Elections_Tour1 ? 'actif' : 'inactif' ?>">1er tour</p>
		<p class="inline-block pourpre tour_2 <?= $tour == Bouzouk::Elections_Tour2 ? 'actif' : 'inactif' ?>">2ème tour</p>
		<p class="inline-block pourpre tour_3 <?= $tour == Bouzouk::Elections_Tour3 ? 'actif' : 'inactif' ?>">3ème tour</p>
		<div class="dates">
			<?php
				$duree_candidatures = $this->bouzouk->config('elections_duree_candidatures');
				$duree_tour_1 = $this->bouzouk->config('elections_duree_tour_1');
				$duree_tour_2 = $this->bouzouk->config('elections_duree_tour_2');
				$duree_tour_3 = $this->bouzouk->config('elections_duree_tour_3');
			?>
			<p class="date_1 pourpre"><?= jour_mois($date_debut) ?></p>
			<p class="date_2 pourpre"><?= jour_mois($date_debut.'+'.$duree_candidatures.' DAY') ?></p>
			<p class="date_3 pourpre"><?= jour_mois($date_debut.'+'.($duree_candidatures + $duree_tour_1).' DAY') ?></p>
			<p class="date_4 pourpre"><?= jour_mois($date_debut.'+'.($duree_candidatures + $duree_tour_1 + $duree_tour_2).' DAY') ?></p>
			<p class="date_5 pourpre"><?= jour_mois($date_debut.'+'.($duree_candidatures + $duree_tour_1 + $duree_tour_2 + $duree_tour_3).' DAY') ?></p>
		</div>
	</div>
</div>
