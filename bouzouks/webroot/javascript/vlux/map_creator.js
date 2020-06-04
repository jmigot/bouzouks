$(document).ready(function()
{
	if ( ! window.WebSocket)
	{
		alert('Ton navigateur ne supporte pas les WebSocket, tu ne peux pas visualiser la carte');
		return;
	}
	else if(typeof(io) == 'undefined'){alert('La map n\'est pas disponible');}
// Définition des variables
socket = null;
ppmap = null;
var host = null;
var args = {};

//Gestion formulaire
$(document).on('keypress', function(e){
	if(e.keyCode == 13){
		e.preventDefault();
		e.stopPropagation();
		$().toastmessage('showWarningToast', "Vous devez utilisez le bouton pour valider le formulaire !!");
		return false;
	}
});

$(document).on('submit', function(e){
	e.preventDefault();
	return false;
});

try{
	
	var host = site_url.replace('http', 'ws');
	host = host.substring(0, host.length-1)+':8080';
	// Connexion au serveur Vlux
	socket= io.connect(host);

	// Lorsque la connexion est établie, on identifie le joueur
	socket.on('connect', function () {

		// Préparation du signal à émettre qui correspond à un méthode du webservice
		var method = 'authentifier';
		var args = {"id":id,"token": websocket_auth, "mode":2};
		socket.emit(method,args);
	});

	socket.on('disconnect', function(){
		console.log("Déconnexion du serveur node");
	});

	socket.on('error', function (){
		alert('Le serveur est inaccessible. Veuiller en référer à un administrateur.');
	});

    // Le joeur est authentifié, on lance la map
    socket.on("authentifie",function(rep){
    	if(rep >= 1){
    	// Le joueur est authentifié, il demande l'affichage de la map
    	var data = {
    		"id" : map_id,
    		"type" : map_type,
    		"bouzouk" : null
    		};
    	socket.emit('afficher_map', data);
    }
    });

	socket.on('alert',function(msg){
		alert(msg);
	});

	socket.on('afficher_map',function(data){
    	afficher_carte(data);
	});

	socket.on('message', function(msg){
		var type = msg[0];
		var message = msg[1];
		if (type != null){
			switch (type){
				case 'notice' :
					type = 'showNoticeToast';
					break;

				case 'succes' :
					type = 'showSuccessToast';
					break;
				case 'alerte' :
					type = 'showWarningToast';
					break;
				default :
					type = 'showErrorToast';
					break;
			}
			$().toastmessage(type, message);
		}
	});

	socket.on('afficher_form_porte', function(data){
		$().toastmessage('showToast',{
			text : data,
			sticky : true,
			type : 'notice',
			close : function(){
				ppmap.killObject(teleport.id);
				lock_menu = false;
				socket.emit('abort_form_teleport', true);
				delete gate_step;
			}
		});
		lock_menu = true;
		$('.type.vlux_display_on').toggleClass('vlux_display_on').toggleClass('vlux_display_off');
		$('.cat.vlux_display_on').toggleClass('vlux_display_on').toggleClass('vlux_display_off');
        reset_cursor();
	});

	socket.on('abort_gate', function(data){
		ppmap.killObject(data);
		lock_menu = false;
		delete gate_step;
	});

	socket.on('change_map', function(data){
		change_map(data);
		$('#menu_annul').toggleClass('hidden', true);
		$('#menu_std').toggleClass('hidden', false);
		lock_menu = false;
	});

	socket.on('arrival_gate', function(data){
		change_cursor(data[0]);
		teleport.cache_key = data[1];
		teleport.depart = map_id;
		teleport.dest = data[2];
		$('#menu_annul').toggleClass('hidden', false);
		$('#menu_std').toggleClass('hidden', true);
	});

}

catch(e) {
	console.log(e);
}

});

var Choix = 0;
var oldChoix = 0;
var lock_menu = false;
var teleport = {
	depart: map_id,
	dest: 0,
	id : null,
	x: null,
	y: null,
	type : null
};
var mask;
var maxH = 10;
var cat_slider = false;

function zoom_plus() {
    zoom += zoom_pas;
    if(zoom > zoom_max) zoom = zoom_max;
    ppmap.zoomMap(zoom);
}

function zoom_moins() {
    zoom -= zoom_pas;
    if(zoom < zoom_min) zoom = zoom_min;
    ppmap.zoomMap(zoom);
}

