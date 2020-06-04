// jQuery Plugin Boilerplate
// A boilerplate for jumpstarting jQuery plugins development
// version 1.1, May 14th, 2011
// by Stefan Gabos

// remember to change every instance of "vluxMap" to the name of your plugin!
(function($) {

    // here we go!
    $.vluxMap = function(element, options) {

        // Paramètres par défaut
        var defaults = {

            Choix: 0,
            auto_connect : false,
            mode : 'viewer', // configuration auto du plugin viewer|walker|creator
            img_path : '/webroot/images/map/',
            zoom : 0.8,
            mapDrag : true,
            mouseWheel : false,
            mapFixe : false,
            tuiles : '',
            fluid : false,
            nbrTitleSetsSlide : 10,
            
            // Définition des écouteurs
            onFoo: function() {
            	console.log("Foo!");
            }

        };

        // Configuration par défaut du plugin pp3diso
        var map_default = {
        		id : 0,
           		tuiles : '1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1504,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913',// GRande 15x15 pour la présentation
                zone : '',
                mapZones : '',
           		decor : '',
           		items : '',
				img_path: defaults.img_path+"objets/"
            };

        

        // Autres variables
        var plugin = this;
        var ppmap;
        var socket;
        var img_interface = defaults.img_path+'interface/';
        var img_items = defaults.img_path+'objets/';

        // Listes des items disponibles
        var vluxItems = [
        {
            'img' : img_items+'tuile_zone',
            'decx' : 0,
            'decy' : 0,
        }
        ];

        // Paramètrages optionnel
        plugin.settings = {}

        var $element = $(element), // reference to the jQuery version of DOM element
             element = element;    // reference to the actual DOM element

        // constructeur
        plugin.init = function() {
        	var map;
        	console.log("Initialisation vluxMap");

            // the plugin's final properties are the merged default and 
            // user-provided options (if any)
            plugin.settings = $.extend({}, defaults, options);

            zoom = plugin.settings.zoom;
            // Si une socket est requise
            if(plugin.settings.auto_connect){
            	socket = getSocket();
            	map = socket.reponse.mapData;
            	items = socket.reponse.itemsData;
            	decor = socket.reponse.decorData;
            }
            else{
                if(plugin.settings.tuiles !=''){
                    map_default.tuiles = plugin.settings.tuiles;
                }
            	map = map_default;
            	items = '';
            	decor = '';
            }
            // config map
            //On instancie pp3diso
            ppmap = get_ppmap(map)
            // affichage
            load_map(items, decor);

        }

        // public methods
        // these methods can be called like:
        // plugin.methodName(arg1, arg2, ... argn) from inside the plugin or
        // element.data('vluxMap').publicMethod(arg1, arg2, ... argn) from outside 
        // the plugin, where "element" is the element the plugin is attached to;

        // a public method. for demonstration purposes only - remove it!
        plugin.test = function(data) {

            console.log(data);

        }

        // zoom in de la carte
        plugin.zoomPlus = function(){
        		zoom += 0.5;
        		if(zoom > 5) zoom = 5;
        		ppmap.zoomMap(zoom);
        	}

        plugin.zoomMoins = function() {
        		zoom -= 0.5;
        		if(zoom < 0.5) zoom = 0.5;
        		ppmap.zoomMap(zoom);
        	}

        plugin.getZoom = function(){
            return zoom;
        }

        plugin.addItem = function(x, y, z, sprite, decx, decy, id){
            ppmap.addObject(x, y, z, img_items+sprite, decx, decy, id);
        }

        plugin.changeTuile = function(x, y, img){
            ppmap.changeOneMap(x, y, img);
        }

        plugin.get_X = function(x, y){
           return ppmap.getPX(x, y);
        }

        plugin.get_Y = function(x, y){
           return ppmap.getPY(x, y);
        }

        plugin.getZone = function(){
            return ppmap.getZone();
        }

        plugin.changeCursor = function(vluxItemId){
            ppmap.changeCursor(vluxItems[vluxItemId].img+'.png', img_interface+'cursor-off.png', vluxItems[vluxItemId].decx, vluxItems[vluxItemId].decy);
        }

        plugin.getObjects = function(){
            return ppmap.getObjects()
        }

        plugin.getVluxItems = function(){
            return vluxItems;
        }

        plugin.resetMap = function(){
            change_map(map_default);
            if(plugin.settings.mode != 'viewer')
            {
               var objet = this.getObjects();
               for (i=0; i<=(objet.length-1); i++){
                   ppmap.addObject(objet[i].x, objet[i].y, 0, objet[i].sprite, objet[i].decx, objet[i].decy, objet[i].vid);
                   //Todo gestion des zones de collision
               } 
            }
        }

        plugin.moveTo = function(x, y){
            ppmap.moveTo(x, y);
        }
        // private methods
        // these methods can be called only from inside the plugin like:
        // methodName(arg1, arg2, ... argn)

        // a private method. for demonstration purposes only - remove it!
        var foo_private_method = function() {

            // code goes here

        }

        //Création du socket
        var getSocket = function (){
        	switch(site_url){
        		case 'http://www.bouzouks.net/':
        		host='ws://www.bouzouks.net:8080';
        		break;
        		case 'http://bouzouks.dev/':
        		host='ws://bouzouks.dev:8080';
        		break;
        		default :
        		host='ws://localhost:8080';
        	}
        	// Connexion au serveur Vlux
        	socket= io.connect(host);
        }

        // Function d'initialisation de pp3diso
        var get_ppmap = function(param){
        	return $('#ppISO').pp3Diso({
							map : param.tuiles,
							mapId: param.id,				// id de la map
							tx:100,					// dimension x des tuiles
							ty:65,					// dimension y des tuiles
							prefix:'',
							zoom: plugin.settings.zoom,
							path: param.img_path,
                            positionFixe : plugin.settings.mapFixe,
                            drag : plugin.settings.mapDrag,
							auto_resize:true,
                            fluid : plugin.settings.fluid,
                            nbrTitleSetsSlide : plugin.settings.nbrTitleSetsSlide,
							mousewheel: plugin.settings.mouseWheel,
							onmoveavatar:function(x, y, mapId) {
								myClick(x, y, mapId);
							},
							onclicobject: function(x, y, mapId) {
								return true;
							}
						});
        }

        // Peuplement de la cartes.
        var load_map = function(){
        	ppmap.moveMapOn();
        	ppmap.cursor(img_interface+'cursor-on.png', img_interface+'cursor-off.png', 0, -30, 8);
        }

        var change_map = function(param){
            ppmap.reload(param.tuiles, param.zone, param.mapZones, param.id)
        }

        // Lancement du plugin
        plugin.init();

    }

    // add the plugin to the jQuery.fn object
    $.fn.vluxMap = function(options) {

        // iterate through the DOM elements we are attaching the plugin to
        return this.each(function() {

            // if plugin has not already been attached to the element
            if (undefined == $(this).data('vluxMap')) {

                // create a new instance of the plugin
                // pass the DOM element and the user-provided options as arguments
                var plugin = new $.vluxMap(this, options);

                // in the jQuery version of the element
                // store a reference to the plugin object
                // you can later access the plugin and its methods and properties like
                // element.data('vluxMap').publicMethod(arg1, arg2, ... argn) or
                // element.data('vluxMap').settings.propertyName
                $(this).data('vluxMap', plugin);
            }

        });

    }

})(jQuery);

