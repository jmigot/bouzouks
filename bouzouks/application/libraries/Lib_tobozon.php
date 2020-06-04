<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : fonctions de correspondance avec FluxBB (forum appelé tobozon dans le jeu)
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : décembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Lib_tobozon
{
	private $CI;
	private $config;

	public function __construct()
	{
		$this->CI =& get_instance();

		// On lit la config de FluxBB
		require_once FCPATH.'tobozon/config.php';
		$this->config = array(
			'cookie_name'   => $cookie_name,
			'cookie_domain' => $cookie_domain,
			'cookie_path'   => $cookie_path,
			'cookie_secure' => $cookie_secure,
			'cookie_seed'   => $cookie_seed
		);
	}

	public function connecter($joueur = null)
	{
		// On supprime le joueur des visiteurs du tobozon
		$this->CI->db->where('ident', $this->CI->input->ip_address())
					 ->delete('tobozon_online');

		// Si le joueur à connecté n'est pas forcé
		if ( ! isset($joueur))
		{
			// On récupère le mot de passe
			$query = $this->CI->db->select('id, mot_de_passe')
								 ->from('joueurs')
								 ->where('pseudo', $this->CI->session->userdata('pseudo'))
								 ->get();
			$joueur = $query->row();
		}

		$expire = time() + 51840000; // 2 ans environ
		
		$this->pun_setcookie($joueur->id, $joueur->mot_de_passe, $expire);
		$this->set_tracked_topics(null);
	}

	public function deconnecter()
	{
		// On supprime le joueur des "en ligne" du tobozon
		$this->CI->db->where('user_id', $this->CI->session->userdata('id'))
					 ->delete('tobozon_online');

		// On met à jour le champ last_visit
		if ( ! $this->CI->session->userdata('admin_connecte'))
		{
			$this->CI->db->set('last_visit', time())
						->where('id', $this->CI->session->userdata('id'))
						->update('tobozon_users');
		}

		$this->pun_setcookie(1, sha1(uniqid(rand(), true)), time() + 31536000);
	}

	public function pun_setcookie($user_id, $password_hash, $expire)
	{
		$cookie_name = $this->config['cookie_name'];
		$cookie_seed = $this->config['cookie_seed'];
		
		$this->forum_setcookie($cookie_name, $user_id.'|'.$this->forum_hmac($password_hash, $cookie_seed.'_password_hash').'|'.$expire.'|'.$this->forum_hmac($user_id.'|'.$expire, $cookie_seed.'_cookie_hash'), $expire);
	}

	public function forum_setcookie($name, $value, $expire)
	{
		$cookie_path   = $this->config['cookie_path'];
		$cookie_domain = $this->config['cookie_domain'];
		$cookie_secure = $this->config['cookie_secure'];

		// Enable sending of a P3P header
		header('P3P: CP="CUR ADM"');

		if (version_compare(PHP_VERSION, '5.2.0', '>='))
			setcookie($name, $value, $expire, $cookie_path, $cookie_domain, $cookie_secure, true);
		else
			setcookie($name, $value, $expire, $cookie_path.'; HttpOnly', $cookie_domain, $cookie_secure);
	}

	public function forum_hmac($data, $key, $raw_output = false)
	{
		if (function_exists('hash_hmac'))
			return hash_hmac('sha1', $data, $key, $raw_output);

		// If key size more than blocksize then we hash it once
		if (strlen($key) > 64)
			$key = pack('H*', sha1($key)); // we have to use raw output here to match the standard

		// Ensure we're padded to exactly one block boundary
		$key = str_pad($key, 64, chr(0x00));

		$hmac_opad = str_repeat(chr(0x5C), 64);
		$hmac_ipad = str_repeat(chr(0x36), 64);

		// Do inner and outer padding
		for ($i = 0;$i < 64;$i++) {
			$hmac_opad[$i] = $hmac_opad[$i] ^ $key[$i];
			$hmac_ipad[$i] = $hmac_ipad[$i] ^ $key[$i];
		}

		// Finally, calculate the HMAC
		$hash = sha1($hmac_opad.pack('H*', sha1($hmac_ipad.$data)));

		// If we want raw output then we need to pack the final result
		if ($raw_output)
			$hash = pack('H*', $hash);

		return $hash;
	}

	public function set_tracked_topics($tracked_topics)
	{
		$cookie_name   = $this->config['cookie_name'];
		$cookie_path   = $this->config['cookie_path'];
		$cookie_domain = $this->config['cookie_domain'];
		$cookie_secure = $this->config['cookie_secure'];

		$cookie_data = '';
		$this->forum_setcookie($cookie_name.'_track', $cookie_data, time() + $this->pun_config('o_timeout_visit'));
		$_COOKIE[$cookie_name.'_track'] = $cookie_data; // Set it directly in $_COOKIE as well
	}

	public function pun_config($cle)
	{
		$query = $this->CI->db->select('conf_value')
							  ->from('tobozon_config')
							  ->where('conf_name', $cle)
							  ->get();
		$config_tobozon = $query->row();
		return $config_tobozon->conf_value;
	}

	public function bannir($joueur_id)
	{
		// On l'enlève de la liste des modérateurs
		$this->supprimer_moderateur($joueur_id);

		// On supprime toutes les souscriptions
		$this->CI->db->where('user_id', $joueur_id)
					 ->delete('tobozon_topic_subscriptions');
		$this->CI->db->where('user_id', $joueur_id)
					 ->delete('tobozon_forum_subscriptions');

		// On l'enlève de la liste des connectés
		$this->CI->db->where('user_id', $joueur_id)
					 ->delete('tobozon_online');

		// On met à jour le cache
		$this->regenerer_cache('cache_users_info.php');
	}
	
	public function supprimer_joueur($joueur_id)
	{
		// On l'enlève de la liste des modérateurs
		$this->supprimer_moderateur($joueur_id);

		// On supprime toutes les souscriptions
		$this->CI->db->where('user_id', $joueur_id)
					 ->delete('tobozon_topic_subscriptions');
		$this->CI->db->where('user_id', $joueur_id)
					 ->delete('tobozon_forum_subscriptions');

		// On l'enlève de la liste des connectés
		$this->CI->db->where('user_id', $joueur_id)
					 ->delete('tobozon_online');

		// On change le pseudo de tous ses posts à "Invité"
		$this->CI->db->set('poster_id', 1)
					 ->set('poster', 'Un pochtron')
					 ->where('poster_id', $joueur_id)
					 ->update('tobozon_posts');

		// On supprime l'utilisateur
		$this->CI->db->where('id', $joueur_id)
					 ->delete('tobozon_users');

		// On supprime l'avatar
		$this->supprimer_avatar($joueur_id);

		// On met à jour le cache
		$this->regenerer_cache('cache_users_info.php');
	}

	public function supprimer_avatar($joueur_id)
	{
		// On récupère le dossier des avatars
		$query = $this->CI->db->select('conf_value')
							  ->from('tobozon_config')
							  ->where('conf_name', 'o_avatars_dir')
							  ->get();

		if ($query->num_rows() == 0)
			$avatars_dir = 'img/avatars';

		else
		{
			$config = $query->row();
			$avatars_dir = $config->conf_value;
		}
		
		$filetypes = array('jpg', 'gif', 'png');

		foreach ($filetypes as $cur_type)
		{
			$fichier = FCPATH.'tobozon/'.$avatars_dir.'/'.$joueur_id.'.'.$cur_type;

			if (file_exists($fichier))
				@unlink($fichier);
		}
	}

	public function supprimer_moderateur($joueur_id)
	{
		// On récupère le pseudo et le groupe du joueur
		$query = $this->CI->db->select('username, group_id')
							  ->from('tobozon_users')
							  ->where('id', $joueur_id)
							  ->get();

		if ($query->num_rows() == 0)
			return;

		$joueur = $query->row();

		// Si il est modérateur ou admin, on l'enlève de la liste des utilisateurs
		$query = $this->CI->db->select('g_moderator')
							  ->from('tobozon_groups')
							  ->where('g_id', $joueur->group_id)
							  ->get();

		if ($query->num_rows() == 0)
			return;

		$groupe = $query->row();
		
		if ($joueur->group_id == Bouzouk::Tobozon_IdGroupeAdmins || $groupe->g_moderator == '1')
		{
			$query = $this->CI->db->select('id, moderators')
								  ->from('tobozon_forums')
								  ->get();
			$forums = $query->result();

			foreach ($forums as $forum)
			{
				$moderators = ($forum->moderators != '') ? unserialize($forum->moderators) : array();

				if (in_array($joueur_id, $moderators))
				{
					unset($moderators[$joueur->username]);
					$moderators = ( ! empty($moderators)) ? serialize($moderators) : 'NULL';

					$this->CI->db->set('moderators', $moderators)
								 ->where('id', $forum->id)
								 ->update('tobozon_forums');
				}
			}
		}
	}

	public function regenerer_cache($nom)
	{
		$fichier = FCPATH.'tobozon/cache/'.$nom;

		if (file_exists($fichier))
			@unlink($fichier);
	}

	public function poster_topic($id, $pseudo, $sujet, $message)
	{
		$time = time();
		
		// On créé le topic
		$data_tobozon_topics = array(
			'poster'  => $pseudo,
			'subject' => $sujet,
			'posted' => $time,
			'last_post' => $time,
			'last_poster' => $pseudo,
			'sticky' => '0',
			'forum_id' => Bouzouk::Tobozon_IdForumJournal
		);
		$this->CI->db->insert('tobozon_topics', $data_tobozon_topics);
		$topic_id = $this->CI->db->insert_id();

		// On créé le post
		$data_tobozon_posts = array(
			'poster' => $pseudo,
			'poster_id' => $id,
			'poster_ip' => '',
			'message' => $message,
			'hide_smilies' => '0',
			'posted' => $time,
			'topic_id' => $topic_id
		);
		$this->CI->db->insert('tobozon_posts', $data_tobozon_posts);
		$post_id = $this->CI->db->insert_id();

		// On met à jour le topic
		$this->CI->db->set('last_post_id', $post_id)
					 ->set('first_post_id', $post_id)
					 ->where('id', $topic_id)
					 ->update('tobozon_topics');

		$this->update_forum(Bouzouk::Tobozon_IdForumJournal);
		return $topic_id;
	}

	public function strip_search_index($post_ids)
	{
		$query = $this->CI->db->select('word_id')
							  ->from('tobozon_search_matches')
							  ->where_in('post_id', $post_ids)
							  ->group_by('word_id')
							  ->get();

		if ($query->num_rows() > 0)
		{
			$word_ids = array();
		
			foreach ($query->result() as $word)
				$word_ids[] = $word->word_id;

			$query = $this->CI->db->select('word_id')
								  ->from('tobozon_search_matches')
								  ->where_in('word_id', $word_ids)
								  ->group_by('word_id')
								  ->having('COUNT(word_id) = 1')
								  ->get();

			if ($query->num_rows() > 0)
			{
				$word_ids = array();
				
				foreach ($query->result() as $word)
					$word_ids[] = $word->word_id;

				$this->CI->db->where_in('id', $word_ids)
							 ->delete('tobozon_search_words');

			}
		}

		$this->CI->db->where_in('post_id', $post_ids)
					 ->delete('tobozon_search_matches');
	}

	public function supprimer_forum($forum_id)
	{
		// On récupère les topics à supprimer
		$query = $this->CI->db->select('id')
						  	  ->from('tobozon_topics')
						  	  ->where('forum_id', $forum_id)
						  	  ->get();
		$topic_ids = array();

		foreach ($query->result() as $topic)
			$topic_ids[] = $topic->id;

		if (count($topic_ids) > 0)
		{
			// On récupère les posts à supprimer
			$query = $this->CI->db->select('id')
								  ->from('tobozon_posts')
								  ->where_in('topic_id', $topic_ids)
								  ->get();

			$post_ids = array();
			
			foreach ($query->result() as $post)
				$post_ids[] = $post->id;

			if (count($post_ids) > 0)
			{
				// On supprime les topics
				$this->CI->db->where_in('id', $topic_ids)
							 ->delete('tobozon_topics');

				// On supprime les souscriptions email
				$this->CI->db->where_in('topic_id', $topic_ids)
							 ->delete('tobozon_topic_subscriptions');

				// On supprime les posts
				$this->CI->db->where_in('id', $post_ids)
						 ->delete('tobozon_posts');

				// On met à jour l'index de recherche
				$this->strip_search_index($post_ids);
			}
		}

		// On cherche les "topics orphelins redirigés" pour les supprimer
		$query = $this->CI->db->select('t1.id')
							  ->from('tobozon_topics t1')
							  ->join('tobozon_topics t2', 't1.moved_to = t2.id', 'left')
							  ->where('t2.id IS NULL AND t1.moved_to IS NOT NULL')
							  ->get();

		if ($query->num_rows() > 0)
		{
			$orphans = array();

			foreach ($query->result() as $orphan)
				$orphans[] = $orphan->id;

			$this->CI->db->where_in('id', $orphans)
						 ->delete('tobozon_topics');
		}

		// On supprime le forum et toutes les permissions associées ainsi que les souscription email
		$this->CI->db->where('id', $forum_id)
					 ->delete('tobozon_forums');
		
		$this->CI->db->where('forum_id', $forum_id)
					 ->delete('tobozon_forum_perms');

		$this->CI->db->where('forum_id', $forum_id)
					 ->delete('tobozon_forum_subscriptions');

		// On régénère le quick jump cache
		$query = $this->CI->db->select('g_id')
							  ->from('tobozon_groups')
							  ->get();

		foreach ($query->result() as $group)							  
			$this->regenerer_cache('cache_quickjump_'.$group->g_id.'.php');
	}

	public function update_forum($forum_id)
	{
		// On va chercher le nombre de topics et le nombre de réponses
		$query = $this->CI->db->select('COUNT(id) AS num_topics, SUM(num_replies) AS num_posts')
							  ->from('tobozon_topics')
							  ->where('forum_id', $forum_id)
							  ->get();
		$forum = $query->row();
		$num_posts = $forum->num_posts + $forum->num_topics;

		// On va chercher le dernier topic
		$query = $this->CI->db->select('last_post, last_post_id, last_poster')
							  ->from('tobozon_topics')
							  ->where('forum_id', $forum_id)
							  ->where('moved_to IS NULL')
							  ->order_by('last_post', 'desc')
							  ->limit(1)
							  ->get();

		// Si il existe
		if ($query->num_rows() == 1)
		{
			$topic = $query->row();
			$this->CI->db->set('num_topics', $forum->num_topics)
						 ->set('num_posts', $num_posts)
						 ->set('last_post', $topic->last_post)
						 ->set('last_post_id', $topic->last_post_id)
						 ->set('last_poster', $topic->last_poster)
						 ->where('id', $forum_id)
						 ->update('tobozon_forums');
		}

		else
		{
			$this->CI->db->set('num_topics', $forum->num_topics)
						 ->set('num_posts', $num_posts)
						 ->set('last_post', null)
						 ->set('last_post_id', null)
						 ->set('last_poster', null)
						 ->where('id', $forum_id)
						 ->update('tobozon_forums');
		}
	}

	public function update_moderateurs_clans($forum_id, $forum_mode, $chef_id, $chef_pseudo)
	{
		$moderateurs = array($chef_pseudo => $chef_id);

		// Les modérateurs modèrent ce nouveau forum si il est ouvert ou fermé
		if ($forum_mode < 3)
		{
			// On récupère les modérateurs tobozon
			$query = $this->CI->db->select('id, pseudo')
							      ->from('joueurs')
							      ->where('(rang & '.Bouzouk::Rang_ModerateurTobozon.')')
							      ->order_by('pseudo')
							      ->get();

			foreach ($query->result() as $moderateur)
				$moderateurs[$moderateur->pseudo] = $moderateur->id;
		}

		// On place le chef modérateur de ce clan
		$this->CI->db->set('moderators', serialize($moderateurs))
				 	 ->where('id', $forum_id)
				 	 ->update('tobozon_forums');

		// On place le chef dans le groupe des chefs de clan, sauf s'il est déjà modérateur ou admin
		$query = $this->CI->db->select('tu.id')
							  ->from('tobozon_users tu')
							  ->join('tobozon_groups tg', 'tg.g_id = tu.group_id')
							  ->where('tu.id', $chef_id)
							  ->where('(tg.g_moderator = 1 OR tg.g_id = 1)')
							  ->group_by('tu.id')
							  ->get();

		if ($query->num_rows() == 0)
		{
			$this->CI->db->set('group_id', Bouzouk::Tobozon_IdGroupeChefsClans)
						 ->where('id', $chef_id)
						 ->update('tobozon_users');
		}
	}
}