function change_cursor(n) {
	ppmap.changeCursor(img_item+'/'+items[n]['img']+'.png', img_interface+'cursor-off.png', parseInt(items[n]['decx']), parseInt(items[n]['decy']));
	oldChoix = Choix;
	Choix = n;
}

function reset_cursor(){
	ppmap.changeCursor(img_interface+'cursor-on.png', img_interface+'cursor-off.png', 0, -30, 8);
	Choix = 0;
	iCan = true;
}

function myClick(x, y, MapId) {
	console.log('myclick');
	if(!iCan){
		$().toastmessage('showNoticeToast', "Tu ne peux pas poser cet objet ici !");
		return true;
	}
	if(Choix != 0){
		var item = items[Choix];
		var type = item['type'];
		var cat = item['cat'];
	}
	//Sols
	if (type == 'sols'){
		ppmap.changeOneMap(x, y, item['img']);
		nZone[x][y] = items[Choix].nature;
	}
	//Items
	else if (type =='exterieur' || type =='interieur'){
		addObject(x, y, cursorAlti, item);
		if(items[oldChoix].nom =="déplacer"){
			change_cursor(oldChoix);
		}
	}
	//Portes
	else if(cat =='portes'){
		// on récupère le contenu de la case
		var objet = getObjet(x, y);
		if(objet){
			objet = objet[0];
		}
		// Si une objet est déjà sur la case et que c'est un téléport
		if( !!objet && items[objet.vid].cat == "portes"){
			// Demande formulaire
			console.log("ajout dest");
			new_dest(x, y, items[Choix]['img']);
			return false;	
		}
		// Sinon, on prévient le joueur que ce n'est pas possible 
		else if (!!objet && items[objet.vid].cat != "portes"){
			$().toastmessage('showNoticeToast', "Vous ne pouvez pas poser de téléport ici !");
			return false;
		}
		else if (items[oldChoix].nom =="déplacer"){
			addObject(x, y, 0, item);
			update_teleport(x, y);
			console.log('déplacement de tp !');
			change_cursor(oldChoix);
		}
		else{
			addObject(x, y, 0, item);
			new_teleport(x, y, items[Choix]['img']);
		}
			
	}
	// Gommer/ Déplacer
	else if(items[Choix]['nom'] == 'gomme' || items[Choix]['nom'] == 'déplacer'){
		var object_a = getObjectOneTile(x, y);
		if(object_a){
			var object = object_a.pop();
			var oZ = JSON.parse(object.zone);
			var oZl = oZ.length;
			var dropedOver = false;
			// Si l'objet à supprimer fait plus d'une case, on vérifie que rien ne soit poser dessus
			if(oZl>1){
				dropedOver = getItemZone(object.x, object.y, cursorAlti, oZ, oZl);
			}
			if(!dropedOver){
				killObject(object);
				if(items[Choix]['nom'] == 'déplacer'){
					change_cursor(object.id);
					if(object.cat == 'portes'){
						teleport.x = x;
						teleport.y = y;
					}
				}
			}
			else if(items[Choix]['nom'] == 'gomme' ){
				$().toastmessage('showNoticeToast', "Tu ne peux pas supprimer cet objet car d'autres sont posés dessus.");
			}
			else if(items[Choix]['nom'] == 'déplacer'){
				$().toastmessage('showNoticeToast', "Tu ne peux pas déplacer cet objet car d'autres sont posés dessus.");
			}
		}
	}
	// Choix bizarre, on fait rien et pis c'est tout ><
	else{
		console.log("Choix : "+Choix);
	}
}

// Lorsqu'on clique sur un objet
function myClickObject(x, y, mapId){
	var vluxMapObjects = ppmap.getObjects();
	var vmol = vluxMapObjects.length;
	console.log('myClickObject');
	for (var imo = 0; imo < vmol; imo++ ){
		var objet = vluxMapObjects[imo];
		if(typeof(objet) != 'undefined'){
			if( x == objet.x && y == objet.y ){
				if(items[Choix]['nom'] == 'gomme'){
					// Si l'item est une porte
					if(items[objet.vid].cat == "portes"){
						//Envoie de la requête au webservice
						console.log("Téléport à effacer :");
						console.dir(objet);
						data = {
							'map_id' : map_id,
							'x' : objet.x,
							'y' : objet.y
						};
						socket.emit('supression_teleport', data);
					}
				}
				else{
					console.log("Non !! Prends la gomme !");
				}
			}
		}
	}
}

