<?php $this->layout->set_title('FAQ - Le plouk'); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - Le plouk</h4>
		<div class="bloc_bleu">
			<!-- Retour à la FAQ -->
			<p class="highlight centre"><a href="<?= site_url('site/faq') ?>">Retour à la FAQ</a></p>

			<p class="margin">
				Jeu de combat éléctoral par cartes. Le but du jeu est d'être en tête des sondages médiatiques, soit en arrivant à <span class="pourpre">100%</span> au cours de la partie soit
				en ayant le pourcentage le plus fort à la fin du nombre de tours de la partie.<br><br>

				A tour de rôle, les joueurs peuvent soit jouer une carte, soit se défausser d'une carte (la carte est jetée sans être jouée). La carte jouée ou jetée sera alors remplacée par une
				nouvelle. Un joueur ne peut pas avoir deux fois la même carte dans la main et ne peut avoir qu'une seule carte <span class="pourpre">&laquo; Rejouer &raquo;</span> en même temps.<br><br>

				Pour pouvoir jouer une carte, il faut avoir assez de médiatisation ou de partisans (indiqué en bas à droite de la carte dans une petite bulle). Les cartes nécessitant
				de la médiatisation sont <span class="bleu">bleues</span> et celles nécessitant des partisans sont <span class="vert">vertes</span>.<br><br>

				<span class="pourpre">A chaque fois qu'un joueur a fini de jouer, il gagne automatiquement de la médiatisation et des partisans (selon le choix fait lors de la création de la partie).</span><br><br>

				Le charisme sert à "absorber" les attaques de l'adversaire. Quand le charisme est à <span class="pourpre">0%</span>, les attaques de charismes s'appliquent directement au
				pourcentage des sondages. Par exemple, si un joueur a <span class="pourpre">5 de charisme</span> et qu'il prend <span class="pourpre">-20 de charisme</span>, son charisme descend
				à <span class="pourpre">0</span> et les <span class="pourpre">15 restants</span> sont déduits des sondages, il perd donc <span class="pourpre">-15% de sondages</span> en plus.
			</p>

			<p class="highlight">Explications de l'interface</p>
			<p class="margin">
				<img src="<?= img_url('plouk/faq.png') ?>" width="680" alt="Règles du jeu">
			</p>

			<p class="highlight">Créer une partie</p>
			<p class="margin">
				Le créateur choisit le nombre de tours, le chrono, le charisme de départ et les gains automatiques de médiatisation/partisans à chaque tour. Il peut choisir de mettre un mot de
				passe à sa partie pour empêcher un joueur ou un spectateur de rentrer sur la partie sans le mot de passe. Le créateur peut également interdire aux spectateurs de parler
				sur la machine à café.
			</p>

			<ul>
				<li>On peut jouer une seule partie à la fois</li>
				<li>Il n'est pas obligatoire de parier un objet</li>
				<li>Les objets pariés doivent avoir au moins <span class="pourpre"><?= pluriel($this->bouzouk->config('plouk_peremption_min'), 'jour') ?></span> de péremption</li>
				<li>Il est possible de parier des objets illimités</li>
				<li>Les administrateurs et certains modérateurs peuvent suivre une partie sans le mot de passe</li>
			</ul>

			<p class="highlight">Rejoindre une partie</p>
			<p class="margin">
				Un joueur qui n'a aucune partie en cours peut rejoindre une partie créée. Si le créateur a parié un objet, celui qui veut le rejoindre doit posséder le même objet selon les conditions
				décrites au paragraphe <span class="pourpre italique">Créer une partie</span>.<br>
				Si un mot de passe est requis, il sera demandé après avoir cliqué sur <span class="pourpre">Rejoindre</span>.<br><br>

				En cliquant sur <span class="pourpre">Rejoindre</span>, tu n'as pas l'exclusivité de la partie donc quelqu'un peut rejoindre la partie avant que tu ne valides.
			</p>

			<p class="highlight">Jouer une partie</p>
			<p class="margin">
				Au cours de la partie, un joueur peut décider de <span class="pourpre">Déclarer forfait</span>, c'est-à-dire abandonner la partie au profit de son adversaire. Dans ce cas
				l'adversaire obtient <span class="pourpre">100% des sondages</span> et il est déclaré vainqueur. Les objets éventuellement pariés lui reviennent de droit.<br><br>

				Dans le cas où un adversaire est absent de la partie pendant un certain temps, l'autre joueur peut cliquer sur <span class="pourpre">Adversaire asbent</span>, ce qui a pour effet
				de déclarer le joueur comme vainqueur au même titre que si l'adversaire avait abandonné.
			</p>

			<ul>
				<li>Si la partie n'a pas encore commencée, l'adversaire doit être absent depuis <span class="pourpre">30 secondes</span></li>
				<li>Si la partie est en cours, l'adversaire doit être absent depuis <span class="pourpre">1 minute</span></li>
			</ul>

			<p class="margin">Si tu cliques sur <span class="pourpre">Adversaire absent</span> et que le temps minimum d'absence n'est pas encore écoulé, l'adversaire <span class="pourpre">sera considéré comme encore présent</span>.</p>

			<p class="highlight">Suivre une partie</p>
			<p class="margin">
				Il est possible de suivre une partie en tant que spectateur. On ne peut pas voir les cartes des joueurs, mais on voit les cartes jouées. Les places de spectateurs sont
				limitées à <span class="pourpre"><?= $this->bouzouk->config('plouk_max_spectateurs') ?> par partie</span>, joueurs inclus. Un joueur ne peut pas être sur plus de
				<span class="pourpre"><?= pluriel($this->bouzouk->config('plouk_nb_suivies_max'), 'partie') ?></span> en même temps.
			</p>

			<p class="highlight">Nettoyage des parties</p>
			<p class="margin">
				Afin d'éviter un trop grand nombre de parties abandonnées, un nettoyage des parties est régulièrement effectué. Ainsi sont automatiquement supprimées les parties qui respectent
				une des conditions suivantes :
			</p>

			<ul>
				<li>Les parties créées depuis plus de <span class="pourpre"><?= pluriel($this->bouzouk->config('plouk_delai_suppression_proposee'), 'minute') ?></span> sans adversaire</li>
				<li>Les parties en attente depuis plus de <span class="pourpre"><?= pluriel($this->bouzouk->config('plouk_delai_suppression_attente'), 'minute') ?></span></li>
				<li>Les parties en cours sans connecté depuis plus de <span class="pourpre"><?= pluriel($this->bouzouk->config('plouk_delai_suppression_en_cours'), 'minute') ?></span></li>
			</ul>

			<p class="highlight">Classements</p>
			<p class="margin">
				La page des classement regroupe le classement des meilleurs joueurs au plouk ainsi que celui des plus mauvais. Pour être classé il faut avoir joué un certain nombre de parties.
				Le score prend en compte les victoires, les défaites et les égalités.
			</p>
		</div>
	</div>
</div> 
