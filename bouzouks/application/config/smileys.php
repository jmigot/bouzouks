<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| SMILEYS
| -------------------------------------------------------------------
| This file contains an array of smileys for use with the emoticon helper.
| Individual images can be used to replace multiple simileys.  For example:
| :-) and :) use the same image replacement.
|
| Please see user guide for more info:
| http://codeigniter.com/user_guide/helpers/smiley_helper.html
|
*/

$smileys = array(

//	smiley			image name						            width height	alt           secret title

	':argh:'        => array('argh.gif',                        '20', '20',     'argh',       '0', 'Se retiens de péter un cable'),
	':classe:'      => array('classe.gif',                      '20', '20',     'classe',     '0', 'Fais le kéké'),
	':grrr:'        => array('grrr.gif',                        '20', '20',     'grrr',       '0', 'En colère'),
	':happy:'       => array('happy.gif',                       '20', '20',     'happy',      '0', 'Content'),
	':hey:'         => array('hey.gif',                         '20', '20',     'hey',        '0', 'Mais ça va pas ou quoi ?'),
	':hu:'          => array('hu.gif',                          '20', '20',     'hu',         '0', 'Keskidi ??'),
	':lol:'         => array('lol.gif',                         '20', '20',     'lol',        '0', 'LOL'),
	':oui:'         => array('oui.gif',                         '17', '22',     'oui',        '0', 'Absolument monsieur !'),
	':pfff:'        => array('pfff.gif',                        '20', '20',     'pfff',       '0', "Mais n'importe quoi..."),
	':rhaaa:'       => array('rhaaa.gif',                       '20', '20',     'rhaaa',      '0', 'Rohlala mais laisse tomber...'),
	':search:'      => array('search.gif',                      '20', '20',     'search',     '0', 'Vaut mieux se méfier'),
	':smile:'       => array('smile.gif',                       '20', '20',     'smile',      '0', 'Héhé'),
	':-('           => array('triste.gif',                      '20', '20',     'triste',     '0', 'Bouhouuu :('),
	':zzz:'         => array('zzz.gif',                         '20', '20',     'zzz',        '0', 'Zzzzzzzz'),
	':baille:'      => array('baille.gif',                      '20', '20',     'baille',     '0', 'Ouaaaaah je vais aller dormir'),
	';-)'           => array('cling.gif',                       '20', '20',     'cling',      '0', 'O_O'),
	':haha:'        => array('haha.gif',                        '20', '20',     'haha',       '0', 'Hihihihi'),
	':heu:'         => array('heuuuu.gif',                      '20', '20',     'heuuuu',     '0', 'Mais bien sûr...'),
	':love:'        => array('love.gif',                        '20', '20',     'love',       '0', 'Je kiff !'),
	':oeil:'        => array('oeil.gif',                        '20', '20',     'oeil',       '0', "Et ma trompe c'est du poulet ?"),
	':ouin:'        => array('ouin.gif',                        '20', '20',     'ouin',       '0', 'Ouiiiiiiin'),
	':respect:'     => array('respect.gif',                     '20', '22',     'respect',    '0', 'Alors là respect !'),
	':saoul:'       => array('saoul.gif',                       '22', '20',     'saoul',      '0', 'Hips !'),
	':-p'           => array('tongue.gif',                      '20', '20',     'tongue',     '0', 'Pouet'),
	':ups:'         => array('ups.gif',                         '20', '20',     'ups',        '0', 'Slurp'),
	':non:'         => array('non.gif',                         '23', '20',     'non',        '0', 'Non non non'),

//	smiley			image name						            width height	alt            secret smiley

	':saint:'       => array('secrets/ange.gif',                '25', '25',     'ange',        '1', 'Innocent'),
	':choque:'      => array('secrets/chut.gif',                '20', '25',     'chut',        '1', 'Chuuuuuuuuuuuut'),
	':hurle_red:'   => array('secrets/hurle1.gif',              '35', '29',     'hurle1',      '1', "Bougez vous l'autre trompe"),
	':hurle_blue:'  => array('secrets/hurle2.gif',              '35', '29',     'hurle2',      '1', 'Bougez-vous la trompe !'),
	':bierro:'      => array('secrets/biere.gif',               '8',  '21',     'biere',       '1', 'Une petite bière, mon cher ?'),
	':pioupiouk:'   => array('secrets/piouk.gif',               '24', '16',     'piouk',       '1', 'Piouuuuuk'),
	':shit:'        => array('secrets/crocomouth.gif',          '26', '27',     'crocomouth',  '1', 'Krokomouth en boîte'),
	':bisou:'       => array('secrets/kiss.gif',                '35', '20',     'kiss',        '1', 'Bisous partout'),
	':idiot:'       => array('secrets/normal.gif',              '20', '20',     'normal',      '1', "Pas d'idée de texte..."),
	':svp:'         => array('secrets/ouinon.gif',              '25', '20',     'ouinon',      '1', 'Tututut'),
	':rock:'        => array('secrets/rock.gif',                '32', '27',     'rock',        '1', "Rock'n'roll"),
	':squelete:'    => array('secrets/squeleton.gif',           '20', '20',     'squeleton',   '1', 'Idiot du village'),
	':java:'        => array('secrets/tasse.gif',               '19', '19',     'tasse',       '1', 'Tout petit café'),
	':cafe:'        => array('secrets/cafe.gif',                '15', '15',     'cafe',        '1', 'Petit café'),
	':surprise:'    => array('secrets/cadouille.gif',           '30', '34',     'cadouille',   '1', 'Cadeau'),
	':schnibble:'    => array('secrets/schnibble.gif',          '25', '34',    'schnibble',   '1', 'Caillou sans importance'),
	':ninja:'       => array('secrets/kamikaz.gif',             '20', '20',     'kamikaz',     '1', 'Fou'),
	':pincemi:'     => array('secrets/lovefool.gif',            '20', '20',     'lovefool',    '1', 'Amoureux'),
	':noel:'        => array('secrets/nozouk.gif',              '20', '20',     'nozouk',      '1', "C'est noël !"),
	':zombie:'      => array('secrets/zombee.gif',              '20', '20',     'zombee',      '1', 'Mort vivant'),
	':fete:'        => array('secrets/pouet.gif',               '21', '20',     'pouet',       '1', 'Bonne année !'),
	':diablotin:'   => array('secrets/satan.gif',               '20', '20',     'satan',       '1', 'Diabolique'),
	':starwars:'    => array('secrets/starfighter.gif',         '20', '20', 	'starfighter', '1', 'Le pilote est dans la place'),
	':danse:'       => array('secrets/bouzoukette.gif',         '20', '24',     'bouzoukette', '1', 'Makarena !'),
	':exclaim:'     => array('secrets/exclamation.gif',         '20', '17',     'exclamation', '1', 'Attention !'),
	':contrat:'     => array('secrets/contrat.gif', 	        '21', '24',     'contrat',     '1', 'Vas y ! Signe !'),
	':raki:'  		=> array('secrets/raki.gif',        		'19', '20',     'raki',		   '1', 'Drogué'),
	':greve:'   	=> array('secrets/greve.gif',   		    '85', '30',     'greve',	   '1', 'Rah le bol !! Grève !'),
	':link:'   		=> array('secrets/link.gif',   		    	'26', '21',     'link',	   	   '1', 'Elle est ici la triforce ?'),
	':yeah:'        => array('secrets/yeah.gif',                '29', '24',     'yeah',        '1', 'Ouais mon gars !') // no comma after last item
);

/* End of file smileys.php */
/* Location: ./application/config/smileys.php */
