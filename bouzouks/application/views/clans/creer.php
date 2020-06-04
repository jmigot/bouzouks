<?php $this->layout->set_title('Créer un clan'); ?>

<div id="clans-creer">
	<div id="clans-lister">
		<!-- Menu -->
		<?php $this->load->view('clans/menu_liste', array('lien' => 2)) ?>

		<div class="cellule_bleu_type1">
			<h4>Formulaire de création de clan</h4>
			<div class="bloc_bleu padd_vertical">
			<?= form_open('clans/creer') ?>
						<p class="mini_bloc">
							Prix à débourser pour créer un syndicat : <?= struls($this->bouzouk->config('clans_struls_min_creer_syndicat')) ?><br>
							Prix à débourser pour créer un parti politique : <?= struls($this->bouzouk->config('clans_struls_min_creer_parti_politique')) ?><br>
							Prix à débourser pour créer une organisation : <?= struls($this->bouzouk->config('clans_struls_min_creer_organisation')) ?>
						</p>
			
						<div class="marge_haut frameborder_bleu">
							<table>
								<!-- Nom du clan -->
								<tr>
									<td><label for="nom">Nom du clan :</label></td>
									<td><input type="text" name="nom" id="nom" size="40" maxlength="35" value="<?= set_value('nom') ?>" placeholder="35 caractères max"></td>
								</tr>

								<!-- Type de clan -->
								<tr>
									<td><label for="type">Type de clan :</label></td>
									<td>
										<select name="type" id="type">
											<option value="">----------</option>

											<?php if ( ! $this->session->userdata('clan_id')[Bouzouk::Clans_TypeSyndicat] && $this->session->userdata('syndicats_autorises') && $this->session->userdata('experience') >= $this->bouzouk->config('clans_xp_min_creer_syndicat')): ?>
												<option value="<?= Bouzouk::Clans_TypeSyndicat ?>"<?= set_value('type') == Bouzouk::Clans_TypeSyndicat ? ' selected' : '' ?>>Syndicat (force)</option>
											<?php endif; ?>

											<?php if ( ! $this->session->userdata('clan_id')[Bouzouk::Clans_TypePartiPolitique] && $this->session->userdata('experience') >= $this->bouzouk->config('clans_xp_min_creer_parti_politique')): ?>
												<option value="<?= Bouzouk::Clans_TypePartiPolitique ?>"<?= set_value('type') == Bouzouk::Clans_TypePartiPolitique ? ' selected' : '' ?>>Parti politique (charisme)</option>
											<?php endif; ?>

											<?php if ( ! $this->session->userdata('clan_id')[Bouzouk::Clans_TypeOrganisation] && $this->session->userdata('experience') >= $this->bouzouk->config('clans_xp_min_creer_organisation')): ?>
												<option value="<?= Bouzouk::Clans_TypeOrganisation ?>"<?= set_value('type') == Bouzouk::Clans_TypeOrganisation ? ' selected' : '' ?>>Organisation (intelligence)</option>
											<?php endif; ?>
										</select>
									</td>
								</tr>

								<!-- Mode d'ouverture -->
								<tr>
									<td><label for="mode_recrutement">Mode de recrutement :</label></td>
									<td>
										<select name="mode_recrutement" id="mode_recrutement">
											<option value="">----------</option>
											<option value="<?= Bouzouk::Clans_RecrutementOuvert ?>"<?= set_value('mode_recrutement') == Bouzouk::Clans_RecrutementOuvert ? ' selected' : '' ?>>Ouvert (tout le monde peut rejoindre)</option>
											<option value="<?= Bouzouk::Clans_RecrutementFerme ?>"<?= set_value('mode_recrutement') == Bouzouk::Clans_RecrutementFerme ? ' selected' : '' ?>>Fermé (un chef doit valider la demande)</option>
											<option value="<?= Bouzouk::Clans_RecrutementInvisible ?>"<?= set_value('mode_recrutement') == Bouzouk::Clans_RecrutementInvisible ? ' selected' : '' ?>>Invisible (il faut connaître le nom pour postuler)</option>
										</select>
									</td>
								</tr>

								<!-- Description -->
								<tr>
									<td><label for="description">Description :</label></td>
									<td>
										<textarea name="description" class="compte_caracteres" cols="50" rows="8" maxlength="250" placeholder="Entre ici la description du clan qui sera affichée sur la  page du clan et lorsqu'un joueur recherchera un clan"><?= set_value('description') ?></textarea>
										<p id="description_nb_caracteres_restants" class="transparent centre">&nbsp;</p>
									</td>
								</tr>
							</table>
				</div>

				<!-- Créer -->
				<p class="centre clearfloat"><input type="submit" value="Créer le clan"></p>
			</form>
		</div>
	</div>
</div>
</div>