function get_map_data(){
	monde = ppmap.getMonde();
	// Formatage du retour de la fonction pour le webservice.
	var tuiles="";
	var l =monde.length-1;
	var h = monde[1].length-1;
	for(y = 1;y <= h; y++) {
		if (y>1) {
			tuiles = tuiles + '-';
		}
		for(x = 1; x <= l; x++){
			if(x>1){
				tuiles = tuiles + '_';
			}
			tuiles = tuiles + monde[x][y];
		}
	}
	var data = {
		"map_id" : map_id,
		"map_tuiles" : tuiles
		};

	// Les objets de la map
	var vluxMapObjects = ppmap.getObjects();
	var lmi = vluxMapObjects.length;
	if(lmi!=0){
		var decor="";
		for (var a =0; a<lmi; a++){
			var it= vluxMapObjects[a];
			if(a>0 && typeof(it)!='undefined'){
				decor = decor+"-";
			}
			if(typeof(it)!='undefined'){
				decor = decor+it['x']+"_"+it['y']+"_"+it['z']+"_"+it['vid'];
			}
		}
		if(decor.charAt(0)=='-'){
			decor = decor.substring(1, decor.length);
		}
		data['map_objets'] = decor;
	}
	else{
		data['map_objets'] = 0;
	}
	return data;
}

function enregistrer_map() {
	var data = get_map_data();
	//Permet le blocage de la sauvegarde pendant le process de création de téléport
	if(typeof(gate_step) === 'undefined'){
		console.log('save map !');
		socket.emit('enregistrer_map',data);
	}
}

function update_teleport(x, y){
	console.log('update tp!');
	var data = get_map_data();
	var decor = data['map_objets'];
	socket.emit('update_teleport', {'old_x': teleport.x, 'old_y' : teleport.y, 'nex_x' : x, 'new_y' : y, 'map_id' : map_id, "decor": decor});
}

//Demande du formulaire d'ajout de téléport
function new_teleport( x, y, type){
	var vluxMapObjects = ppmap.getObjects();
	var vmol = vluxMapObjects.length;
	// Si les coordonnée du téléport correspondent, on récupère l'id
	for (var imo = 0; imo < vmol; imo++ ){
		 var objet = vluxMapObjects[imo];
		 if(typeof(objet) != 'undefined'){
			if( x == objet.x && y == objet.y ){
				// On récupère l'id de la porte pour une possible supression
				teleport.id = objet.id;
				teleport.x = x;
				teleport.y = y;
				teleport.type = type;
			}
		}
	}
	// envoie de la requête au web service.
	var data = {
		"id_bouzouk" : id,
		"map_id" : map_id
		};
	 if(typeof(gate_step) =='undefined'){
		socket.emit('new_teleport', data);
		gate_step=1;
	}
	// Le téléport de départ est une création
	else if(gate_step == 1){
		socket.emit('create_gate', teleport);
		delete gate_step;
	}
	// Le téléport de dépard existe déjà
	else if(gate_step == 2){
		socket.emit('append_dest', teleport);
		delete gate_step;
	}
	else {
		console.log("Erreur formulaire : "+gate_step)
	};
}

function next_gate(pop){
	var form = document.forms["new_teleport"];
	//Récupération des données
	teleport.dest = form['destination'].value;
	//Check formulaire
	if(teleport.nom != '' && teleport.dest != 0){
		//fermeture du popup
		var wrapper = $(pop).parent().parent().parent();
		$().toastmessage('removeToast',wrapper,{'close':null});
		//envoie au webservice
		socket.emit('next_gate', teleport);
	}
	if(teleport.nom ==''){
		$().toastmessage('showWarningToast',"Vous devez donner un nom !");
	}
	if(teleport.dest ==0){
		$().toastmessage('showWarningToast',"Vous devez choisir une destination !");
	}
}

