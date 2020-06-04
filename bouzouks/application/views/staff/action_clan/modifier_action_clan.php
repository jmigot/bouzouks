<?php
$this->layout->set_title($title);
?>
<div id="gerer-gerer-objet">
    <div class="cellule_bleu_type1 marge_haut">
        <h4>Modification de l'action<?= $action->nom ?></h4>

        <div class="bloc_bleu">
            <p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>
            <?php
            echo form_open('staff/gerer_action_clan/modifier/'.$action->id);
            echo "Nom de l'action : ".form_input(array('name' => 'nom', 'value' => set_value('nom', $action->nom)))."<br>";
            echo "Description : <br>".form_textarea(array('name' => 'description', 'value' => set_value('description', $action->description)))."<br>";
            $opt_clan_type = array(
                Bouzouk::Clans_TypeSyndicat => 'Syndicat',
                Bouzouk::Clans_TypePartiPolitique => 'Parti',
                Bouzouk::Clans_TypeOrganisation => 'Orga',
                Bouzouk::Clans_TypeCDBM => 'CDBM',
                Bouzouk::Clans_TypeStruleone => 'Struleon',
                Bouzouk::Clans_TypeSDS => 'SDS',
                Bouzouk::Clans_TypeMLB => 'MLB'
            );
            echo "Type de clan : ".form_dropdown('clan_type', $opt_clan_type, $opt_clan_type[set_value('clan_type', $action->clan_type)])."<br>";
            $opt_effet = array(
                Bouzouk::Clans_EffetDiffere => 'différé',
                Bouzouk::Clans_EffetDirect => 'direct'
            );
            echo "Type d'effet : ".form_dropdown('effet', $opt_effet, set_value('effet', $opt_effet[$action->effet]))."<br>";
            echo "Coût : ".form_input(array('name' => 'cout', 'value' => set_value('cout',$action->cout)))." PA.<br>";
            echo "Durée : ".form_input(array('name' => 'duree', 'value' => set_value('durre', $action->duree)))." heure(s).<br>";
            echo "Nombre de membres minimum : ".form_input(array('name' => 'nb_membres_min', 'value' => set_value('nb_membres_min', $action->nb_membres_min)))."<br>";
            echo "Nombre d'alliés minimum : ".form_input(array('name' => 'nb_allies_min', 'value' => set_value('nb_allies_min', $action->nb_allies_min)))."<br>";
            echo "Nombre de membres par alliés : ".form_input(array('name' => 'nb_membres_allies_min', 'value' => set_value('nb_membres_allies_min', $action->nb_membres_allies_min)))."<br>";
            echo "Coût par alliés : ".form_input(array('name' => 'cout_par_allie', 'value' => set_value('cout_par_allie', $action->cout_par_allie)))." PA.<br>";
            echo form_submit('gerer_action', 'Modifier');
            echo form_close();
            ?>
        </div>
    </div>
</div>
