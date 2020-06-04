<?php $this->layout->set_title("FAQ - Points d'actions"); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - Points d'actions</h4>
		<div class="bloc_bleu">
			<!-- Retour à la FAQ -->
			<p class="highlight centre"><a href="http://www.bouzouks.net/site/faq">Retour à la FAQ</a></p>

			<p class="margin">
				Trois compétences font les caractéristiques du bouzouk : <span class="pourpre">la force</span>, <span class="pourpre">le charisme</span> et <span class="pourpre">l'intelligence</span>.<br />
				Ces compétences pourront être définies par le joueur au fur et à mesure qu'il gagne des <span class="pourpre">points d'action</span>.<br />
				Les points distribués dans chaque compétence pourront servir de deux manières différentes :
				<ul>
					<li>Être plus productif dans son entreprise, et donc espérer être mieux payé</li>
					<li>Donner des points à son clan</li>
				</ul>
			</p>
			
			<p class="highlight">Comment gagner des points d'action ?</p>
			<p class="margin">
				À chaque point d'expérience gagné, le joueur cumule 1 point d'action. Si le compteur atteint <span class="pourpre"><?= $this->bouzouk->config('joueur_points_action_max') ?> points d'action</span>, il ne pourra pas en gagner d'autres tant qu'il n'aura pas utilisé les précédents.<br><br>

				<span class="pourpre">
					Attention : si tu gagnes de l'expérience et que le total du gain additionné à tes points actuels dépasse <?= $this->bouzouk->config('joueur_points_action_max') ?> points,
					tu gagneras également tous les points au-dessus de <?= $this->bouzouk->config('joueur_points_action_max') ?>. Par contre, une fois que tu as dépassé les <?= $this->bouzouk->config('joueur_points_action_max') ?> points
					en un coup, tu ne peux plus gagner de point tant que tu n'as pas distribué tes points.</span>
			</p>

			<p class="highlight">Comment gagner des points de compétence ?</p>
			<p class="margin">
				Pour gagner des points en force, charisme, ou intelligence, le joueur doit distribuer les points d'action qu'il a gagnés.
			</p>
			<p class="margin">
				Dès qu'il a cumulé 30 points d'action, le joueur peut les répartir au choix dans l'une des 3 compétences : <span class="pourpre">force</span>, <span class="pourpre">charisme</span>, ou <span class="pourpre">intelligence</span>.
			</p>

			<p class="highlight">À quoi servent les points de compétence ?</p>
			<p class="margin">
				Le joueur a deux options pour utiliser ses points de compétence : les garder, ou les donner à son clan. Il a la possibilité d'en garder une partie et de donner l'autre.
			</p>
			
			<p class="highlight">Comment mes points de compétence peuvent-ils augmenter mon salaire ?</p>
			<p class="margin">
				Les points de compétence que le joueur décide de garder lui permettent de rapporter plus de struls à son entreprise. Par conséquent, le <a href="http://www.bouzouks.net/site/faq/jobs">salaire conseillé par la FAQ</a> sera augmenté pour le joueur en question.<br />
			</p>
			<p class="margin">
				Les conditions pour être plus productif dans son entreprise sont les suivantes :
				<ul>
					<li>Avoir 50% en faim et en santé minimum, et 50% en stress maximum</li>
					<li>Avoir cumulé un certain nombre de points dans une compétence donnée, comme détaillé dans le tableau de <a href="<?= site_url('site/faq/jobs') ?>">la page des jobs</a></li>
				</ul>
			</p>
			<p class="margin pourpre italique">
				Revoir à la hausse le salaire du joueur reste un choix du patron.
			</p>
			
			<p class="margin">
				L'expérience requise pour accéder à un job reste celle détaillée sur la <a href="<?= site_url('site/faq/jobs') ?>">FAQ des jobs</a>.
			</p>
			
			<p class="margin">
				Le patron peut lui aussi rapporter plus à son entreprise : s'il a au moins <span class="pourpre">200 points</span> en force, charisme et intelligence, <span class="pourpre">il rapportera à son entreprise le nombre de struls qu'aurait rapporté un employé ayant son expérience et son ancienneté</span>.
			</p>
			
			<p class="highlight">Je suis patron : comment savoir si mon employé produit plus ?</p>
			<p class="margin">
				À côté du pseudo d'un employé, deux indicateurs permettent au patron de savoir si celui-ci remplit les conditions pour produire plus dans l'entreprise :
				<ul>
					<li>Une flèche vers le haut ou vers le bas indique si l'employé est suffisamment en forme ou non</li>
					<li>Un rond barré indique que l'employé n'a pas les compétences nécessaires</li>
				</ul>
			</p>
		</div>
	</div>
</div>