// Lancement de vluxMap.js
$(document).ready(function()
{
    item_type =($('input[name=type]').val());
    if(item_type == 'sols'){
        map = '1431181913,1431181874,1431181913:1431181874,1504,1431181874:1431181913,1431181874,1431181913';
        cX = 2;
        cY = 2;
    }
    else{
         //map ='1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1504,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913';
        map='1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1504,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913:1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874:1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913,1431181874,1431181913';
        cX = 6;
        cY = 10;
    }
	//Instanciation du plugin
	Map = $('#map-index').vluxMap({
        tuiles : map
    });
	Map = $('#map-index').data('vluxMap');
	// Usage example
	/*
	Map = $('#map-index').vluxMap({
		auto_connect : false,
		mode : 'viewer',
		zoom : 0.8 //zoom par défault pour le plugin pp3diso
	});
	Map.test('toto');
    */
    Choix = 0;
    item_type =($('input[name=type]').val());
    itemZone = [];
    // Affichage de la zone de collision pour les objets déjà existant
    display_zone();
    Map.moveTo(cX,cY);
 
});

function set_range(axe, value)
{
    console.log(axe+' '+value);
    if(axe =='x'){
        var x = Map.get_X(cX, cY)+ (value * Map.getZoom());
        $('#o_1').css('left', ~~x);
        $('#decx_number').val(value);
    }
    if(axe =='y'){
        var y = Map.get_Y(cX, cY)+ (value * Map.getZoom());
        $('#o_1').css('top', ~~y);
        $('#decy_number').val(value);
    }
}

