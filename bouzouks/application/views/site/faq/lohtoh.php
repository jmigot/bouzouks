<?php $this->layout->set_title('FAQ - Lohtoh'); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - Lohtoh</h4>
		<div class="bloc_bleu">
			<!-- Retour à la FAQ -->
			<p class="highlight centre"><a href="<?= site_url('site/faq') ?>">Retour à la FAQ</a></p>

			<p class="margin">
				Les bouzouks pourront jouer à la loterie pour tenter de gagner des struls. Une combinaison aléatoire sera tirée parmi toutes les combinaisons possibles.<br>
				Si un ou plusieurs bouzouks avaient joué cette combinaison, ils se partagent la cagnotte cumulée jusque là.<br><br>

				<span class="pourpre">Attention : le maire décide du pourcentage des impôts sur les gains du lohtoh.</span>
			</p>

			<p class="highlight">La numérotation bouzouk</p>
			<p class="margin">
				La numérotation bouzouk comporte 10 chiffres : <span class="pourpre">GNEE=0 | KAH=1 | ZIG=2 | STO=3 | BLAZ=4 | DRU=5 | GOZ=6 | POO=7 | BNZ=8 | GLAP=9</span>
			</p>

			<p class="highlight">Combien de fois peut-on acheter un ticket ?</p>
			<p class="margin">
				On peut acheter jusqu'à <?= $this->bouzouk->config('jeux_max_tickets_lohtoh') ?> tickets par jour maximum, mais un ticket coûte <?= struls($this->bouzouk->config('jeux_prix_ticket_lohtoh')) ?>. Évidemment, plus tu achèteras de tickets, plus tu auras
				de chances de gagner.
			</p>

			<p class="highlight">Combien y a-t-il de tirages ?</p>
			<p class="margin">
				Il y a un tirage par jour, lors de la maintenance du site, à <span class="pourpre">une heure aléatoire</span>.
			</p>

			<p class="highlight">Comment sont tirés les numéros ?</p>
			<p class="margin">
				Les numéros sont tirés chacun aléatoirement un par un. Il est ainsi possible d'avoir plusieurs fois le même numéro. Il faut deviner les numéros dans l'ordre de gauche à droite, si tu as choisis
				<span class="pourpre">KAH - ZIG - STO</span> et que le tirage est <span class="pourpre">STO - ZIG - KAH</span> (à l'envers), tu as perdu. De même pour des numéros mélangés.
			</p>

			<p class="highlight">Combien peut-on gagner ?</p>
			<p class="margin">
				La somme mise en jeu varie en fonction du nombre de tickets achetés par chaque bouzouk depuis le dernier tirage gagnant et du prix du ticket.
				Si personne ne gagne pendant un certain temps, la cagnotte du lohtoh grimpe de plus en plus.
			</p>
		</div>
	</div>
</div>
