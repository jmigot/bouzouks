<?php $this->layout->set_title('FAQ - Mendier / Les mendiants'); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - Mendier / Les mendiants</h4>
		<div class="bloc_bleu">
			<!-- Retour à la FAQ -->
			<p class="highlight centre"><a href="<?= site_url('site/faq') ?>">Retour à la FAQ</a></p>
			<p class="margin">
				Plus aucun revenu ? Le maire t'a tout pris ou tu as tout dépensé au bonneteau ? Il te reste encore une solution, compter sur la générosité de tes semblables.<br>
				Pour ce faire, rends-toi à la page <span class="pourpre">mendier</span> et écris un texte qui saura attendrir les passants. Sois original car la concurrence est rude et
				certains bouzouks déjà très riches n'hésitent pas à mendier aussi pour rafler quelques <a href="<?= site_url('site/faq/argent') ?>">struls</a> supplémentaires.
			</p>

			<p class="highlight">À partir de quand peut-on mendier ?</p>
			<p class="margin">
				Quand ta fortune totale est soit en-dessous de <?= struls($this->bouzouk->config('mendiants_fortune_max_mendier')) ?>, soit au-dessus de <?= struls($this->bouzouk->config('mendiants_fortune_min_mendier')) ?> (oui les riches aussi peuvent
				mendier, il n'y a pas de raison que ça sois toujours les mêmes !).
			</p>

			<p class="highlight">Qu'est-ce que la fortune totale ?</p>
			<p class="margin">
				C'est l'argent du bouzouk additionné à la valeur des objets qu'il possède dans sa maison et de ceux qu'il a mis en vente. Le calcul prend donc en compte 3 paramètres :
			</p>

			<ul>
				<li>Les struls en poche</li>
				<li>Les objets dans ta maison</li>
				<li>Les objets que tu as mis en vente au marché noir</li>
			</ul>

			<p class="highlight">Combien peut-on donner de struls à un mendiant ?</p>
			<p class="margin">
				Entre <span class="pourpre">1 et <?= $this->bouzouk->config('mendiants_don_max') ?> struls</span>. Donner <?= struls($this->bouzouk->config('mendiants_don_min_xp')) ?> ou plus peut te permettre d'avoir un bonus d'xp mais attention si tu les donnes à un riche c'est le contraire
				qui arrive ! Tu perdras des xp ! Donc fais attention à te renseigner sur la personne avec qui tu veux être généreux.<br><br>

				<span class="pourpre">Les gains/pertes d'expérience ne fonctionnent qu'une fois par jour (bah oui ça serait un peu facile sinon...tsss...).</span>
			</p>

			<p class="highlight">Comment peut-on savoir si un mendiant est riche ou pauvre ?</p>
			<p class="margin">
				On ne peut pas. Mais certains indices pourront te mettre la puce à la trompe : si le mendiant est patron d'entreprise, maire de la ville, ou que son expérience est très élevée
				(qui dit expérience élevée dit gros salaire), alors il y a des chances que la personne soit plutôt riche que pauvre.
			</p>
		</div>
	</div>
</div>