function new_dest(x, y, type){
	//Le téléport de départ existe
	if(typeof(gate_step)=='undefined'){
		teleport.x = x;
		teleport.y = y;
		teleport.type = type;
		socket.emit('new_dest', {"map_id":map_id, "x" :x, "y":y, "id":id});
	}
	//Le téléport de départ est une création
	else if(gate_step == 1){
		
		var cache_key = teleport.cache_key;
		socket.emit('create_dest', {"map_id":map_id, "x":x, "y":y, "ck":cache_key});
		delete gate_step;
	}
	//Le deuxiéme téléport existe également
	else if(gate_step == 2){
		var cache_key = teleport.cache_key;
		socket.emit('add_dest', {"mp_id":map_id, "x":x, "y":y, "ck":cache_key});
		delete gate_step;
	}
	else{
		console.log('Erreur de add dest');
	}
	
}

function next_dest(pop){
	var form = document.forms["new_dest"];
	var data = {
		'depart' : map_id,
		'x' : teleport.x,
		'y' : teleport.y,
		'type' : teleport.type,
		'destination' : form['destination'].value
	}
	console.log(data);
	//fermeture du popup
	var wrapper = $(pop).parent().parent().parent();
	$().toastmessage('removeToast',wrapper,{'close':null});
	//envoie au webservice
	socket.emit('next_dest', data);
	gate_step = 2;
}

function afficher_carte(data){
	console.time('vlurx');
	zoom_min = parseFloat(data.config.zoom_min);
	zoom_max = parseFloat(data.config.zoom_max);
	zoom_pas = parseFloat(data.config.zoom_pas);
	img_path = data.config.img_path;
	img_interface = img_path+"interface/";
	img_item = img_path+"objets";
	tx = 100;
	ty = 65;
	ppmap = $('#ppISO').pp3Diso({
		map : data.map.tuiles,
		mapId:data.map.id,				// id de la map
		nbrTitleSetsSlide:data.config.map_slide,		// pas de mouvement de la map lorsque l'on click dessus
		tx:100,					// dimension x des tuiles
		ty:65,					// dimension y des tuiles
		prefix:data.config.img_prefix,
        zoom: data.config.zoom_default,
        zoom_min : zoom_min,
        zoom_max : zoom_max,
        zoom_pas : zoom_pas,
		path: data.config.img_path+"objets/",
		auto_resize:true,
        mousewheel:parseInt(data.config.mousewheel),
        cursorDelay : parseInt(data.config.cursor_delay),
        cursorZindex : parseInt(data.config.cursor_z_index),
        fluid : false,
        nbrTitleSetsSlide : 10,
		onmoveavatar:function(x, y, mapId) {
			myClick(x, y, mapId);
		},
		onclicobject: function(x, y, mapId) {
			myClickObject(x, y, mapId);
		}
	});
	// Chargement de la map
	load_map(data);
	console.timeEnd('vlurx');
}

// Peuplement de la cartes et définition de variables usuelles
function load_map(data){
	map_id= data.map.id;
	teleport['depart'] = map_id;
	items = data.items;
	decor = data.map.decor;
	zoom = parseFloat(data.config.zoom_default);
	mapSize = parseInt(data.map.size);
	oZone =  new Array(mapSize+1);
	for ( var oz=0; oz<=10; oz++){
		oZone[oz] = new Array(10);
		for(var ox=1; ox<=mapSize; ox++) {
			oZone[oz][ox] = new Array(mapSize+1);
			for(var oy=1; oy<=mapSize; oy++){
				oZone[oz][ox][oy] = new Array();
			}
		}
	}
	Choix = 0;
	cursorAlti = 0;
	iCan = true;
	mask = false;
	if(map_type=='interieur'){
		maxH = 4;
	}
	// Chargement des éléments de la map
	for( var b =0; b<decor.length; b++){
        var item = decor[b];
        var item_id = decor[b].vid;
        addObject(parseInt(item.x), parseInt(item.y), parseInt(item.z), items[item_id]);
    }
    // On établie la carte de nature du sol
    nZone = makeNatMap();
    // Affichage
    ppmap.moveMapOn();
    ppmap.cursor(img_interface+'cursor-on.png', img_interface+'cursor-off.png', 0, -30, 8);
    ppmap.moveTo(Math.floor(mapSize/2), Math.floor(mapSize/2));
}

function change_map(data){
	var map = data.map.tuiles;
	var mapId = data.map.id;
	var zone = '';
	var mapZones = '';	
	ppmap.reload(map, zone, mapZones, mapId);
	// Peuplement de la map
	load_map(data);
}

