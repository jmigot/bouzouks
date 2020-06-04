<?php $this->layout->set_title('FAQ - Inscription'); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - Inscription</h4>
		<div class="bloc_bleu">
			<p class="highlight">Comment s'inscrire ?</p>

			<p class="margin">
				L'inscription se déroule en 3 étapes. Dans un premier temps, il te suffit d'aller sur la page &laquo; <i>Inscription</i> &raquo; et de renseigner tous les champs demandés.
				Un mail te sera alors envoyé. Tu dois lire ce mail et aller à l'adresse indiquée pour valider ton inscription.<br><br>

				<span class="italqiue pourpre">ATTENTION : pense à mettre une adresse mail valide : tu devras valider ton inscription via le mail qui te sera envoyé.</span><br><br>

				Quand tu auras validé ton inscription, connecte-toi à ton compte. Suite à cette première connexion, on te demandera de répondre à deux questionnaires contenant des questions
				basiques sur le jeu. Remplis les pour passer à la dernière étape de cette inscription.<br><br>

				Un nouveau formulaire te demandera des informations complémentaires sur ton personnage. Remplis les champs demandés et valide. Tu es désormais un bouzouk, félicitations ! ;-)
			</p>

			<p class="highlight">Quand je veux m'inscrire, on me dit &laquo; Ta connexion internet n'est pas autorisée sur ce serveur &braquo;</p>
			<p class="margin">
				Pour t'inscrire à bouzouks.net tu dois désactiver tout type de serveur proxy, VPN ou autre logiciel qui modifie ton adresse IP.<br>
				Si tu as toujours des soucis, vérifie que tu n'as pas un serveur web chez toi ou un logiciel qui propose une interface web pour la gestion, et désactive le temporairement.<br>
				Enfin si ça ne fonctionne pas, désactive ton pare-feu le temps de t'inscrire.
			</p>

			<p class="highlight">Quand je valide mon inscription, j'ai un message d'erreur...</p>
			<p class="margin">
				C'est que ton lien est erroné ou ta messagerie ne l'affiche pas correctement. Dans ce cas, rends-toi sur la page de
				<a href="<?= site_url('visiteur/inscription_confirmation') ?>">confirmation</a> et tape ton pseudo et le code de validation donné dans le mail.<br><br>

				Il se peut aussi que tu aies déjà validé ton inscription...Quoi qu'il en soit, si le problème persiste, <a href="<?= site_url('site/team') ?>">contacte un administrateur</a>
				en précisant ton pseudo, ton adresse mail et le code qui t'a été donné.
			</p>

			<p class="highlight">Parrainage</p>

			<p class="margin">
				Tu peux parrainer des amis en leur donnant <span class="pourpre">ton pseudo</span>, ce qui te rapportera un <span class="pourpre">objet rare</span> dédié aux parrainages.
				Une fois sur la page d'inscription, ton ami pourra indiquer ton pseudo dans la case <span class="pourpre">Parrain</span>.<br><br>

				Afin de limiter la triche et les multicomptes, les parrainages sont soumis à validation par un modérateur/administrateur avant d'être effectifs. Les parrainages ne peuvent être validés qu'une fois
				que ton ami devient <span class="pourpre">actif</span> (après avoir fait les controuilles). De plus ton compte doit également être toujours actif (un Game Over de l'un ou de l'autre annule le parrainage).
			</p>
		</div>
	</div>
</div>
