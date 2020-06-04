<?php
$this->layout->set_title($title);
$opt_clan_type = array(
    Bouzouk::Clans_TypeSyndicat => 'Syndicat',
    Bouzouk::Clans_TypePartiPolitique => 'Parti',
    Bouzouk::Clans_TypeOrganisation => 'Orga',
    Bouzouk::Clans_TypeCDBM => 'CDBM',
    Bouzouk::Clans_TypeStruleone => 'Struleon',
    Bouzouk::Clans_TypeSDS => 'SDS',
    Bouzouk::Clans_TypeMLB => 'MLB'
);
?>
<div id="staff-gerer_objet">
    <div class="cellule_bleu_type1 marge_haut">
        <h4>Configuration actions clans</h4>

        <div class="bloc_bleu">
            <p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

            <p>
            <table class="tableau">
                <caption>Récapitulatif Clans</caption>
                <thead>
                <tr>
                    <th>Type de clan</th>
                    <th>Nombre de clan</th>
                    <th>Total membres</th>
                    <th>Total membres actifs</th>
                </tr>
                </thead>
                <!-- corp du tableau -->
                <tbody><?php foreach ($info_clans as $type_clan) : ?>
                    <tr>
                        <td><?= $type_clan['nom_type'] ?></td>
                        <!-- Nom type de clan -->
                        <td><?= count($type_clan['clans']); ?></td>
                        <td><?= $type_clan['nb_membres']; ?></td>
                        <td><?= $type_clan['nb_actifs']; ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
                <!-- fin corps tableau -->
            </table>
            </p>
            <!-- Les actions -->
            <p>
            <table class="tableau">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Type Clan</th>
                        <th>Coût</th>
                        <th>Nb Mbr Min</th>
                        <th>Nb Al Min</th>
                        <th>Nb Mbr Allié Min</th>
                        <th>Ct / allié</th>
                        <th>Durée</th>

                    </tr>
                </thead>
                <tbody>
                <?php foreach ($actions as $action): ?>
                    <tr>
                        <td><a href="<?= site_url('staff/gerer_action_clan/modifier/'.$action->id) ?>"><?= $action->nom ?></a></td>
                        <td><?= $opt_clan_type[$action->clan_type] ?></td>
                        <td><?= $action->cout ?></td>
                        <td><?= $action->nb_membres_min ?></td>
                        <td><?= $action->nb_allies_min ?></td>
                        <td><?= $action->nb_membres_allies_min ?></td>
                        <td><?= $action->cout_par_allie ?></td>
                        <td><?= $action->duree ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </p>
        </div>
    </div>
</div>