<?php $this->layout->set_title('FAQ - Expérience'); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - Expérience</h4>
		<div class="bloc_bleu">
			<p class="highlight centre"><a href="<?= site_url('site/faq') ?>">Retour à la FAQ</a></p>
			<p class="margin">
				Un bouzouk qui vient de s'inscrire ou qui redémarre sa partie après être parti en quête du schnibble (game over) possède 5 d'expérience.<br><br>
				L'expérience permet de définir le job que tu peux occuper en entreprise et donc le salaire auquel tu peux accéder. Certaines parties du site te seront inaccessibles
				tant que tu n'auras pas l'expérience requise.<br><br>

				Il est possible de gagner ou de perdre de l'expérience en fonction de tes actions dans le jeu.<br>
				Il n'est pas possible d'avoir une expérience négative, elle reste bloquée à 0.
			</p>

			<p class="highlight pourpre">Expérience automatique chaque jour</p>
			<?php
				$gain_actif = $this->bouzouk->config('joueur_gain_xp_joueur_actif');
				$gain_employe = $this->bouzouk->config('joueur_gain_xp_employe');
				$gain_patron = $this->bouzouk->config('joueur_gain_xp_patron');
				$gain_maire = $this->bouzouk->config('joueur_gain_xp_maire');
			?>
			<ul>
				<li>Être un joueur actif (ni en pause ni à l'asile) : <span class="pourpre">+<?= $gain_actif ?> xp / jour</span></li>
				<li>Avoir un emploi : <span class="pourpre">+<?= $gain_employe ?> xp / jour</span></li>
				<li>Être patron d'entreprise : <span class="pourpre">+<?= $gain_patron ?> xp / jour</span></li>
				<li>Être maire de la ville : <span class="pourpre">+<?= $gain_maire ?> xp / jour</span></li>
			</ul>



			<p class="margin italique">Ces expériences se cumulent : être patron et maire en même temps rapporte (<?= $gain_actif ?>+<?= $gain_patron ?>+<?= $gain_maire ?>) =
			<span class="pourpre">+<?= $gain_actif + $gain_patron + $gain_maire ?> xp / jour</span></p>

			<p class="highlight pourpre">Gagner de l'expérience</p>
			<ul>
				<li>Candidater aux élections : <span class="pourpre">+<?= $this->bouzouk->config('elections_gain_xp_candidater') ?> xp</span></li>
				<li>Voter : <span class="pourpre">+<?= $this->bouzouk->config('elections_gain_xp_voter') ?> xp</span></li>
				<li>Créer une entreprise : <span class="pourpre">+<?= $this->bouzouk->config('entreprises_gain_xp_creer') ?> xp</span></li>
				<li>Jouer au Lohtoh : <span class="pourpre">+<?= $this->bouzouk->config('jeux_gain_xp_lohtoh') ?> xp</span> (une fois par jour)</li>
				<li>Gagner 4 fois de suite au bonneteau : <span class="pourpre">+<?= $this->bouzouk->config('jeux_gain_xp_bonneteau') ?> xp</span></li>
				<li>Acheter un objet rare à un bouzouk mythique (J.F Sébastien par ex.) : <span class="pourpre">+<?= $this->bouzouk->config('marche_noir_gain_xp_objet_rare') ?> xp</span></li>
				<li>Donner 5 struls ou plus à un mendiant pauvre : <span class="pourpre">+<?= $this->bouzouk->config('mendiants_gain_xp_pauvre') ?> xp</span> (une fois par jour)</li>
				<li>Arriver jusqu'au 2è tour aux élections : <span class="pourpre">+<?= $this->bouzouk->config('elections_gain_xp_tour2') ?> xp</span></li>
				<li>Arriver au 3è tour des élections : <span class="pourpre">+<?= $this->bouzouk->config('elections_gain_xp_tour3') ?> xp</span></li>
				<li>Gagner au Lohtoh : <span class="pourpre">+<?= $this->bouzouk->config('jeux_gain_xp_gagnant_lohtoh') ?> xp</span></li>
			</ul>

			<p class="highlight pourpre">Perdre de l'expérience</p>
			<ul>
				<li>Démissionner en tant qu'employé : <span class="pourpre">-<?= $this->bouzouk->config('boulot_perte_xp_demission') ?> xp</span></li>
				<li>Démissionner en tant que patron : <span class="pourpre">-<?= $this->bouzouk->config('entreprises_perte_xp_demission') ?> xp</span></li>
				<li>Se faire prendre par la bouzopolice au marché noir : <span class="pourpre">-<?= $this->bouzouk->config('marche_noir_perte_xp_achat_police') ?> xp</span> (achat) ou <span class="pourpre">-<?= $this->bouzouk->config('marche_noir_perte_xp_vente_police') ?> xp</span> (vente)</li>
				<li>Donner 5 struls ou plus à un mendiant riche : <span class="pourpre">-<?= $this->bouzouk->config('mendiants_perte_xp_riche') ?> xp</span></li>
				<li>Être éliminé du 1er tour des élections : <span class="pourpre">-<?= $this->bouzouk->config('elections_perte_xp_tour2') ?> xp</span></li>
				<li>Mettre son entreprise en faillite : <span class="pourpre">-<?= $this->bouzouk->config('entreprises_faillites_perte_xp') ?> xp</span></li>
				<li>Aller à l'asile : <span class="pourpre">-<?= $this->bouzouk->config('joueur_perte_xp_asile') ?> xp</span> (jauge de stress) ou <span class="pourpre">-<?= $this->bouzouk->config('joueur_perte_xp_asile_moderation') ?> xp</span> (punition)</li>
				<li>Ne recevoir aucun don de la journée en tant que mendiant : <span class="pourpre">-<?= $this->bouzouk->config('mendiants_perte_xp_aucun_don') ?> xp</span></li>
			</ul>

			<p class="highlight pourpre">Sections nécessitant une certaine expérience</p>
			<ul>
				<li>Candidater aux élections : avoir <span class="pourpre"><?= $this->bouzouk->config('elections_xp_candidater') ?> xp</span> minimum</li>
				<li>Voter aux élections : avoir <span class="pourpre"><?= $this->bouzouk->config('elections_xp_voter') ?> xp</span> minimum</li>
				<li>Créer une entreprise : avoir <span class="pourpre"><?= $this->bouzouk->config('entreprises_xp_creer') ?> xp</span> minimum</li>
				<li>Poster une rumeur : avoir <span class="pourpre"><?= $this->bouzouk->config('communaute_xp_proposer_rumeur') ?> xp</span> minimum</li>
			</ul>
		</div>
	</div>
</div>

