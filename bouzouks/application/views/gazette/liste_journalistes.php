<?php

// On va chercher la liste des journalistes
$query = $this->db->select('id, pseudo, rang')
					->from('joueurs')
					->where('rang & '.$this->bouzouk->get_masque(Bouzouk::Masque_Journaliste | Bouzouk::Masque_Admin).' > 0')
					->order_by('rang', 'desc')
					->order_by('pseudo')
					->get();

$nb_journalistes = $query->num_rows();
$journalistes = array(
	'stagiaires'   => array(),
	'journalistes' => array(),
	'chefs'        => array(),
);

foreach ($query->result() as $journaliste)
{
	if ($journaliste->rang & Bouzouk::Rang_JournalisteStagiaire)
		$journalistes['stagiaires'][] = $journaliste;

	else if ($journaliste->rang & Bouzouk::Rang_Journaliste)
		$journalistes['journalistes'][] = $journaliste;

	else if ($journaliste->rang & ($this->bouzouk->get_masque(Bouzouk::Masque_Admin) | Bouzouk::Rang_JournalisteChef))
		$journalistes['chefs'][] = $journaliste;
}

?>

<div class="cellule_gris_type1 marge_haut">
	<h4>Liste des journalistes</h4>
	<div class="bloc_gris">
		<p class="margin italique pourpre">Il y a en tout <?= pluriel($nb_journalistes, 'journaliste') ?> dans la rédaction</p>

		<!-- Stagiaires -->
		<p class="highlight pourpre">&nbsp;&nbsp;Chroniqueurs</p>
		<table class="liste_bouzouks margin">
			<tr>
				<?php $i = 0; ?>
				<?php foreach ($journalistes['stagiaires'] as $journaliste): ?>
					<td><?= profil($journaliste->id, $journaliste->pseudo, $journaliste->rang) ?></td>
					<?php if (++$i % 4 == 0): ?>
						</tr>
						<tr>
					<?php endif; ?>
				<?php endforeach; ?>
			</tr>
		</table>

		<!-- Journalistes -->
		<p class="highlight pourpre">&nbsp;&nbsp;Journalistes</p>
		<table class="liste_bouzouks margin">
			<tr>
				<?php $i = 0; ?>
				<?php foreach ($journalistes['journalistes'] as $journaliste): ?>
					<td><?= profil($journaliste->id, $journaliste->pseudo, $journaliste->rang) ?></td>
					<?php if (++$i % 4 == 0): ?>
						</tr>
						<tr>
					<?php endif; ?>
				<?php endforeach; ?>
			</tr>
		</table>

		<!-- Chefs -->
		<p class="highlight pourpre">&nbsp;&nbsp;Rédacteurs en chef</p>
		<table class="liste_bouzouks margin">
			<tr>
				<?php $i = 0; ?>
				<?php foreach ($journalistes['chefs'] as $journaliste): ?>
					<td><?= profil($journaliste->id, $journaliste->pseudo, $journaliste->rang) ?></td>
					<?php if (++$i % 4 == 0): ?>
						</tr>
						<tr>
					<?php endif; ?>
				<?php endforeach; ?>
			</tr>
		</table>
	</div>
</div>
