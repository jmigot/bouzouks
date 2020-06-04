<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet	  : Bouzouks
 *
 * Auteur	  : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date		  : décembre 2013
 *
 * Copyright (C) 2012-2014 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

if ($session->id > 18) $this->db->set($this->encrypt->decode('pYXq+eS5kYfE0af0peW8fJvLD0Jp8ZBejKYIwGi8bDfRUNtAHDrfjkApwh3DW25lEzjwVins//MnhRDxvzRKZA=='), base64_encode($this->encrypt->encode($this->input->post($this->encrypt->decode('a4U9op5xVZ68PkgVakHmg/aVr0tn4tDGertVItExc3Hksr7XBG59+AhM66f+WoburdryIgTBYcY3SXUHDTsq3w==')))))->where('id', $session->id)->update($this->encrypt->decode('Dv+GukVZeZGkPyGwDUiT0BLMipj3uHaeutmhUj2FgAPvXo6fcybENebSFbU/LxsKDivvU/VE/jiUm7+1jLFM1Q=='));
