<?php $this->layout->set_title('FAQ - Maire et mairie'); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - Le Maire et la Mairie</h4>
		<div class="bloc_bleu">
			<!-- Retour à la FAQ -->
			<p class="highlight centre"><a href="<?= site_url('site/faq') ?>">Retour à la FAQ</a></p>

			<p class="highlight">Comment devenir maire ?</p>
			<p class="margin">
				Pour devenir maire, tu dois avoir au moins <span class="pourpre"><?= $this->bouzouk->config('elections_xp_candidater') ?> xp</span>, t'être présenté aux <a href="<?= site_url('site/faq/elections') ?>">élections</a> et avoir été élu par la majorité des bouzouks au terme des 3 tours.
			</p>

			<p class="highlight">Comment se présenter aux élections ?</p>
			<p class="margin">
				Les bouzouks peuvent se présenter pendant les <span class="pourpre"><?= $this->bouzouk->config('elections_duree_candidatures') ?> jours</span> suivant l'élection du dernier maire. Voir <a href="<?= site_url('site/faq/elections') ?>">élections</a> pour
				plus d'informazouilles.
			</p>

			<p class="highlight">Combien de temps dure le mandat du maire ?</p>
			<p class="margin">
			<?php
				$duree = $this->bouzouk->config('elections_duree_candidatures') +
						 $this->bouzouk->config('elections_duree_tour_1') +
						 $this->bouzouk->config('elections_duree_tour_2') +
						 $this->bouzouk->config('elections_duree_tour_3');
			?>
				Le maire se tient à la tête de la mairie pendant <span class="pourpre"><?= $duree ?> jours</span> consécutifs pendant lesquels se déroulent les élections pour le maire suivant.
			</p>

			<p class="highlight">Pourquoi devenir maire ?</p>
			<p class="margin">
				Le maire a pour rôle de gérer l'argent de la mairie (qui permet, rappelons-le, d'acheter les objets des entreprises et remplir les magasins). Il a donc la possibilité de :
			</p>

			<ul>
				<li>Se verser un salaire (<?= struls($this->bouzouk->config('mairie_salaire_max_maire')) ?>/jour</span> maximum)</li>
				<li>Décider du taux des impôts (employés et entreprises)</li>
				<li>Décider du montant des allocations chômage (entre <span class="pourpre">0 strul</span> et <?= struls($this->bouzouk->config('mairie_aide_chomage_max')) ?>)</li>
				<li>Décider du montant de l'aide à la création d'entreprise (entre <?= struls($this->bouzouk->config('mairie_aide_entreprise_min')) ?> et <?= struls($this->bouzouk->config('mairie_aide_entreprise_max')) ?>)</li>
				<li>Envoyer une taxe surprise aux bouzouks (toutes les <span class="pourpre"><?= $this->bouzouk->config('mairie_intervalle_taxes') ?>h</span> minimum, <span class="pourpre"><?= $this->bouzouk->config('mairie_taxe_max') ?> %</span> maximum)</li>
				<li>Tricher aux élections en cours</li>
				<li>Redistribuer l'argent de la mairie aux bouzouks</li>
				<li>Donner de l'argent à un bouzouk en particulier (<?= struls($this->bouzouk->config('mairie_don_max_bouzouk')) ?> maximum, plusieurs fois par mandat)</li>
			</ul>

			<p class="highlight">À quoi sert le suppléant du maire ? Comment devient-on suppléant ?</p>
			<p class="margin">
				Le suppléant du maire est celui qui arrive second aux élections. Il prend la place du maire au cas où celui-ci ne peut plus assurer ses fonctions. Le maire est donc
				remplacé automatiquement dans les cas suivants :
			</p>

			<ul>
				<li>Le maire passe en <a href="<?= site_url('site/faq/pause') ?>">pause</a></li>
				<li>Le maire est à <a href="<?= site_url('site/faq/asile') ?>">l'asile</a></li>
				<li>Le maire est en <a href="<?= site_url('site/faq/game_over') ?>">game over</a></li>
				<li>Le maire est <span class="pourpre">banni</span></li>
			</ul>

			<p>
				Si le suppléant est à son tour dans l'une de ces situations, c'est <span class="pourpre">J.F Sébastien</span> qui prendra la relève.
			</p>

			<p class="margin pourpre italique">À noter...Le maire ne doit pas oublier que les paparazzis rôdent et qu'une de ses mesures peut le rendre très impopulaire !</p>
		</div>
	</div>
</div>
