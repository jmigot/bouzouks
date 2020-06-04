<?php
$this->layout->set_title("Le tchat bouzouks");
$this->layout->ajouter_javascript('irc.js');
?>

<div id="site-tchat">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Tchatter avec les autres bouzouks</h4>
		<div class="bloc_bleu">
			<!-- Tchat Ajax -->
			<?= form_open('http://qwebirc.powanet.org:9090/?channels=bouzouks&amp;prompt=1&amp;uio=d4', array('class' => 'margin centre')) ?>
				<p>
					Pseudo : <input type="text" name="pseudo" maxlength="12" size="12" value="<?= mb_strtolower($this->session->userdata('pseudo')) ?>"><br>
					<input type="submit" value="Lancer le tchat bouzouks">
				</p>
			</form>

			<p class="centre margin">
				<a href="#" class='au_secours'>Au secours ! Le tchat ne marche pas bien chez moi !</a>
			</p>
			
			<!-- Tchat Flash -->
			<?= form_open('http://www.powanet.org/flash2/index.php?autojoin=bouzouks&amp;language=fr&amp;showNickSelection=true&amp;styleURL=css/blue.css&amp;rememberNickname=true', array('class' => 'margin invisible centre')) ?>
				<p>
					Alors essaye celui-là :<br>
					<input type="submit" value="Lancer le tchat bouzouks (Flash)">
				</p>
			</form>

			<p class="highlight">Usurpation d'identité</p>
			
			<p class="margin">
				Pour être certain de parler à un administrateur ou à un modérateur sur le tchat :
			</p>

			<ul>
				<li>Les <span class="rouge">administrateurs</span> sont en haut de la liste des connectés avec un <span class="rouge">@</span> devant leur nom</li>
				<li>Les <span class="bleu">modérateurs</span> sont en haut de la liste des connectés avec un <span class="bleu">%</span> devant leur nom</li>
			</ul>

			<p class="margin">
				Tout autre personne n'est pas identifiée et ne doit pas être considérée comme faisant partie de l'équipe de modération/administration.
				<span class="pourpre">La TeamBouzouk ne te demandera jamais ton mot de passe !</span>
			</p>

			<p class="highlight">Commandes du bot Bouzouk disponibles aux joueurs</p>
			<ul>
				<li><span class="pourpre">!fortune &lt;pseudo&gt;</span> : donne la fortune d'un bouzouk</li>
				<li><span class="pourpre">!invite &lt;pseudo&gt;</span> : envoie une notification d'invite à un bouzouk (fonctionne en privé à <span class="pourpre">Bouzouk</span>)</li>
				<li><span class="pourpre">!bol &lt;1, 2 ou 3&gt;</span> : jouer au bonneteau</li>
			</ul>

			<ul>
				<li><span class="pourpre">!quizz join Mon équipe</span> : créer/rejoindre l'équipe "Mon équipe"</li>
				<li><span class="pourpre">!quizz join Mon équipe/mot_de_passe</span> : créer/rejoindre l'équipe "Mon équipe" avec un mot de passe</li>
				<li><span class="pourpre">!quizz leave</span> : quitter son équipe actuelle</li>
			</ul>
		</div>
	</div>

	<?php $this->load->view('communaute/connectes_tchat'); ?>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Utiliser un logiciel de tchat</h4>
		<div class="bloc_bleu logiciels">
			<p class="fl-gauche margin">
				<a href="http://hexchat.github.io/downloads.html" class="lien"><img src="<?= img_url('site/hexchat.png') ?>" alt="HexChat Logo" width="70"></a>
			</p>

			<p class="margin">
				Nous te conseillons d'utiliser <a href="http://hexchat.github.io/downloads.html">HexChat</a> et de créer un nouveau réseau :<br><br>
				
				<span class="noir">Serveur :</span> <span class="pourpre">irc.powanet.org/6667</span><br>
				<span class="noir">Canal à rejoindre :</span> <span class="pourpre">#bouzouks</span><br>
				<span class="noir">Charset :</span> <span class="pourpre">UTF-8</span>
			</p>
		</div>
	</div>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Charte IRC</h4>
		<div class="bloc_bleu logiciels">
			<p class="margin">
				1. Toute personne se connectant au chan IRC #bouzouks sur irc.powanet.org s'engage à respecter les différentes chartes d'utilisation qui lui permettent d'utiliser les ressources mises à disposition.<br><br>
				2. Les @ (admins) IRC se réservent le droit d'interdire toute connexion à l'IRC Bouzouks.net.<br><br>
				3. L'<a href="<?= site_url('site/charte') ?>">Article 2</a> de la charte du site s'applique aussi au t'chat IRC.<br><br>
				4. D'une manière générale, les operateurs IRC sont responsables du bon fonctionnement du réseau IRC, mais en aucun cas des différents canaux privés.<br><br>
				5. L'envoi en masse de messages, pub pour les élections, vente au Marché Noir, ainsi que la publicite non sollicitée (spam), sont interdits.<br><br>
				6. Tout prosélytisme politique ou religieux n'étant pas en rapport direct avec le Role-Play de bouzouks.net est strictement interdit sur le channel #bouzouks.<br><br>
				7. Toute personne tentant d'usurper l'identité d'un autre joueur de Bouzouks.net sera sanctionnée immédiatement. Une sanction pourra être également appliquée à son compte joueur sur le site.<br><br>
				8. Il est interdit de harceler les utilisateurs par des messages répétés. En cas de nuisances prolongées, les operateurs pourront decider de prendre des mesures contre les harceleurs !<br><br>
				9. Pour tout problème avec un modérateur, l'article 7 de la charte du jeu doit être prise en compte. Vous pourrez contacter un administrateur pour signaler un soucis.<br><br>
				10. Tout manquement à la présente charte sera sanctionné.
			</p>
		</div>
	</div>
</div>
