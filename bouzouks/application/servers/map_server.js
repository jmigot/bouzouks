/* Projet      : Bouzouks
 * Description : serveur Node.js pour recevoir et gérer les connexions websocket de la map et appeler les webservices php demandés par les clients
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : décembre 2013
 *
 * Copyright (C) 2012-2014 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */
process.env.DEBUG="*";
// Chargement du plugin 'debug', c'est plus joli ^^
var debug= require('debug')('Vlux_3D');
debug("######## Start ########");
// Variables de configuration
debug("Chargement de la configuration.");
var config={
	//'domaine' : "http://www.bouzouks.net/",
	'domaine' : "http://bouzouks.dev/",
	'port' : "8080",
	'transports': [
    		'websocket'
  		, 'flashsocket'
  		, 'htmlfile'
  		, 'xhr-polling'
  		, 'jsonp-polling'
		],
	'api' : "index.php webservices_map "
	};

debug('Lancement du serveur Vlux 3D');

// Création du serveur vlux
var http_serv = require('http').createServer(handler);

function handler(req, res) {
    res.writHead(301, {"Location": config.domaine});
    res.end();
}

debug("Paramétrage de socket.io");
// Démarage du gestionnaire de socket
var map_serv = require('socket.io').listen(http_serv);

// Configuration du serveur
map_serv.set('transports',config.transports);
debug("Configuration du serveur : ");
debug(config.transports);
debug("Configuration du port du port : "+config.port);
map_serv.listen(config.port);

debug("Configuration du routeur");
// Création du routeur de socket
var router= require('socket.io-events')();

// Paramétrage du routeur
// le premier paramètre désigne le ou l'ensemble d'évent à capturer, regex et wildcard possible
// Le deuxième paramètre désigne les donnée de l'envent ['event_name','data1',...'datan']
// Le dernier argument et un fonction de callback. Ici next() permet de transmettre l'envent.

function que_faire (socket,data,next){
	if (typeof data != "undefined") {
		var api = config['api'];
		var methode = data[0];
		var args = data[1];
		web_service(api,methode,args,socket);
	}
	next();
}
 
// Ajout de la methode d'écoute au router sur tout les events
router.on("*", que_faire);

debug ("Activation du router");
map_serv.use(router);
debug("Serveur Vlux en ligne !");

/**
 * io.on:connection
 *Réponse du serveur lors de la connexion d'un joueur
 * @return void 
*/
map_serv.on('connection', function (socket) {
    // Variables de session
    socket.user_session = {
    	"socket_id" : socket.id,
    	"mode" : 0,
    	"id" : -1,
    	"pseudo" : '',
    	"rang" : 0,
    	"sexe" : 'male',
    	"avatar_img" : '',
    	"avatar_decx" : 0,
    	"avatar_decy" : 0,
    	"map_id": -1,
    	"map_type" : '',
    	'map_x' : 1,
    	"map_y" : 1,
    	"tchat_statut": -1, // timestamp pour le /chut
    	"tchat_derniere_requete" : -1,
    	"disconnected" : 0
    }; // date la dernière requête, permet de limité le nbr de requête
    // Tableau d'autorisation des méthodes. Par défaut, seul l'authentification est autorisée.
    socket.auth_methods = {
    	"deconnexion" : 1,
    	"authentifier": 1,
    	"afficher_map" : 0,
    	"enregistrer_map" : 0,
    	"new_teleport" : 0,
    	"new_dest" : 0,
    	"abort_form_teleport" : 0,
    	"abort_teleport" : 0,
    	"next_gate" : 0,
    	"next_dest" : 0,
    	"create_gate" : 0,
    	"append_dest" : 0,
    	"create_dest" : 0,
    	"add_dest" : 0,
    	"supression_teleport" : 0,
    	"teleportation_request" : 0,
    	"teleportation" : 0,
    	"move_player" : 0,
    	"update_teleport" : 0,
    	"tchat_message" : 0,
    	"avatar_info" : 0,
    	"get_histo" : 0,
    	"suppr_msg" : 0

    };

	debug("Connexion effectué !");
	// Déconnexion auto
	socket.on('disconnect', function(){
		if(this.user_session.disconnected==0){
			_deconnexion(this);
		}
	});
});

function _deconnexion(socket){
		debug ('Déconnexion de la socket '+socket.id);
		web_service(config['api'], 'deconnexion', {'user_session' : encodeURIComponent(JSON.stringify(socket.user_session))}, socket);

}

/**
 *
 *		API BOUZOUKS
 *  
 */
// Chargement du module CLI
var runner = require("child_process");
/**
 * web_service(method, arguments, socket, callback)
 * Permet d'utiliser le webservice bouzouk via la console
 */
function web_service(api,methode,vars,socket){
	var parser ="php ";
	var args = '';
	if(methode == 'disconnect_unload'){
		socket.user_session.disconnected = 1;
		return _deconnexion(socket);
	}
	if(methode == 'afficher_map' || methode == 'teleportation_request' || methode == 'teleportation' || methode == 'tchat_message'){
		args += ' ' + socket.id;
	}
	for (var cle in vars){
		var arg = vars[cle];
		//Si l'argument est une chaine de caractère, on l'échape
		if(typeof arg ==='string'){
			if(methode =="tchat_message" || methode =="suppr_msg"){
				arg = '\"'+(fixedEncodeURIComponent(arg))+'\"';
			}
			else{
				arg = '\"'+arg+'\"';
			}
		}
		args+=' ' + arg;
	}
	// On joint les données de session à la requête
	if(methode == 'tchat_message' || methode=='authentifier' || methode =='teleportation_request' || methode =='teleportation' || methode == 'avatar_info' || methode=='move_player' || methode == 'suppr_msg' || methode == "get_histo" || methode == "afficher_map" || methode == "enregistrer_map"){
		args+=' "' + encodeURIComponent(JSON.stringify(socket.user_session))+'"';
	}
	debug(parser+api+methode+args);
	if(can_i_do(socket, methode)){
		runner.exec(parser+api+methode+args,function(err, rep, stderr){
			if (err !=null){
				debug ('Erreur d\'éxecution php :'+err+" : "+stderr+"\n"+rep);
			}
			// Si le script retourne une réponse, on l'éxecute.
			// Le webservie retourne du code js
			else {
				// Utile pour le stacktrace php
				debug(rep);
				eval(rep);
			}
		});
	}
	else{
		socket.emit('alert', "Cette action n'est pas autorisée. La Bouzopolice est en chemin !!");
	}
}

function can_i_do(socket, methode){
	if(socket.auth_methods[methode]==1){
		return true;
	}
	else{
		return false;
	}
}

function fixedEncodeURIComponent(str){
	return encodeURIComponent(str).replace(/[!'()*]/g, function(c){
		return '%' + c.charCodeAt(0).toString(16);
	});
}

function set_one_client(id, params){
	// Liste des sockets connectées
	var clients = map_serv.sockets.connected;
	for ( var client in clients){
		// La socket correspondant à l'id du joueur
		if(clients[client]['user_session']['id'] == id){
			for ( var prop in params){
				clients[client]['user_session'][prop] = params[prop];
			}
		}
	}
}
