<?php

$this->layout->set_title('FAQ - Les clans');
$this->layout->ajouter_javascript('faq.js');

// On récupère les actions possibles pour le clan
$query = $this->db->select('id, clan_type, nom, description, effet, cout, image, nb_membres_min, nb_allies_min, nb_membres_allies_min, cout_par_allie')
				  ->from('clans_actions')
				  ->order_by('clan_type')
				  ->order_by('cout')
				  ->get();
$actions = $query->result();

$clan_types = array(
	Bouzouk::Clans_TypeSyndicat       => 'Syndicats',
	Bouzouk::Clans_TypePartiPolitique => 'Partis Politiques',
	Bouzouk::Clans_TypeOrganisation   => 'Organisations',
	Bouzouk::Clans_TypeCDBM           => 'Club des Bonnes Moeurs',
	Bouzouk::Clans_TypeStruleone      => 'Famille Struleone',
	Bouzouk::Clans_TypeSDS            => 'Secte du Schnibble',
	Bouzouk::Clans_TypeMLB            => 'Mouvement Libérateur Bouzouk',
);

?>

<div id="site-faq-clans">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - Les clans</h4>
		<div class="bloc_bleu">
			<!-- Retour à la FAQ -->
			<p class="highlight centre"><a href="http://www.bouzouks.net/site/faq">Retour à la FAQ</a></p>

			<p class="margin">
				Les clans regroupent plusieurs bouzouks dans une même idéologie. Ils interagissent directement sur le déroulement du jeu et sur les événements qu'il s'y passe.
				Ceux-ci sont regroupés en 3 catégories avec des objectifs différents pour chacun :
		 	</p>

			<ul>
				<li>Les <span class="pourpre">syndicats</span> sont liés à une entreprise et servent à défendre les salariés contre le patronat.</li>
				<li>Les <span class="pourpre">partis politiques</span> se concentrent principalement sur la mairie et les élections.</li>
				<li>Les <span class="pourpre">organisations</span> agissent sur différents secteurs de la ville et sur les citoyens.</li>
			</ul>

			<p class="margin">Il existe 3 types de clans pour chaque catégorie :</p>

			<ul>
				<li><span class="pourpre">Ouvert :</span> tous les bouzouks peuvent intégrer le clan directement sans demande préalable.</li>
				<li><span class="pourpre">Fermé :</span> les joueurs doivent postuler pour pouvoir intégrer le clan.</li>
				<li><span class="pourpre">Caché :</span> le clan est invisible pour les non-membres, il faut connaître le nom du clan pour postuler.</li>
			</ul>

			<p class="margin">
				L'interaction sur le jeu se fait à l'aide d'une liste d'actions spécifiques à chaque catégorie de clan.<br>
				Cette liste d'actions possibles est proposée pour chaque clan qui pourra les réaliser en utilisant des <a href="<?= site_url('site/faq/points_actions') ?>">points d'action</a>.<br><br>

				Il existe des <span class="pourpre">actions directes</span> qui seront effectuées à l'instant et des <span class="pourpre">actions différées</span> qui seront soumises aux enchères et
				qui n'auront lieu que lors de la maintenance suivante. Le clan qui mise le plus de points est celui qui pourra réaliser une action.<br>
				Les clans d'une même catégorie sont en concurrence directe. Ainsi il peut y avoir 3 actions par jour (syndicats/partis politiques/organisations).
			</p>

			<ul>
				<li>Pour créer un syndicat il vous faut : <?= struls($this->bouzouk->config('clans_struls_min_creer_syndicat')) ?> et <span class="pourpre"><?= $this->bouzouk->config('clans_xp_min_creer_syndicat') ?> xp</span></li>
				<li>Pour créer un parti politique il vous faut  : <?= struls($this->bouzouk->config('clans_struls_min_creer_parti_politique')) ?> et <span class="pourpre"><?= $this->bouzouk->config('clans_xp_min_creer_parti_politique') ?> xp</span></li>
				<li>Pour créer une organisation il vous faut  : <?= struls($this->bouzouk->config('clans_struls_min_creer_organisation')) ?> et <span class="pourpre"><?= $this->bouzouk->config('clans_xp_min_creer_organisation') ?> xp</span></li>
			</ul>

			<ul>
				<li>Expérience minimum pour rejoindre un syndicat : <span class="pourpre"><?= $this->bouzouk->config('clans_xp_min_rejoindre_syndicat') ?> xp</span></li>
				<li>Expérience minimum pour rejoindre un parti politique : <span class="pourpre"><?= $this->bouzouk->config('clans_xp_min_rejoindre_parti_politique') ?> xp</span></li>
				<li>Expérience minimum pour rejoindre une organisation : <span class="pourpre"><?= $this->bouzouk->config('clans_xp_min_rejoindre_organisation') ?> xp</span></li>
			</ul>

			<p class="highlight">Force / Charisme / Intelligence</p>
			<p class="margin">Les points d'action sont répartis entre ces 3 stats qui déterminent les capacités de ton personnage. Chacune de ces stats correspond à une catégorie de clan :</p>
			
			<ul>
				<li>Les syndicats ont besoin de la <span class="pourpre">force</span> ouvrière pour lutter contre le patronat.</li>
				<li>Les partis politiques ont besoin de <span class="pourpre">charisme</span> pour prendre le pouvoir aux élections.</li>
				<li>Les organisations utilisent leur <span class="pourpre">intelligence</span> pour mener à bien leurs différents projets dans la ville.</li>
			</ul>

			<p class="margin">Les membres des clans doivent donc bien répartir leurs points d'actions parmis ces 3 stats pour aider au mieux leur clan.</p>

			<!-- Liste des actions réalisables -->
			<?php foreach (array_keys($clan_types) as $clan_type): ?>
				<p class="margin"></p>
				<p class="hr"></p>
				<p class="highlight centre padding">Actions disponibles pour : <span class="pourpre"><?= $clan_types[$clan_type] ?></span></p>

				<div class="centre actions">
					<!-- Liste en images -->
					<?php foreach ($actions as $action): ?>
						<?php if ($action->clan_type != $clan_type) continue; ?>

						<div class="action fl-gauche" id="action_image_<?= $action->id ?>" style="background: url(<?= img_url('clans/actions/'.$action->image) ?>); background-size: 100% 100%;">
							<div class="infos possible">
								<p class="nom"><?= form_prep($action->nom) ?></p>
								<p class="cout"><?= $action->cout ?> p.a. <?= $action->effet == Bouzouk::Clans_EffetDiffere ? 'mini' : '' ?></p>
							</div>
						</div>
					<?php endforeach; ?>

					<p class="clearfloat"></p>

					<!-- Détails cachés -->
					<?php foreach ($actions as $action): ?>
						<?php if ($action->clan_type != $clan_type) continue; ?>
						
						<div class="details invisible" id="action_details_<?= $action->id ?>">
							<div>
								<p class="description highlight inline-block padding">
									<b><?= form_prep($action->nom) ?></b> : <?= $action->description ?>
									
									<?php if ($action->nb_membres_min > 0): ?>
										<!-- Condition sur le nombre de membres -->
										<br>Condition : <i>avoir au moins <?= pluriel($action->nb_membres_min, 'membre') ?> dans le clan</i>
									<?php endif; ?>

									<?php if ($action->nb_allies_min > 0): ?>
										<!-- Condition sur le nombre d'alliés -->
										<br>Condition : <i>avoir au moins <?= pluriel($action->nb_allies_min, 'allié') ?> acceptant l'action avec chacun au moins <?= $action->nb_membres_allies_min ?> membres actifs</i>
									<?php endif; ?>

									<br><br>
									Coût de base : <span class="pourpre"><?= $action->cout ?> points</span>

									<?php if ($action->cout_par_allie > 0): ?>
										<br>
										Coût par alliée : <span class="pourpre"><?= $action->cout_par_allie ?> points</span>
									<?php endif; ?>
								</p>
								<p class="effet highlight inline-block padding">Action<br><?= $action->effet == Bouzouk::Clans_EffetDirect ? 'directe' : 'différée' ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>	
			
			<p class="highlight">Les clans officiels</p>
			<p class="margin">Il existe 4 clans dont le RP a été officiellement validé par la TeamBouzouk :</p>

			<ul>
				<li><span class="rouge">Le Mouvement Libérateur Bouzouk</span> (Parti politique)</li>
				<li><span class="vert">La Secte du Schnibble</span> (Organisation)</li>
				<li><span class="bleu">Le Club des Bonnes Moeurs</span> (Organisation)</li>
				<li><span class="pourpre">La Famille Struleone</span> (Organisation)</li>
			</ul>

			<p class="margin">Ces 4 clans ont des actions spécifiques supplémentaires mais sont soumis au mêmes règles que tous les autres clans.</p>

			<p class="highlight">Comment réaliser une action ?</p>
			<p class="margin">
				Il faut assez de points d'action. Pour chaque action, un minimum obligatoire de points est indiqué. Si tu n'as pas assez de points, l'action sera affichée en rouge.<br>
				Si tu as le nombre de points nécessaires dans ce cas tu pourras valider l'action :
			</p>
			
			<ul>
				<li>Si c'est une action directe, elle se réalisera dès sa validation. </li>
				<li>Si c'est une action différée, dans ce cas, l'action ne se réalisera que le lendemain et sera en concurrence directe avec les autres clans de ta catégorie.
					Il faudra miser plus de points que les autres clans pour que l'action se réalise.<br>
					Tu as jusqu'a 20h pour enchérir. Passé ce délai, il sera encore possible de surenchérir <?= $this->bouzouk->config('temps_pour_surenchere') ?> minutes après la dernière enchère. C'est la dernière enchère qui est prise en compte et l'action est réalisée après la maintenance du site.</li>
			</ul>

			<p class="highlight">Combien faut-il de membres dans le clan pour lancer une action ?</p>
			<p class="margin">
				Certaines actions nécessitent un certain nombre de membres pour pouvoir être lancées. Le nombre minimum requis est indiqué dans le détail d'une action. L'action sera rouge si tu ne peux pas la lancer.
				Si tu lances une action qui nécessite 5 membres et qu'une fois lancée les membres quittent le clan, l'action reste validée. Tu ne pourras par contre plus surenchérir ou lancer une autre action si tu n'as plus
				le nombre de membres requis.
			</p>

			<p class="highlight">Comment se font les enchères entre syndicats ?</p>
			<p class="margin">
				Les enchères des syndicats se font entre tous les syndicats de toutes les entreprises, même si plusieurs syndicats sont créés au sein d'une seule et même entreprise.<br>
			</p>

			<p class="highlight">Les actions marquées « <span class="pourpre">un jour</span> » durent-elles vraiment 24h ? Qu'en est-il des actions marquées « <span class="pourpre">24h</span> » ?</p>
			<p class="margin">
				Quand la durée d'une action est affichée en <span class="pourpre">jour</span> (ex: 1 jour, 3 jours, ...), 1 jour représente la période entre 2 maintenances consécutives. La maintenance se déroule chaque nuit à une heure aléatoire (heure française).<br>
				Quand la durée d'une action est affichée en <span class="pourpre">heure</span> (ex: 1h, 24h, ...), il s'agit de la durée réelle de l'action. Même si il y a une maintenance au milieu, l'action se déroulera jusqu'au bout.
			</p>

			<p class="highlight">Dans quel ordre sont lancées les actions à la maintenance ?</p>
			<p class="margin">
				La gestion des clans et des actions est réalisée en tout dernier à la maintenance, après tout le reste. Les actions qui touchent donc d'autres parties du jeu seront prises en compte après les
				modifications faites par la maintenance sur ces autres parties. Exemple : une action qui permet aux membres du clan de paraître à 0 strul de fortune pendant 24h ne fonctionnera pas sur une taxe
				envoyée la même nuit car la gestion des taxes aura été faite avant que l'action ne soit lancée.
			</p>

			<p class="highlight">Les missives interactives</p>
			<p class="margin">
				Certaines actions peuvent être contrées par la personne qui a été ciblée. Elle reçoit une missive lui expliquant ce qu'il risque de lui arriver.
				Dans ce cas, plusieurs solutions sont possibles :
			</p>
			
			<ul>
				<li>La personne ciblée peut coopérer avec le clan pour limiter la casse.</li>
				<li>Elle tente une action aléatoire qui a une chance de bloquer l'action mais qui risque d'avoir des effets inattendus.</li>
				<li>Si la personne a suffisamment de points d'actions correspondants à la catégorie de clan qui l'attaque, elle peut les utiliser pour bloquer l'action.</li>
			</ul>
		</div>
	</div>
</div>