//Fonction d'annulation de l'action en cours
function abort(){
	if(typeof(teleport.cache_key)!= 'undefined'){
		console.log(teleport.cache_key);
		socket.emit('abort_teleport', {'cache_key' :teleport.cache_key});
		delete gate_step;
	}
}

// Modification de la carte des objets
function  changeState(id, item, x, y, z){
	if(typeof(z) =='undefined'){ z=0; }
	var zItem = {
		'id':id,
		'support': JSON.parse(item.support),
		'nature' : item.nature
	};
	var itemZone = JSON.parse(item.zone);
	var iH = parseInt(item.hauteur)+z;
	var itl = itemZone.length;
	// Les items qui n'ont pas d'épaisseur remplisse tout de même un cube.
	if(iH == z){ iH = 1+z};
	for( var iz=0; iz<itl; iz++){
		var zx=(x+itemZone[iz].x);
		var zy=(y+itemZone[iz].y);
		for (var zz = z; zz<iH; zz++){
			oZone[zz][zx][zy].push(zItem);
		}
	}
}

// Gestion de contrôle des items
function myCursorDelay(x, y){
	var oldICan = iCan;
	// Un outil en main
	if(Choix !=0 && items[Choix].nom != 'gomme' && items[Choix].type != 'sols' && items[Choix].nom !='déplacer'){
		var item = items[Choix];
		var checkZone = true;
		var checkDrop = true;
		var checkAlti = true;
		if(item.zone){
			var itemZone = JSON.parse(item.zone);
			// Si l'objet fait plus d'une case
			if(itemZone.length>1){
				checkZone =verif_zone(x|0, y|0, itemZone, item.dropable, item.water_dropable);
			}
			// Si l'objet fait une case
			else{
				checkDrop = verif_drop(x, y, item);
			}	
		}
		if(cursorAlti+(item.hauteur|0)>maxH){
			checkAlti = false;
		}
		// Si une impossibilité est levée, on bloque la pose et on modifie le cursor
		if(!checkZone || !checkDrop || !checkAlti){
			iCan = false;
		}
		else{
			iCan = true;
		}
		if(oldICan != iCan){
			if(!iCan){
				setCursorOpacity(0.5);
			}
			else{
				setCursorOpacity(1);
			}
		}
	}
	// Gestion opacité des objets devant le curseur
	gestionMask((x|0),(y|0), cursorAlti);
}

function verif_zone(x, y, itemZone, dropable, water_dropable){
	var iizl = itemZone.length;
	var ix, iy;
	for(var iiz=0; iiz<iizl; iiz++){
		ix = itemZone[iiz].x+x;
		iy = itemZone[iiz].y+y;
		// Si l'objet déborde en x
		if(ix <= 0){
			console.log('dépassement NO!');
			return false;
		}
		else if(ix > mapSize){
			console.log('dépassement SE !');
			return false;
		}
		// Si l'objet déborde en y
		else if(iy <= 0){
			console.log('dépassement NE !');
			return false;
		}
		else if(iy > mapSize){
			console.log('dépassement SO !');
			return false;
		}
		// Si une tuile "eau" est dans la zone
		if(nZone[ix][iy]=="eau"){
			console.log('plouf zone !');
			if(!water_dropable){
				return false;
			}
		}
	}

	// Si un objet est présent dans la zone 
	if(cursorAlti==0){
		var cz = 0;
	}
	else{
		var cz = cursorAlti-1;
	}
	var supports = getItemZone(x, y, cz, itemZone, iizl);
	if(supports){
		//var support = getSupport(ix, iy, cursorAlti);
		//console.log(support);
		// L'item est-il droppable ?
		if(!dropable){
			console.log('undropable !!');
			return false;
		}
		else{
			// On vérifie que le support est suffisemment grand
			// Si oui, on vérifie que l'item à poser ne déborde pas.
			//var objet_id = ppmap.getObjetOnCase(x, y);
			return false;
		}
	}
	return true;
}

