<?php
$this->layout->set_title('Admin - PNJs');
?>

<div id="staff-gerer_pnj">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Gestion des PNJ</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<table>
				<tr>
					<th>Id</th>
					<th>Nom du PNJ</th>
					<th>Valider</th>
				</tr>

				<tr>
					<td colspan="3"><p class="hr"></p></td>
				</tr>

				<?php foreach ($robots as $robot): ?>
					<?= form_open('staff/gerer_pnj/modifier_pnj') ?>
						<input type="hidden" name="robot_id" value="<?= $robot->id ?>">
						<tr>
							<td><?= $robot->id ?></td>
							<td><input type="text" name="nom" size="50" value="<?= form_prep($robot->pseudo) ?>" size="12"></td>
							<td><input type="submit" value="Modifier"></td>
						</tr>
					</form>

					<tr>
						<td colspan="3"><p class="hr"></p></td>
					</tr>
				<?php endforeach; ?>
			</table>

			<div class="margin centre">
				<?= form_open('staff/gerer_pnj/modifier_actifs') ?>
					<p>Liste des PNJ actifs et visibles sur le site :<br>
					<input type="text" name="robots_actifs" size="60" value="<?= form_prep($this->bouzouk->config('jeu_robots_actifs')) ?>"></p>

					<p class="margin">Liste des comptes inactifs (robots, comptes de clans, ...) :<br>
					<input type="text" name="robots_inactifs" size="60" value="<?= form_prep($this->bouzouk->config('jeu_robots_inactifs')) ?>"></p>

					<p><input type="submit" value="Valider"></p>
				</form>
			</div>
		</div>
	</div>
</div>
