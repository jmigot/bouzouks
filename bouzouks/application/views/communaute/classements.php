<?php
$this->layout->set_title('Classements du jeu');

function phrase($classement, $mairie = null, $plus_nul = null)
{
	$phrases = array(
		Bouzouk::Classement_Richesse => array(
			" avec un total de {valeur} ! Besoin d'argent ? {sexe:Il|Elle} ne t'en donnera pas !",
			" d'après la police {sexe:il|elle} aurait accumulé {valeur}...Quel escroc...",
			" qui aurait la modique somme de {valeur}, lui manque plus qu'un monocle...",
			" a declaré au percepteur {valeur} (mais {sexe:il|elle} doit en planquer un peu...).",
			" a actuellement {valeur} mais {sexe:lui|elle} on s'en fiche."
		),
		
		Bouzouk::Classement_Experience => array(
			" avec un total de {valeur} ! {sexe:Le doyen|La doyenne} de la ville.",
			" qui cumule {valeur}...Un vrai dinosaure...",
			" qui aurait atteint {valeur}, un peu moyen...",
			" a declaré être à {valeur}, mais {sexe:il|elle} a probablement menti.",
			" a actuellement {valeur}, pas de quoi se vanter, {sexe:le dernier|la dernière} du classement."
		),

		Bouzouk::Classement_Fortune => array(
			" avec une fortune totale de {valeur}, risque de se faire kidnapper.",
			" dont la fortune s'élève à {valeur}...Incroyable...",
			" qui a dépassé les {valeur}, assez médiocre...",
			" pense en être à {valeur} mais {sexe:il est aveuglé|elle est aveuglée} par sa fortune.",
			" a un patrimoine de {valeur} ."
		),

		Bouzouk::Classement_Collection => array(
			" avec un score de {valeur} est {sexe:le plus grand chercheur|la plus grande chercheuse} de trésors de la ville !",
			" et ses {valeur} points a sûrement trahi des amis pour avoir autant d'objets rares...",
			" a un score de {valeur}. Son objet préféré ? Le slip usagé de {maire} !",
			" qui atteint {valeur}, bien mérité vu le nombre d'heures passées sur le marché noir.",
			" monte à {valeur} alors {sexe:qu'il|qu'elle} n'achète que des invendus démodés."
		),

		Bouzouk::Classement_Plouk => array(
			" avec un ratio de {valeur}, imbattable à ce jour !",
			" qui est à {valeur}, {sexe:un|une} vrai pro.",
			" qui a {valeur}, pas mal du tout !",
			" est proche de {valeur} mais {sexe:il|elle} aurait triché plusieurs fois.",
			" a un ratio de {valeur}."
		),

		Bouzouk::Classement_PloukMauvais => array(
			" avec un ratio de {valeur} est {sexe:le joueur de Plouk le plus nul|la joueuse de Plouk la plus nulle} de l'histoire Bouzouk !",
			" est tombé à {valeur}, les statisticiens bouzouks pensaient ça impossible...",
			" a un ratio de {valeur} donc si tu veux augmenter tes stats, joue avec {sexe:lui|elle} !",
			" est proche de {valeur} ({sexe:il|elle} a choisi {plus_nul} comme coach...).",
			" avec un ratio de {valeur} est {sexe:le moins pire des plus nuls|la moins pire des nuls}."
		)		
	);
	
	$texte = $phrases[$classement->type][$classement->position - 1];

	// On remplace la valeur
	if (in_array($classement->type, array(Bouzouk::Classement_Richesse, Bouzouk::Classement_Fortune)))
		$texte = str_replace('{valeur}', struls($classement->valeur), $texte);

	else if ($classement->type == Bouzouk::Classement_Experience)
		$texte = str_replace('{valeur}', '<span class="pourpre">'.(int)$classement->valeur.' xp</span>', $texte);

	else
		$texte = str_replace('{valeur}', '<span class="pourpre">'.$classement->valeur.'</span>', $texte);

	// On remplace le maire
	if ($classement->type == Bouzouk::Classement_Collection)
		$texte = str_replace('{maire}', profil($mairie->id, $mairie->pseudo), $texte);

	// On remplace le plus nul au Plouk
	if ($classement->type == Bouzouk::Classement_PloukMauvais)
		$texte = str_replace('{plus_nul}', profil($plus_nul->joueur_id, $plus_nul->pseudo), $texte);
		
	// On remplace le sexe
	$pattern = '#{sexe:(.+)\|(.+)}#U';
	
	if ($classement->sexe == 'male')
		$texte = preg_replace($pattern, '$1', $texte);

	else
		$texte = preg_replace($pattern, '$2', $texte);

	return $texte;
}