function verif_drop(x, y, item){
	// S'il n'y a pas d'objet sur la case
	if(!getObjectOneTile(x,y)){
		if(nZone[x][y]=="eau"){
			console.log('water');
			if(!item.water_dropable){
				return false;
			}
			else{
				return true;
			}
		}
	}
	else{
		// L'objet est-il dropable ?
		if(!item.dropable && item.cat !='portes'){
			console.log(" verif_drop : undropable !");
			return false;
		}
		// L'objet est une porte
		else if(item.cat =='portes'){
			var support_cat = getItem(x, y)[0].cat;
			if(support_cat != 'portes'){
				console.log('pas un tp');
				return false;
			}
			else{
				return true;
			}
		}
		// L'objet présent sur la case est-il un support ?
		else{
			var support = getItem(x, y, cursorAlti-1).pop();
			if(!support.support){
				console.log('not support')
				return false;
			}
			// L'objet présent est un tp
			else if(support.cat == 'portes'){
				console.log('pas sur tp');
				return false;
			}
		}
	}
	return true;	
}

function makeNatMap(){
	var nMap = ppmap.getMonde();
	var itmx=1, itmy=1;
	nml = nMap.length;
	// Pour chaque tuiles de la map
	for( itmx; itmx<nml; itmx++){
		for( itmy; itmy<nml; itmy++){
			for(var itm in items){
				if(items[itm].img == nMap[itmx][itmy]){
					nMap[itmx][itmy] = items[itm].nature;
				}
			}
		}
	}
	return nMap;
}

function setCursorOpacity(opacity){
	$('#pp3diso-cursor').css('opacity', opacity);
}

// Renvoie l'objet au sein de ppmap
function getObjet(x, y, z){
	if(x<=0 || x>mapSize || y<=0 || y>mapSize || z>10 || z<0){
		return false;
	}
	if(typeof(z) =='undefined'){ z=0; }
	var ozl = oZone[z][x][y].length;
	var dummy = [];
	for(var i=0; i<ozl; i++){
		dummy.push(ppmap.getObjet(oZone[z][x][y][i].id));
	}
	if(dummy.length==0){
		return false;
	}
	else{
		return dummy;
	}
}

// Renvoie l'objet avec les info du tableau items
function getItem(x, y, z){
	if(typeof(z) =='undefined' || z<0){ z=0; }
	var objet_array = getObjet(x,y, z);
	if(objet_array){
		var oal = objet_array.length;
		var dummy_a = [];
		for(var i=0; i<oal; i++){
			console.log(objet_array[i]);
			if(typeof objet_array[i] != 'undefined'){
				var item = JSON.parse(JSON.stringify(items[objet_array[i].vid]));
				item.x = objet_array[i].x|0;
				item.y = objet_array[i].y|0;
				item.z = objet_array[i].z|0;
				item.ppId = objet_array[i].id;
				dummy_a.push(item);
			}
		}
		return dummy_a;
	}
	else{
		return false;
	}
}

// Renvoie tout les objet à la verticale d'une case
function getObjectOneTile(x, y){
	var alti = 0;
	var objects = [];
	var object;
	while(alti <= 10){
		object_array = getItem(x, y, alti);
		if(object_array){
			var oal = object_array.length;
			var alti_case = 0;
			for(i=0; i<oal; i++){
				alti_case += object_array[i].hauteur|0;
				objects.push(object_array[i]);
			}
			if(alti_case == 0){
				alti = alti+1;
			}
			else{
				alti += alti_case;
			}
		}
		else{
			alti++;
			//break;
		}
	}
	if(objects.length >0){
		return objects;
	}
	else{
		return false;
	}
}

function getMaxAlt (x, y){
	var objects = getObjectOneTile(x, y);
	var maxAlt = 0;
	var i =0;
	var il = objects.length;
	for(i; i<il; i=i+1){
		maxAlt += (objects[i].hauteur)|0;
	}
	return maxAlt;
}

function getItemZone(x, y, z, zone, zonelength){
	var dummy_o = [];
	var o_objets = [];
	var o_objet_array =[];
	var ax, ay;
	for(var az=0; az<zonelength; az++){
		ax = zone[az].x+x;
		ay = zone[az].y+y;
		o_objet_array = getItem(ax, ay, z);
		ooal = o_objet_array.length;
		for(var i=0; i<ooal; i++){
			var o_objet = o_objet_array[i];
			if(o_objet && dummy_o.indexOf(o_objet.ppId)==-1){
				o_objets.push(o_objet);
				dummy_o.push(o_objet.ppId);
			}
		}
	}
	if(o_objets.length>0){
		return o_objets;
	}
	else{
		return false;
	}
}

