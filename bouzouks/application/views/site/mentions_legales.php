<?php $this->layout->set_title('Mentions légales') ?>

<div id="site-mentions_legales">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Mentions légales pour Bouzouks.net</h4>
		<div class="bloc_bleu">
			<p class="margin">
				<b>BOUZOUKS.NET</b> (TeamBouzouk)<br>
				Email :
				<?php if ($this->session->userdata('connecte')): ?>
					<?= $this->bouzouk->config('email_from') ?>
				<?php else: ?>
					<img src="<?= img_url('teambouzouk.png') ?>" alt="L'adresse de la TeamBouzouk">
				<?php endif ?>
			</p>

			<p class="margin">
				<b>Directeur de publication</b><br>
				Marius Gadille
			</p>

			<p class="margin">
				<b>Hébergement</b><br>

				<b>OVH</b><br>
				SAS au capital de 10 000 000 €<br>
				RCS Roubaix – Tourcoing 424 761 419 00045<br>
				Code APE 6202A<br>
				N° TVA : FR 22 424 761 419<br>
				Siège social : 2 rue Kellermann - 59100 Roubaix - France.
			</p>

			<p class="margin">
				<b>Protection et traitement de données à caractère personnel</b><br>

				L'utilisation de nos services nécessite de fournir sur notre site des informations personnelles. En nous les fournissant, vous acceptez que celles-ci soient traitées afin de répondre facilement et clairement à vos besoins et pour vous permettre de jouer sur bouzouks.net.<br><br>
				Conformément aux articles 39 et suivants de la loi n° 78-17 du 6 janvier 1978 relative à l'informatique, aux fichiers et aux libertés, toute personne a un droit d'accès aux données qui la concerne. De plus, elle a un droit de rectification ou suppression des informations la concernant, en rédigeant un courriel à bouzouks.net@gmail.com. « Toute personne peut également, pour des motifs légitimes, s'opposer au traitement des données la concernant ».
			</p>
				
			<p class="margin">
				<b>Cookies</b><br>
				Notre site http://www.bouzouks.net souhaite implanter un cookie dans votre ordinateur.<br>
				Ce cookie enregistre un identifiant de session aléatoire anonyme et ne contient aucune donnée personnelle.<br><br>
				Nous attirons votre attention sur le fait que tous les services de ce site requièrent que l'utilisateur accepte les cookies. Si le navigateur est configuré pour les refuser, l'accès à ces services peut se révéler altéré, voire impossible.
			</p>

			<p class="margin">
				<b>Propriété littéraire et artistique</b><br>
				Selon l'article L.122-4 du code de la propriété intellectuelle, « Toute représentation ou reproduction intégrale ou partielle faite sans le consentement de l'auteur ou de ses ayants droit ou ayants cause est illicite. Il en est de même pour la traduction, l'adaptation ou la transformation, l'arrangement ou la reproduction par un art ou un procédé quelconque. »<br><br>

				La structure générale, ainsi que les logiciels, textes, images animées ou fixes, sons, savoir-faire, dessins, graphismes, les noms de produits, les marques citées, les logos ou tout autre élément composant le Site sont la propriété exclusive de la TeamBouzouk et/ou ont fait l'objet d'une licence ou d'une autorisation expresse de leur propriétaire aux fins de leur représentation. Ils sont protégés par les lois françaises et internationales relatives au droit d'auteur, au droit des marques et à la propriété intellectuelle.<br><br>
				
				Toute reproduction et/ou représentation totale ou partielle du Site Internet par quelque procédé que ce soit, sans l'autorisation expresse, écrite et préalable de leur propriétaire, est interdite, et est constitutive de contrefaçon. Il en est de même des bases de données figurant sur le Site Internet, qui sont protégées par les dispositions de la loi du 1er juillet 1998 portant transposition dans le Code de la propriété intellectuelle de la directive européenne du 11 mars 1996 relative à la protection juridique des bases de données.<br><br>

				La mise en place d'un lien hypertexte, même profond pointant vers l'adresse http://www.bouzouks.net est autorisée mis à part pour tout site web contenant des informations à caractère pornographique, xénophobe, politique, religieux, ainsi que tout site contenant des caractères allant à l'encontre des bonnes mœurs et de l'ordre public.
				L'éditeur se réserve le droit de demander la suppression d'un lien qu'il estime non conforme à l'objet du site « http://www.bouzouks.net ».
			</p>

			<p class="margin">
				<b>Modification conditions d'utilisation</b><br>
				La TeamBouzouk se réserve la possibilité de modifier, à tout moment et sans préavis, les présentes mentions  afin de les adapter aux évolutions du site et/ou de son exploitation, ainsi que la charte de jeu. L'utilisateur s'engage donc à la consulter régulièrement.
			</p>

			<p class="margin">Dernière mise à jour le : 06/07/2013</p>
		</div>
	</div>
 </div>