function set_number(axe, value)
{
    if(axe ==='x'){
        var x = Map.get_X(cX, cY)+(value *Map.getZoom());
        $('#o_1').css('left', ~~x);
        $('#decx_range').val(value);
    }
    if(axe =='y'){
        var y = Map.get_Y(cX, cY)+ (value * Map.getZoom());
        $('#o_1').css('top', ~~y);
        $('#decy_range').val(value);
    }
}

function set_zone(itemZone)
{
    $('input[name="zone"]').val(JSON.stringify(itemZone));
}

// Affichage de l'item au choix de l'image
$('#itemfile').bind('change', function()
{
    var file = this.files[0];
    console.log(file.type);
    if(file.type != 'image/png'){
        $().toastmessage('showWarningToast', "Patwon, le fichier doit être une image ! Et du png bodwel !!!");
    }
    else{
        var reader = new FileReader();
        reader.addEventListener('load', function()
        {
            var imgSrc = this.result;
            var myImg = new Image();
            myImg.src = this.result;
            myImg.onload = function ()
            {
                console.log(myImg.width);
                console.log(myImg.height);
                var imgWidth = myImg.width;
                var imgHeight = myImg.height;
                //TODO condition sur le type d'objet : si sols, changeOneMap()
                if(item_type =='sols')
                {
                    $('#c_'+cX+'_'+cY).css('background-image', 'url('+imgSrc+')');
                }
                else
                {
                    if($('#o_1').length)
                    {
                        $('#o_1 img').attr('src', imgSrc).css({ height: imgHeight, width: imgWidth});
                    }
                    else
                    {
                        Map.addItem(cX, cY, 0, imgSrc, 0, 0, 0);
                        $('#o_1 img').attr('src', imgSrc)
                    }
                }     
            }
        });
        reader.readAsDataURL(file);
    }
});

// Selection de l'outil de définition de zone
function getTool()
{
    if($('#o_1').length)
    {
        $('#o_1 img').fadeTo(300, .6);
        Map.changeCursor(0);
        Choix = 1;
    }
    else
    {
        alert("Faut d'abord choisir une image ><");
    }
}

function myClick (x, y, mapId)
{
    var dummy = true;
    if(itemZone.length >0)
    {
        for(var i=0; i<=(itemZone.length-1); i++)
        {
            if(itemZone[i].x==(x-cX) && itemZone[i].y==(y-cY))
            {
                dummy = false;
            }
        }
    }
    if(dummy && Choix==1){
        Map.changeTuile(x, y, 'tuile_zone');
        itemZone.push({'x' :(x-cX),'y': (y-cY)});
        set_zone(itemZone);
    }
}

function display_zone()
{
    var dummy = $('input[name="zone"]').val();
    if(dummy!='' && item_type !='sols')
    {
        itemZone = JSON.parse(dummy);
        for(var i=0; i<=(itemZone.length-1); i++){
            Map.changeTuile((itemZone[i].x)+cX, (itemZone[i].y)+cY, 'tuile_zone');
        }
    }  
}

// Remise à zéro de la zone de collision
function reset_zone(){
    //Si l'objet existe
    if($('#o_1').length)
    {
        var imgSrc = $('#o_1 img').attr('src');
        var x = $('#decx_number').val();
        var y = $('#decy_number').val();
        // Raz de la map
        Map.resetMap();
        // Repositionnement de l'item
        Map.addItem(cX, cY, 0, imgSrc, x, y, 0);
        $('#o_1 img').attr('src', imgSrc);
        // vidange du tableau de zone
        itemZone = [];
        set_zone(itemZone);
        Map.moveTo(cX, cY);
    }
    
}