function getMaxAltZone(x, y, zone, zonelength){
	var ma = getMaxAlt(x, y);
	var dx, dy;
	for(var ima =0; ima<zonelength; ima++){
		dx = zone[ima].x+(x|0);
		dy = zone[ima].y+(y|0);
		dummy_ma = getMaxAlt(dx, dy);
		if(dummy_ma && dummy_ma > ma){
			ma = dummy_ma;
		}
	}
	return ma;
}

function gestionMask(x, y, z){
	var oldMask = mask;
	mask = getMask(x,y,z);
	// Il y a un qqch devant le curseur
	if(mask){
		// Il n'y avait pas de masque précédemment
		if(!oldMask){
			var ml = mask.length;
			var im =0;
			for(im; im<ml; im++){
				var idobj = '#o_' + mask[im]+ ' img';
				$(idobj).css("opacity", 0.15);
			}
		}
		else if(oldMask.length>0){
			var iom=0;
			var oml = oldMask.length;
			for(iom; iom<oml; iom++){
				if(mask.indexOf(oldMask[iom])==-1 ){
					var idobj = '#o_' + oldMask[iom]+ ' img';
					$(idobj).css("opacity", 1);
				}
			}
			var im=0;
			var ml= mask.length;
			for(im; im<ml; im++){
				if(oldMask.indexOf(mask[im]) ==-1){
					var idobj = '#o_' + mask[im] + ' img';
					$(idobj).css("opacity", 0.15);
				}
			}
		}
			
	}
	// Il n'y a rien
	else if(!mask && oldMask.length>0){
		var oml = oldMask.length;
		var im=0;
		for(im; im<oml; im ++){
			var idobj =  '#o_' + oldMask[im] + ' img';
			$(idobj).css("opacity", 1);
		}
	}	
}

// Retourne tous les objets qui masquent une case
function getMask(x, y, z){
	if(typeof(z)=='undefined'){
		z=0;
	}
	var dummy_mask = [];
	var oneMask_array = false;
	var oneMask = false;
	var c= 0;
	while ( z<=10){
		var iz = 0;
		for(iz; iz<=1; iz=iz+1)
		{
			var ix=0;
			for(ix; ix<=1; ix=ix+1)
			{
				var iy=0;
				for(iy; iy<=1; iy=iy+1)
				{
					if((ix==0 && iy==0 && iz==0)||(z+iz>10)){
						continue;
					}
					oneMask_array = getItem(x+c+ix, y+c+iy,z+iz);
					// Si un objet est dans la case
					if(oneMask_array){
						omal = oneMask_array.length;
						for(var i=0; i<omal; i++){
							oneMask = oneMask_array[i]
							//Si l'objet est au sol et n'a pas de hauteur
							if(oneMask.hauteur == 0 && iz==0){
								continue;
							}
							// Si l'objet n'est pas présent dans le masque
							if(dummy_mask.indexOf(oneMask.ppId) == -1){
								// On l'ajoute au tableau 
								dummy_mask.push(oneMask.ppId);
							}
						}
						
					}
				}
			}
		}
		z=z+1;
		c =c+1;
	}
	if(dummy_mask.length >0){
		return dummy_mask;
	}
	else{
		return false;
	}
}

function addObject(x, y, z, item){
	// Si l'objet dépasse la hauteur max
	if(z+(item.hauteur|0)>maxH){
		$().toastmessage('showNoticeToast', "Hauteur max autorisée : "+maxH+1);
		return;
	}

	var mid = ppmap.addObject(x, y, z, img_item+'/'+ item['img']+'.png', item['decx'], item['decy'],item.id);
	// On établie la carte des objets
	changeState(mid, item, x, y, z);
}

function killObject(objet){
	// On supprime l'objet de la map
	ppmap.killObject(objet.ppId);
	// Mise à jour du tableau oZone
	var iZone = JSON.parse(objet.zone);
	var zz = objet.z|0;
	var ith = objet.hauteur|0;
	if(ith==0){ith=1;}
	ith =ith+zz;
	var izl = iZone.length;
	// Mise à jour du tableau oZone
		for(zz; zz<ith; zz++){
			for( var iz=0; iz<izl; iz++){
				var zx=(parseInt(objet.x)+iZone[iz].x);
				var zy=(parseInt(objet.y)+iZone[iz].y);
				oZone[zz][zx][zy].pop();
		}
	}
}
