<?php $this->layout->set_title('Le lohtoh qui rend barjo !'); ?>

<div id="jeux-lohtoh">
	<!-- Menu -->
	<div class="onglet">
		<div class="menu">
			<a class="actif" href="<?= site_url('jeux/lohtoh') ?>" title="Lohtoh">Lohtoh</a>
			<a href="<?= site_url('jeux/lohtoh_tirages') ?>" title="Derniers tirages">Derniers tirages</a>
		</div>
		<div class="deco onglet1">
		</div>
	</div>
	
	<?php 
		// ---------- Hook clans ----------
		// Tag MLBiste (MLB)
		if (($tag_mlbiste = $this->bouzouk->clans_tag_mlbiste()) != null)
			$this->load->view('clans/tag_mlb', array('tag_mlbiste' => $tag_mlbiste));
	?>



	<div class="tirage">
		<p class="texte centre">
			Si tu veux dépenser le peu d'argent que tu as dans l'espoir que<br>
			ta vie misérable s'améliore, viens tenter ta chance !<br>
			Il te suffit de choisir une combinaison de <span class="pourpre"><?= $this->bouzouk->config('jeux_nb_numeros_a_jouer') ?> chiffres</span> et<br>
			de perdr... heu... de parier <?= struls($this->bouzouk->config('jeux_prix_ticket_lohtoh')) ?> !  Tirage une fois par jour.
		</p>
		<h4>Lohtoh bouzouk</h4>
		<div class="ticket bloc_bleu padd_vertical">
			<p class="cagnotte mini_bloc">Total de la cagnotte : <b><?= struls($cagnotte) ?></b></p>
			<table class="centre">
			<tr>
				<td class="frameborder_bleu">
					<?= form_open('jeux/lohtoh') ?>
					Choisis tes numéros :<br><br>

					<?php for ($i = 1; $i <= $this->bouzouk->config('jeux_nb_numeros_a_jouer'); $i++): ?>
						<select name="choix<?= $i ?>">
							<option value="1">KAH</option>
							<option value="2">ZIG</option>
							<option value="3">STO</option>
							<option value="4">BLAZ</option>
							<option value="5">DRU</option>
							<option value="6">GOZ</option>
							<option value="7">POO</option>
							<option value="8">BNZ</option>
							<option value="9">GLAP</option>
							<option value="0">GNEE</option>
						</select>
					<?php endfor; ?><br>
					<input type="submit" value="Jouer <?= struls($this->bouzouk->config('jeux_prix_ticket_lohtoh'), false) ?>">
					</form>
				</td><td>
					<?= form_open('jeux/lohtoh') ?>
					Ou laisse l'aléatoire<br>dicter ta vie :<br><br>
					<input type="hidden" name="random" value="true">
					<input type="submit" value="Jouer <?= struls($this->bouzouk->config('jeux_prix_ticket_lohtoh'), false) ?> aléatoirement">
					</form>
				</td>
			</tr>
		</table>
		<p class="frameborder_bleu centre taxe">Impôts mairie : <span class="pourpre"><?= $impots_mairie ?>%</span></p>
		<p class="frameborder_bleu centre padd_vertical"><span class="rond_blanc">Part mairie : <?= struls($part_mairie) ?></span><span class="rond_blanc marge_gauche">Part gagnant : <?= struls($part_gagnant) ?></span></p>
		</div>
	</div>

	<!-- Numéros déjà joués -->
	<div class="deja_joues cellule_gris_type1">
		<h4>Liste des tickets joués</h4>
		<div class="bloc_gris padd_vertical centre">
			<p class="mini_bloc">Tu as joué <span class="pourpre"><?= pluriel($nb_numeros_joues, 'numéro') ?></span> pour un montant total de <?= struls($montant_total) ?></p>

			<table>
				<?php foreach ($numeros_joues as $numero): ?>
					<tr class="frameborder_gris">
						<?php for ($i = 0; $i < $this->bouzouk->config('jeux_nb_numeros_a_jouer'); $i++): ?>
							<td class="centre pourpre"><?= $i < mb_strlen($numero->numeros) ? $nombres[$numero->numeros[$i]] : '' ?></td>
							<?= ($i < $this->bouzouk->config('jeux_nb_numeros_a_jouer') - 1) ? '<td>-</td>' : '' ?>
						<?php endfor; ?>
						<td><p class="highlight"><?= struls($numero->montant) ?></p></td>
					</tr>
					<tr>
					<td colspan="<?= $this->bouzouk->config('jeux_nb_numeros_a_jouer') * 2 ?>"><p class="hr"></p></td>
					</tr>
				<?php endforeach; ?>
		</table>
		</div>
	</div>

</div>