?>

<div id="communaute-classements">
	<!-- Classement richesse(struls) -->
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Les bouzouks les plus riches</h4>
		<div class="bloc_bleu joueurs padd_vertical">
			<p class="mini_bloc">Le classement des bouzouks les plus pingres et avares de la ville !</p>
			<table>
				<?php foreach ($classements[Bouzouk::Classement_Richesse] as $classement): ?>
					<tr>
						<td><p class="tab_espace"># <?= $classement->position ?></p></td>
						<td><p class="tab_espace"><?= profil($classement->joueur_id, $classement->pseudo).phrase($classement) ?></p></td>
						<td><img src="<?= img_url('communaute/'.$classement->evolution.'.png') ?>" alt="Evolution" class="image_evolution"></td>
					</tr>

					<?php if ($classement->position < 5): ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</table>

			<!-- Classement du joueur -->
			<p class="centre margin">
				<?php if (isset($classements_joueur[Bouzouk::Classement_Richesse])): ?>
					<?= profil() ?> : tu es classé <span class="pourpre"># <?= $classements_joueur[Bouzouk::Classement_Richesse][0] ?></span> avec <?= struls($classements_joueur[Bouzouk::Classement_Richesse][1]) ?>
					<img src="<?= img_url('communaute/'.$classements_joueur[Bouzouk::Classement_Richesse][2].'.png') ?>" alt="Evolution" class="image_evolution">
				<?php else: ?>
					Tu n'es pas encore classé. En même temps c'est pas avec <?= struls($this->session->userdata('struls')) ?> que tu peux faire le malin...
				<?php endif; ?>
			</p>
		</div>
	</div>

	<!-- Classement entreprises -->
	<div class="cellule_gris_type2 marge_haut">
		<h4>Les meilleures entreprises</h4>
		<div class="bloc_gris">
			<p class="mini_bloc">Si votre patron est dans ce top5 c'est qu'il vous a déjà arnaqué...</p>
			<table class="entreprises">
				<tr>
					<th>Position</th>
					<th>Nom</th>
					<th>Patron</th>
					<th>Score</th>
					<th></th>
				</tr>

				<?php foreach ($entreprises as $entreprise): ?>
					<tr>
						<td><p class="tab_espace">#<?= $entreprise->position ?></p></td>
						<td><p class="tab_espace"><?= form_prep($entreprise->nom_entreprise) ?></p></td>
						<td><?= profil($entreprise->chef_id, $entreprise->nom_chef) ?></td>
						<td><p class="tab_espace"><?= $entreprise->score ?> points</p></td>
						<td><img src="<?= img_url('communaute/'.$entreprise->evolution.'.png') ?>" alt="Evolution" class="image_evolution"></td>
					</tr>
				<?php endforeach; ?>
			</table>
			<p class="centre margin"><a href="<?= site_url('communaute/lister_entreprises') ?>">Toutes les entreprises</a></p>
		</div>
	</div>

	<!-- Classement expérience -->
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Les plus expérimentés</h4>
		<div class="bloc_bleu joueurs padd_vertical">
			<p class="mini_bloc">Ils n'y connaissent rien les p'tits jeunes de nos jours !</p>
			<table>
				<?php foreach ($classements[Bouzouk::Classement_Experience] as $classement): ?>
				<tr>
						<td><p class="tab_espace"># <?= $classement->position ?></p></td>
						<td><p class="tab_espace"><?= profil($classement->joueur_id, $classement->pseudo).phrase($classement) ?></p></td>
					<td><img src="<?= img_url('communaute/'.$classement->evolution.'.png') ?>" alt="Evolution" class="image_evolution"></td>
					</tr>

					<?php if ($classement->position < 5): ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</table>

			<!-- Classement du joueur -->
			<p class="centre margin">
				<?php if (isset($classements_joueur[Bouzouk::Classement_Richesse])): ?>
					<?= profil() ?> : tu es classé <span class="pourpre"># <?= $classements_joueur[Bouzouk::Classement_Experience][0] ?></span> avec <span class="pourpre"><?= (int)$classements_joueur[Bouzouk::Classement_Experience][1] ?> xp</span>
					<img src="<?= img_url('communaute/'.$classements_joueur[Bouzouk::Classement_Experience][2].'.png') ?>" alt="Evolution" class="image_evolution">
				<?php else: ?>
					Tu n'es pas encore classé, même un pioupiouk a plus d'expérience que toi.
				<?php endif; ?>
			</p>
		</div>
	</div>

	<!-- Classement fortune (struls + maison + marché noir) -->
	<div class="cellule_gris_type2 marge_haut">
		<h4>Classement des bouzouks par fortune</h4>
		<div class="bloc_gris joueurs padd_vertical">
			<p class="mini_bloc">Somme des struls et de la valeur des objets possédés.</p>
				<table>
					<?php foreach ($classements[Bouzouk::Classement_Fortune] as $classement): ?>
						<tr>
							<td><p class="tab_espace"># <?= $classement->position ?></p></td>
							<td><p class="tab_espace"><?= profil($classement->joueur_id, $classement->pseudo).phrase($classement) ?></p></td>
							<td><img src="<?= img_url('communaute/'.$classement->evolution.'.png') ?>" alt="Evolution" class="image_evolution"></td>
						</tr>
						<?php if ($classement->position < 5): ?>
						<?php endif; ?>
					<?php endforeach; ?>
				</table>

			<!-- Classement du joueur -->
			<p class="centre margin">
				<?php if (isset($classements_joueur[Bouzouk::Classement_Fortune])): ?>
					<?= profil() ?> : tu es classé <span class="pourpre"># <?= $classements_joueur[Bouzouk::Classement_Fortune][0] ?></span> avec <?= struls($classements_joueur[Bouzouk::Classement_Fortune][1]) ?>
					<img src="<?= img_url('communaute/'.$classements_joueur[Bouzouk::Classement_Fortune][2].'.png') ?>" alt="Evolution" class="image_evolution">
				<?php else: ?>
					Apparemment tu n'es pas dans les classements, ça m'a l'air de zloter sévère cette histoire...
				<?php endif; ?>
			</p>
		</div>
	</div>

	<!-- Classement collectionneurs -->
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Les meilleurs collectionneurs</h4>
		<div class="bloc_bleu joueurs">
			<p class="mini_bloc">Collection des objets <span class="pourpre">[Rare]</span> et <span class="pourpre">[Très Rare]</span></p>
			<p class="margin-petit centre">La diversité des objets, la quantité détenue et la péremption des objets sont des critères importants.</p>
			<table>
				<?php foreach ($classements[Bouzouk::Classement_Collection] as $classement): ?>
					<tr>
						<td><p class="tab_espace"># <?= $classement->position ?></p></td>
						<td><p class="tab_espace"><?= profil($classement->joueur_id, $classement->pseudo).phrase($classement, $mairie) ?></p></td>
						<td><img src="<?= img_url('communaute/'.$classement->evolution.'.png') ?>" alt="Evolution" class="image_evolution"></td>
					</tr>

					<?php if ($classement->position < 5): ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</table>

			<!-- Classement du joueur -->
			<p class="centre margin">
				<?php if (isset($classements_joueur[Bouzouk::Classement_Collection])): ?>
					<?= profil() ?> : tu es classé <span class="pourpre"># <?= $classements_joueur[Bouzouk::Classement_Collection][0] ?></span> avec <span class="pourpre"><?= $classements_joueur[Bouzouk::Classement_Collection][1] ?> points</span>
					<img src="<?= img_url('communaute/'.$classements_joueur[Bouzouk::Classement_Collection][2].'.png') ?>" alt="Evolution" class="image_evolution">
				<?php else: ?>
					Tu n'es donc pas encore classé ? Indiana, le chien de junior, doit se retourner dans sa niche...
				<?php endif; ?>
			</p>
		</div>
	</div>

	<!-- Classement Plouk (ratio) -->
	<div class="cellule_gris_type2 marge_haut">
		<h4>Classement des meilleurs joueurs au Plouk</h4>
		<div class="bloc_gris joueurs padd_vertical">
			<p class="mini_bloc">Selon leur ratio (gagnées/perdues/égalités).</p>
			<table>
				<?php foreach ($classements[Bouzouk::Classement_Plouk] as $classement): ?>
					<tr>
						<td><p class="tab_espace"># <?= $classement->position ?></p></td>
						<td><p class="tab_espace"><?= profil($classement->joueur_id, $classement->pseudo).phrase($classement, $mairie) ?></p></td>
						<td><img src="<?= img_url('communaute/'.$classement->evolution.'.png') ?>" alt="Evolution" class="image_evolution"></td>
					</tr>
					<?php if ($classement->position < 5): ?>
					<?php else: break; endif; ?>
				<?php endforeach; ?>
			</table>

			<!-- Classement du joueur -->
			<p class="centre margin">
				<?php if (isset($classements_joueur[Bouzouk::Classement_Plouk])): ?>
					<?= profil() ?> : tu es classé <span class="pourpre"># <?= $classements_joueur[Bouzouk::Classement_Plouk][0] ?></span> avec un ratio de <span class="pourpre"><?= $classements_joueur[Bouzouk::Classement_Plouk][1] ?></span>
					<img src="<?= img_url('communaute/'.$classements_joueur[Bouzouk::Classement_Plouk][2].'.png') ?>" alt="Evolution" class="image_evolution">
				<?php else: ?>
					Tu n'es pas encore classé ici. T'inquiète pas, c'est pas grave si t'es nul tant que tu fais mieux que <?= profil($classements[Bouzouk::Classement_PloukMauvais][0]->joueur_id, $classements[Bouzouk::Classement_PloukMauvais][0]->pseudo) ?>.
				<?php endif; ?>
			</p>
		</div>
	</div>

	<!-- Classement Plouk (ratio) -->
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Classement des plus mauvais joueurs au Plouk</h4>
		<div class="bloc_bleu joueurs padd_vertical">
			<p class="mini_bloc">Selon leur ratio (gagnées/perdues/égalités).</p>
			<table>
				<?php foreach ($classements[Bouzouk::Classement_PloukMauvais] as $classement): ?>
					<tr>
						<td><p class="tab_espace"># <?= $classement->position ?></p></td>
						<td><p class="tab_espace"><?= profil($classement->joueur_id, $classement->pseudo).phrase($classement, $mairie, $classements[Bouzouk::Classement_PloukMauvais][0]) ?></p></td>
						<td><img src="<?= img_url('communaute/'.$classement->evolution.'.png') ?>" alt="Evolution" class="image_evolution"></td>
					</tr>
					<?php if ($classement->position < 5): ?>
					<?php else: break; endif; ?>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
</div>
