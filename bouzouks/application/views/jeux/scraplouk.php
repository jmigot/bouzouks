<?php
$this->layout->set_title('Le Scraplouk');
$this->layout->ajouter_javascript('scraplouk.js');
$this->layout->ajouter_javascript_externe('http://webplayer.unity3d.com/download_webplayer-3.x/3.0/uo/UnityObject.js');
?>

<div id="jeux-scraplouk">
	<div class="infos">
			<p class="haut bloc_gris">Le scraplouk est un puzzle-game, réalisé par l'équipe <a href="http://www.gaddygames.com" title="Site de Jeux pour smartphones">Gaddy Games</a>, dans lequel vous devez aligner 3 objets semblables ou plus, à la verticale ou à l'horizontale avant que le tableau ne se remplisse.<br><br>

			- 25 niveaux de jeu<br><br>
			- 5 environnements différents avec les bouzo-shops comme thème<br><br>
			- 6 musiques composées pour ce jeu<br>par Lime Kain.
			</p>
			<p class="bas bloc_gris">
				Le jeu est développé spécialement pour une utilisation sur android. Pour de meilleures conditions de jeu, n'hésitez pas à vous rendre sur
				le <a href="https://play.google.com/store/apps/details?id=com.GaddyGames.Scraplouk">Google Play</a> pour télécharger le jeu sur votre téléphone.
				<br><br>
				<a href="https://play.google.com/store/apps/details?id=com.GaddyGames.Scraplouk">
					<img src="<?= img_url('scraplouk/store_android.png') ?>" alt="Téléchager le jeu sur Android Marcket">
				</a>
			</p>

		<div class="contenu">
			<div id="unityPlayer">
				<div class="missing">
					<a href="http://unity3d.com/webplayer/" title="Unity Web Player. Install now!">
						<img alt="Unity Web Player. Install now!" src="http://webplayer.unity3d.com/installation/getunity.png" width="193" height="63" />
					</a>
				</div>
			</div>
		</div>

	</div>
 
	<div class="cellule_bleu_type2">
		<h4>Développé par Gaddy Games</h4>
		<div class="bloc_bleu">
			<p class="mini_bloc  pourpre">
				Retrouvez le jeu sur votre smartphone !
			</p>
			<p class="margin">
				La TeamBouzouk n'est pas à l'origine du projet. Le jeu a été réalisé par le studio de développement <a href="http://www.gaddygames.com" title="Site de Jeux pour smartphones">Gaddy Games</a> en partenariat avec Bouzouks.net.
				Le jeu utilise la technologie <a href="http://unity3d.com/unity/">Unity Web Player</a>.
			</p>
			<p class="centre margin">
				<a href="https://play.google.com/store/apps/developer?id=Gaddy+Games">
					<img src="<?= img_url('scraplouk/ban_gaddygames.png') ?>" alt="Web and mobile game developers">
				</a>
			</p>
		</div>
	</div>
</div>
