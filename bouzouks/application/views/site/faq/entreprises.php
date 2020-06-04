<?php $this->layout->set_title('FAQ - Entreprises'); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - Entreprises</h4>
		<div class="bloc_bleu">
			<!-- Retour à la FAQ -->
			<p class="highlight centre"><a href="<?= site_url('site/faq') ?>">Retour à la FAQ</a></p>

			<p class="highlight">Comment embaucher des salariés ?</p>
			<p class="margin">
				Pour embaucher, le patron pourra poster des petites annonces. Les bouzouks sans emploi pourront postuler aux annonces, le patron en sera alors immédiatement averti par missive.
				Il aura le choix d'engager le bouzouk ou de le refuser dans l'entreprise.<br>
				Si il refuse le bouzouk, l'annonce sera republiée.
			</p>

			<p class="highlight">Comment créer son entreprise ?</p>
			<p class="margin">
				Un bouzouk pourra créer son entreprise à partir de <span class="pourpre"><?= $this->bouzouk->config('entreprises_xp_creer') ?> xp</span>. Il devra débourser une certaine somme pour construire les locaux et acheter
				les machines nécessaires. Le prix maximum à payer est de <?= struls($this->bouzouk->config('entreprises_prix_entreprise')) ?> mais peut descendre jusqu'à seulement <span class="pourpre"><?= struls($this->bouzouk->config('entreprises_prix_entreprise') - $this->bouzouk->config('mairie_aide_entreprise_max')) ?></span>
				en fonction du montant de l'aide à la création d'entreprise. Ce montant est indiqué sur la page de la mairie et est décidé par le maire.
			</p>

			<p class="highlight">Combien de struls aura la caisse de l'entreprise après l'avoir créée ?</p>
			<p class="margin">
				Lors de la création d'une entreprise, celle-ci recevra l'intégralité des struls payés par le joueur pour la créer + <span class="pourpre">20% de cette somme</span>. Ainsi si un joueur débourse
				<span class="pourpre">2000 struls</span> de sa poche, son entreprise aura <span class="pourpre">2000 + 400 struls</span> (20% de 2000) soit <span class="pourpre">2400 struls</span> disponibles dans les caisses.
			</p>

			<p class="highlight">Comment sont payés les patrons des entreprises ?</p>
			<p class="margin">
				Ils ont un salaire qu'ils décident eux-mêmes et qui est pris sur les struls de l'entreprise chaque jour, après la paye des employés. Le salaire minimum que peut se fixer un patron
				est de <span class="pourpre">0 strul</span> et le maximum de <span class="pourpre"><?= $this->bouzouk->config('entreprises_salaire_max_patron') ?> struls</span>.
			</p>

			<p class="highlight">Comment seront payés les employés ?</p>
			<p class="margin">
				Le chef pourra choisir comme bon lui semble le salaire de ses ouvriers et de virer des employés. Nous lui recommandons quand même de lire la FAQ concernant les jobs. Néanmoins
				le patron devra attendre <span class="pourpre"><?= $this->bouzouk->config('entreprises_attente_embauche') ?>h</span> après l'embauche d'un bouzouk pour pouvoir changer son salaire ou le virer de l'entreprise.
				De plus, le salaire maximum d'un employé est de <span class="pourpre"><?= $this->bouzouk->config('entreprises_salaire_max_employe') ?> struls</span>.
			</p>

			<p class="highlight">À quoi sert la prime d'incompétence ?</p>
			<p class="margin">
				Cette prime est indiquée sur les petites annonces et est donc décidée à l'embauche. Elle ne peut pas être modifiée par la suite et elle sera versée à l'employé s'il se fait virer
				par le patron. Si le bouzouk démissionne, il ne touche pas sa prime d'incompétence. La prime peut varier de <span class="pourpre">0 strul</span> à <?= struls($this->bouzouk->config('entreprises_prime_max')) ?>.
			</p>

			<p class="highlight">Quelles sont les possibilités d'un patron ?</p>
			<p class="margin">
				Un patron aura la possibilité de virer ses employés. Il aura également le devoir d'embaucher de nouveaux bouzouks pour augmenter la production de son entreprise.<br>
				Un patron qui vire un employé devra lui verser sa prime d'incompétence qui est décidée au moment de l'embauche.
			</p>

			<p class="highlight">Quelles sont les possibilités d'un employé ?</p>
			<p class="margin">
				Un employé pourra discuter avec ses collègues autour de la machine à café et démissionner quand bon lui semblera. Il devra néanmoins attendre <span class="pourpre"><?= $this->bouzouk->config('boulot_attente_embauche') ?>h</span> après son embauche avant de pouvoir
				démissionner. Un joueur qui démissionne de lui-même ne perçoit pas de prime d'incompétence.
			</p>

			<p class="highlight">De quoi dépend la production d'une entreprise ?</p>
			<p class="margin">
				Le nombre d'objets produits dépend de la valeur de l'objet au marché, de ton nombre d'employés et des jobs de ces derniers : plus leurs jobs seront importants, plus
				la production sera importante. Les employés augmentent le stock de l'entreprise chaque jour, et en échange ils touchent leurs salaires.
			</p>

			<p class="highlight">Comment est acheté mon stock ?</p>
			<p class="margin">
				La production de chaque entreprise est achetée tous les 3 jours par la mairie. Elle est alors directement livrée aux <a href="<?= site_url('site/faq/shops') ?>">magasins</a>
				de la ville. Si la mairie n'a pas assez de struls pour acheter les stocks de toutes les entreprises, les stocks ne sont pas achetés en totalité et sont achetés dans les mêmes
				proportions pour chaque entreprise du jeu.<br><br>

				<span class="pourpre">Ordre : achats entreprises -> paiement des salaires et du patron -> impôts</span>
			</p>

			<p class="highlight">Que se passe-t-il si mon entreprise n'a plus de struls ?</p>
			<p class="margin">
				Ton entreprise a le droit d'aller jusqu'à <?= struls($this->bouzouk->config('entreprises_limite_faillite')) ?>, au-delà c'est la faillite, ton entreprise est détruite, tes employés mis à la porte et tu te
				retrouves au chômage. Tu devras attendre <span class="pourpre"><?= $this->bouzouk->config('entreprises_duree_faillite') ?> jours</span> avant de pouvoir recréer une entreprise.<br><br>
				<span class="pourpre">Attention, une très mauvaise gestion de ton entreprise pourrait avoir de graves répercussions sur ta fortune personnelle.</span>
			</p>


			<p class="highlight">Que se passe-t-il si le patron va à l'asile ou en pause ?</p>
			<p class="margin">
				L'entreprise continue de fonctionner, mais le patron n'est plus là pour la gérer, il y a donc des risques. Il reprendra les commandes une fois sorti de l'asile ou de la pause,
				si l'entreprise n'a pas coulé entre temps.
			</p>

			<p class="highlight">Combien puis-je injecter dans mon entreprise ?</p>
			<p class="margin">
				Tu peux injecter un total de <?= struls($this->bouzouk->config('entreprises_max_injection')) ?> dans ton entreprise en tant que patron, toutes les <span class="pourpre"><?= $this->bouzouk->config('entreprises_intervalle_max_injection') ?> heures</span>.
			</p>
			
			<p class="highlight">Que se passe-t-il si le patron démissionne ?</p>
			<p class="margin">
				L'entreprise est léguée à l'employé actif ayant le plus d'ancienneté. Un joueur en pause ou à l'asile n'est pas considéré comme actif. Si aucun employé n'est apte à remplacer
				le patron, la boîte est coulée, le patron et les employés se retrouvent au chômage. Démissionner fait perdre <span class="pourpre"><?= $this->bouzouk->config('entreprises_perte_xp_demission') ?> xp</span>.
			</p>
		</div>
	</div>
</div>
