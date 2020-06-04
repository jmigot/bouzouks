 <?php $this->layout->set_title('FAQ - Argent'); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - Argent (le Strul)</h4>
		<div class="bloc_bleu">
			<!-- Retour à la FAQ -->
			<p class="highlight centre"><a href="<?= site_url('site/faq') ?>">Retour à la FAQ</a></p>
			<p class="margin">
				Le strul est l'unité monétaire de la ville et l'objet de toutes les convoitises. S'il n'a plus d'argent, le joueur ne pourra plus assurer le bon niveau de ses statistiques
				et risque donc <a href="<?= site_url('site/faq/game_over') ?>">le Game Over</a>.<br><br>

				Avoir des struls te permettra de manger, de te soigner ou encore de te distraire.<br><br>
			</p>

			<p class="highlight">Comment gagner de l'argent ?</p>
			<p class="margin">
				Il existe plusieurs façons de gagner des struls. La plus simple est sans doute de trouver un emploi. Le premier jour de ton inscription tu commenceras le jeu avec
				<?= struls($this->bouzouk->config('joueur_struls_depart')) ?> mais une bonne note à un de tes <a href="<?= site_url('site/faq/controuilles') ?>">controuilles</a> peut vite faire monter la somme de départ du jeu ;-)<br><br>

				Les autres possibilités de gagner de l'argent sont de jouer au <a href="<?= site_url('site/faq/lohtoh') ?>">Lohtoh</a> ou au
				<a href="<?= site_url('site/faq/bonneteau') ?>">Bonneteau</a> ou de revendre tes objets à bon prix au marché noir.
			</p>

			<p class="highlight">Pourquoi ma fortune n'est pas identique à mes struls ?</p>
			<p class="margin">
				La fortune représente la valeur totale des struls et des objets du joueur, ainsi le calcul de la fortune totale d'un joueur prend en compte <span class="pourpre">les struls</span>,
				<span class="pourpre">les objets de la maison</span> et <span class="pourpre">les objets en vente au marché noir</span>.
			</p>
			
			<p class="highlight">Quel sont les jobs rapportant le plus d'argent ?</p>
			<p class="margin">
				Pour la plupart des jobs, ton salaire augmentera en fonction de ton expérience (XP) mais il existe des exceptions à la règle :
			</p>

			<ul>
				<li>Être chef d'entreprise peut te rendre très riche si tu gères bien ta société</li>
				<li>Si tu deviens maire de la ville, tu peux aussi gagner énormément d'argent si tu n'as aucun scrupule à prendre l'argent gagné grâce aux impôts que payent les bouzouks</li>
			</ul>

			<p class="highlight">A quoi sert la page <a href="<?= site_url('plus_de_struls') ?>">Plus de struls !</a> ?</p>
			<p class="margin">
				Si tu désires évoluer plus rapidement dans le jeu tu peux utiliser le système <a href="<?= site_url('plus_de_struls') ?>">Plus de struls !</a> qui permet
				d'obtenir des struls directement. Ce service est payant et permet aussi à la teambouzouk de payer le serveur du jeu et les différentes dépenses qu'un tel site engendre.
			</p>
		</div>
	</div>
</div>