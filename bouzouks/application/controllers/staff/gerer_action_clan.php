<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : modération/administration du site
 *
 * @Autor      : Hikingyo
 * @Date        : Oct 2015
 *
 * Copyright (C) 2012-2015 Hikingyo - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Gerer_action_clan extends MY_Controller
{

    public function __construct(){
        parent::__construct();
        $this->load->library('Lib_clans');
    }


    public function index()
    {
        //Titre de la page
        $vars['title'] = 'Gestion Actions Clans';
        // Liste des clans
        $this->load->library('lib_clans');
        $clans = $this->lib_clans->get_all_clan();
        $clan_info = array(
            Bouzouk::Clans_TypeSyndicat => array('nom_type'=> 'Syndicats', 'nb_membres'=> 0, 'nb_actifs' => 0),
            Bouzouk::Clans_TypePartiPolitique => array('nom_type'=> 'Partis politiques', 'nb_membres'=> 0, 'nb_actifs' => 0),
            Bouzouk::Clans_TypeOrganisation => array('nom_type'=> 'Organisations', 'nb_membres'=> 0, 'nb_actifs' => 0),
            Bouzouk::Clans_TypeCDBM => array('nom_type'=> 'CDBM', 'nb_membres'=> 0, 'nb_actifs' => 0),
            Bouzouk::Clans_TypeSDS => array('nom_type'=> 'SDS', 'nb_membres'=> 0, 'nb_actifs' => 0),
            Bouzouk::Clans_TypeMLB => array('nom_type'=> 'MLB', 'nb_membres'=> 0, 'nb_actifs' => 0),
            Bouzouk::Clans_TypeStruleone => array('nom_type'=>'Struleone', 'nb_membres'=> 0, 'nb_actifs' => 0)
        );
        foreach( $clans as $clan){
            $nbr_membres = $this->lib_clans->get_nbr_membres($clan->id);
            $nbr_membres_actifs = $this->lib_clans->get_nbr_membres_actifs($clan->id);
            $clan->nb_membres = $nbr_membres;
            $clan->nb_actifs = $nbr_membres_actifs;
            if($clan->type_officiel){
                $clan_info[$clan->type_officiel]['clans'][] = $clan;
                // Nombre total de membre par type
                $clan_info[$clan->type_officiel]['nb_membres'] += $clan->nb_membres;
                // Nombre total de membres actifs par type
                $clan_info[$clan->type_officiel]['nb_actifs'] += $clan->nb_actifs;
            }
            else{
                $clan_info[$clan->type]['clans'][] = $clan;
                // Nombre total de membre par type
                $clan_info[$clan->type]['nb_membres'] += $clan->nb_membres;
                // Nombre total de membres actifs par type
                $clan_info[$clan->type]['nb_actifs'] += $clan->nb_actifs;
            }
            $vars['info_clans'] = $clan_info;
        }
        // Les actions
        $vars['actions'] = $this->lib_clans->get_all_action();
        return $this->layout->view('staff/action_clan/gerer_action_clan', $vars);
    }

    public function modifier($action_id){
        // On récupère l'action à modifier
        $this->load->library('lib_clans');
        if($this->lib_clans->is_action($action_id)){
            $action = $this->lib_clans->get_action($action_id);
        }
        else{
            $this->echec("L'action demander n'existe pas");
            return $this->index();
        }
        $vars['title'] = "Modifier Action";

        // Validation du formulaire
        $this->load->library('form_validation');

        // Règles de validation
        $rules = array(
            array(
                'field' => 'description',
                'label' => 'description de l\'action',
                'rules' => 'required'
            ),
            array(
                'field' => 'clan_type',
                'label' =>'type de clan',
                'rules' =>'required|callback_check_clan_type'
            ),
            array(
                'field' => 'effet',
                'label' => 'effet de l\'action',
                'rules' => 'required|max_length[3]|callback_check_effet'
            ),
            array(
                'field' => 'cout',
                'label' => "coût de l'action",
                'rules' => 'required|max_length[10]|is_natural'
            ),
            array(
                'field' => 'duree',
                'label' => 'durée de l\'action',
                'rules' => 'required|max_length[3]|is_natural'
            ),
            array(
                'field' => 'nb_membres_min',
                'label' => 'nombre de membres minimum',
                'rules' => 'required|max_length[3]|is_natural'
            ),
            array(
                'field' => 'nb_allies_min',
                'label' => 'nombre d\'alliés minimum',
                'rules' => 'required|max_length[3]|is_natural'
            ),
            array(
                'field' => 'nb_membres_allies_min',
                'label' => "nombre de mebres minimum par alliés",
                'rules' => 'required|max_length[3]|is_natural|callback_check_nb_membres_allies_min'
            ),
            array(
                'field' => 'cout_par_allie',
                'label' => 'coût par alliés',
                'rules' => 'required|max_length[10]|is_natural|callback_check_cout_par_allie'
            )
        );
        $this->form_validation->set_rules($rules);

        // Formulaire valide
        if($this->form_validation->run()){
            //Récupération des données
            $action->clan_type = $this->input->post('clan_type');
            $action->nom = $this->input->post('nom');
            $action->description = $this->input->post('description');
            $action->effet = $this->input->post('effet');
            $action->cout = $this->input->post('cout');
            $action->duree = $this->input->post('duree');
            $action->nb_membres_min = $this->input->post('nb_membres_min');
            $action->nb_allies_min = $this->input->post('nb_allies_min');
            $action->nb_membres_allies_min = $this->input->post('nb_membres_allies_min');
            $action->cout_par_allie = $this->input->post('cout_par_allie');

            // mise à jour de l'action
            $this->lib_clans->update_action($action);

            // Retour au tableau des actions
            $this->succes("L'action a bien été modifié");
            return $this->index();
        }
        // Affichage du formulaire
        else{
            $vars['action'] = $action;
            return $this->layout->view('staff/action_clan/modifier_action_clan', $vars);
        }
    }

    public function check_clan_type($clan_type){
        $clan_types = array(
            Bouzouk::Clans_TypeSyndicat,
            Bouzouk::Clans_TypePartiPolitique,
            Bouzouk::Clans_TypeOrganisation,
            Bouzouk::Clans_TypeCDBM,
            Bouzouk::Clans_TypeStruleone,
            Bouzouk::Clans_TypeSDS,
            Bouzouk::Clans_TypeMLB
        );
        if(!in_array($clan_type,$clan_types)){
            $this->form_validation->set_message('check_clan_type', "Ce type de clan n'existe pas ! $clan_type");
            return FALSE;
        }
        else{
            return TRUE;
        }
    }

    public function check_effet($effet){
        $effets = array(
            Bouzouk::Clans_EffetDiffere,
            Bouzouk::Clans_EffetDirect
        );
        if(!in_array($effet, $effets)){
            $this->form_validation->set_message('check_effet', 'le type d\'effet demandé n\'existe pas');
            return FALSE;
        }
        else{
            return TRUE;
        }
    }

    public function check_nb_membres_allies_min($nb_mb_allie){
        if($this->input->post('nb_allies_min') != 0 && $nb_mb_allie == 0){
            $this->form_validation->set_message('check_nb_membres_allies_min', 'Le nombre de membres par alliés doit être supérieur à zéro !');
            return FALSE;
        }
        else{
            return TRUE;
        }
    }

    public function check_cout_par_allie($cout_par_allie){
        if($this->input->post('nb_allies_min') != 0 && $cout_par_allie == 0){
            $this->form_validation->set_message('check_cout_par_allie', "Les coût par allié doit être supérieur à zéro !!");
            return FALSE;
        }
        else{
            return TRUE;
        }
    }
}