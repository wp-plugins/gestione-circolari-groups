<?php
/*
Plugin Name:Gestione Circolari Groups
Plugin URI: http://www.sisviluppo.info
Description: Plugin che implementa la gestione delle circolari scolastiche
Version:2.0.4
Author: Scimone Ignazio
Author URI: http://www.sisviluppo.info
License: GPL2
    Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : info@sisviluppo.info)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
  die('You are not allowed to call this page directly.'); 
}

define("Circolari_URL",plugin_dir_url(dirname (__FILE__).'/circolari.php'));
define("Circolari_DIR",dirname (__FILE__));
global $wpdb,$table_prefix;
$wpdb->table_firme_circolari = $table_prefix . "firme_circolari";
include_once(Circolari_DIR."/admin/firme.php");
include_once(Circolari_DIR."/functions.inc.php");
include_once(Circolari_DIR."/GestioneCircolari.widget.php");
include_once(Circolari_DIR."/GestioneNavigazioneCircolari.widget.php");
$msg="";
require_once(ABSPATH . 'wp-includes/pluggable.php'); 

switch ($_REQUEST["op"]){
	case "Firma":
		global $msg;
		$msg=gcg_FirmaCircolare($_REQUEST["pid"],-1);
		break;
	case "Adesione":
		global $msg;
		$msg=gcg_FirmaCircolare($_REQUEST["pid"],$_REQUEST["scelta"]);
		break;	
}

if ($_GET['update'] == 'true')
	$stato="<div id='setting-error-settings_updated' class='updated settings-error'> 
			<p><strong>Impostazioni salvate.</strong></p></div>";

add_action('init', 'circolariG_crea_custom_post_type');
add_filter( 'post_updated_messages', 'circolariG_updated_messages');
add_action( 'save_post', 'circolariG_salva_dettagli');
add_action('add_meta_boxes','circolariG_crea_box');
add_filter('manage_posts_columns', 'circolariG_NuoveColonne');  
add_action('manage_posts_custom_column', 'circolariG_NuoveColonneContenuto', 10, 2); 
add_action( 'admin_menu', 'circolariG_menu' ); 
add_action('init', 'circolariG_update_Impostazioni');
//add_action( 'contextual_help', 'circolariG_Help', 10, 3 );
//add_action( 'wp_before_admin_bar_render', 'circolariG_admin_bar_render' );
add_action( 'admin_menu', 'circolariG_add_menu_bubble' );
register_uninstall_hook(__FILE__,  'circolariG_uninstall' );
register_activation_hook( __FILE__,  'circolariG_activate');
add_filter( 'the_content', 'circolariG_vis_firma');
add_shortcode('VisCircolari', 'circolariG_Visualizza');
add_shortcode('VisCircolariHome', 'circolariG_VisualizzaCircolariHome');
add_action('wp_head', 'circolariG_Testata' );
add_action( 'admin_enqueue_scripts',  'circolariG_Admin_Enqueue_Scripts' );

add_action('do_feed', 'circolariG_disable_feed', 1);
add_action('do_feed_rdf', 'circolariG_disable_feed', 1);
add_action('do_feed_rss', 'circolariG_disable_feed', 1);
add_action('do_feed_rss2', 'circolariG_disable_feed', 1);
add_action('do_feed_atom', 'circolariG_disable_feed', 1);
 
function circolariG_disable_feed() {
	if ( get_post_type()=='circolari') {
        wp_die( 'Non ci sono feed disponibili per le Circolari, per visualizzarle naviga la  <a href="'. get_bloginfo('url') .'">Home</a> del sito!');
    }
}

function circolariG_Admin_Enqueue_Scripts( $hook_suffix ) {
	if(strpos($hook_suffix,"circolari")===false)
		return;	wp_enqueue_script('jquery');

	wp_enqueue_script('jquery');
	wp_enqueue_script( 'jquery-ui-datepicker', '', array('jquery'));
	wp_enqueue_script( 'Circolari-admin', plugins_url('js/Circolari.js', __FILE__ ));
	wp_enqueue_style( 'jquery.ui.theme', plugins_url( 'css/jquery-ui-custom.css', __FILE__ ) );

	wp_enqueue_script( 'Circolari-DataTable', plugins_url('js/jquery.dataTables.js', __FILE__ ));
	wp_enqueue_script( 'Circolari-DataTable-Tools', plugins_url('js/dataTables.tableTools.js', __FILE__ ));

	wp_enqueue_style( 'Circolari-DataTable-theme', plugins_url( 'css/jquery.dataTables.css', __FILE__ ) );
	wp_enqueue_style( 'Circolari-DataTable-theme-Tools', plugins_url( 'css/dataTables.tableTools.css', __FILE__ ) );
}

function circolariG_search_filter($query) {

if (get_post_type()=='newsletter' ) {
    	      $query->set('post_type', array( 'post', 'circolari' ) );
}
	return $query;
}

add_action('pre_get_posts','circolariG_search_filter');

function circolariG_Visualizza(){
	require_once ( dirname (__FILE__) . '/admin/frontend.php' );
	return $ret;
}
function circolariG_VisualizzaCircolariHome(){
	require_once ( dirname (__FILE__) . '/admin/frontendhome.php' );
	return $ret;
}

function circolariG_vis_firma( $content ){
	$PostID= get_the_ID();
	if (post_password_required( $PostID ))
		return $content;
	$Campo_Firma="";
	if (get_post_type( $PostID) !="circolari")
		return $content;
	if (!gcg_Is_Circolare_Da_Firmare($PostID)){
		return $content;
	}
	if (!is_user_logged_in() or !gcg_Is_Circolare_per_User($PostID))
		return $content;
	if (strlen(stristr($_SERVER["HTTP_REFERER"],"wp-admin/edit.php?post_type=circolari&page=Firma"))>0)
		return "<br />
		<button style=' outline: none;
 cursor: pointer;
 text-align: center;
 text-decoration: none;
 font: bold 12px Arial, Helvetica, sans-serif;
 color: #fff;
 padding: 10px 20px;
 border: solid 1px #0076a3;
 background: #0095cd;' onclick='javascript:history.back()'>Torna alla Firma</button>".$content;
	else{
		$Adesione=get_post_meta($PostID, "_sciopero");
		if (gcg_Is_Circolare_per_User($PostID)){
			$TipoCircolare="Circolare";
			$Campo_Firma_Adesione="";
			if ($Adesione[0]=="Si"){			
				$TipoCircolare="Circolare con Adesione";
				switch (gcg_get_Circolare_Adesione($PostID)){
				case 1:
					$Campo_Firma_Adesione=": adesione Si";
					break;
				case 2:
					$Campo_Firma_Adesione=": adesione No";		
					break;
				case 3:
					$Campo_Firma_Adesione=": Presa Visione";				
					break;
				}
			}	
			$firma=get_post_meta($PostID, "_firma");
			$BaseUrl=admin_url()."edit.php";
			if($firma[0]=="Si" or $Adesione[0]=="Si"){
				if (gcg_Is_Circolare_Firmata($PostID)){
					$Campo_Firma="Firmata".$Campo_Firma_Adesione;
				}
				else{
					if ($Adesione[0]=="Si"){			
					$Campo_Firma='<form action=""  method="get" style="display:inline;">
						<input type="hidden" name="post_type" value="circolari" />
						<input type="hidden" name="page" value="Firma" />
						<input type="hidden" name="op" value="Adesione" />
						<input type="hidden" name="pid" value="'.$PostID.'" />
						<input type="radio" name="scelta" class="s1-'.$PostID.'" value="1"/>Si 
						<input type="radio" name="scelta" class="s2-'.$PostID.'" value="2"/>No 
						<input type="radio" name="scelta" class="s3-'.$PostID.'" value="3" checked="checked"/>Presa Visione
						<input type="submit" name="inviaadesione" class="button inviaadesione" id="'.$PostID.'" value="Firma" rel="'.get_the_title($PostID).'"/>
					</form>';
					}else
						$Campo_Firma='<a href="?op=Firma&pid='.$PostID.'">Firma Circolare</a>';
				}
				$dati_firma=gcg_get_Firma_Circolare($PostID);
			}	
		}
		return $content." <br /><div style='border: solid 1px #0076a3; background: #c6d7f2;padding: 5px;'>".$Campo_Firma."</div>";
	}
}
function circolariG_activate() {
	global $wpdb;
	if(get_option('Circolari_Visibilita_Pubblica')== ''||!get_option('Circolari_Visibilita_Pubblica')){
		add_option('Circolari_Visibilita_Pubblica', '0');
	}
	if(get_option('Circolari_Categoria')== ''||!get_option('Circolari_Categoria')){
		add_option('Circolari_Categoria', '0');
	}
	if(get_option('Circolari_NumPerPag')== ''||!get_option('Circolari_NumPerPag')){
		add_option('Circolari_NumPerPag', '0');
	}
	if(get_option('Circolari_GGScadenza')== ''||!get_option('Circolari_GGScadenza')){
		add_option('Circolari_GGScadenza', '30');
	}
   	if(get_option('Circolari_NrCircHome')== ''||!get_option('Circolari_NrCircHome')){
    	add_option('Circolari_NrCircHome', '0');
    }
	$sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->table_firme_circolari." (
  			post_ID  bigint(20) NOT NULL,
  			user_ID bigint(20) NOT NULL,
  			datafirma timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  			ip varchar(16) DEFAULT NULL,
			adesione smallint(6) NOT NULL DEFAULT '-1',
  			PRIMARY KEY (post_ID,user_ID));";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}
	
function circolariG_add_menu_bubble() {
  global $menu;
  $NumCircolari=gcg_GetCircolariDaFirmare("N");
  if ($NumCircolari==0)
	return;
  $i=0;
  foreach($menu as $m){
  	if ($menu[$i][0]=="Circolari"){
		$menu[$i][0] .= "<span class='update-plugins count-1'><span class='update-count'>$NumCircolari</span></span>";
		return;
	}
	$i++;
 }
}

function circolariG_menu(){
   add_submenu_page( 'edit.php?post_type=circolari', 'Parametri',  'Parametri', 'edit_published_posts', 'circolari', 'circolariG_MenuPagine');
   $pageFirma=add_submenu_page( 'edit.php?post_type=circolari', 'Firma',  'Firma', 'read', 'Firma', 'circolariG_GestioneFirme');
   add_action( 'admin_head-'. $pageFirma, 'circolariG_Testata' );
   $pageFirmate=add_submenu_page( 'edit.php?post_type=circolari', 'Firmate',  'Firmate', 'read', 'Firmate', 'circolariG_VisualizzaFirmate');
   add_action( 'admin_head-'. $pageFirma, 'circolariG_Testata' );
   $pagenFirmate=add_submenu_page( 'edit.php?post_type=circolari', 'Scadute e non Firmate', 'Scadute e non Firmate', 'read', 'non_Firmate', 'circolariG_VisualizzaNonFirmate');
   add_action( 'admin_head-'. $pagenFirmate, 'circolariG_Testata' );
   $utility=add_submenu_page( 'edit.php?post_type=circolari', 'Utility',  'Utility', 'edit_published_posts', 'Utility', 'circolariG_Utility');
   add_action( 'admin_head-'. $utility, 'circolariG_Testata' );
}

function circolariG_Utility($Stato=""){
echo '<div class="wrap">
	<img src="'.Circolari_URL.'/img/utility.png" alt="Icona Permessi" style="display:inline;float:left;margin-top:5px;"/>
		<h2 style="margin-left:40px;">Utility Circolari</h2>';
if ($Stato!="") 
	echo '<div id="message" class="updated"><p>'.str_replace("%%br%%","<br />",$Stato).'</p></div>
      <meta http-equiv="refresh" content="5;url=admin.php?page=utility"/>';
echo '		</div> 
		<p></p>
		<div class="widefat" style="padding:10px;">
				<p style="text-align:center;font-size:1.5em;font-weight: bold;">
				Verifica procedura
				</p>
				<p style="text-align:left;font-size:1em;font-style: italic;">
Questa procedura esegue un test generale della procedura e riporta eventuali anomalie nei dati e nelle impostazioni.</spam><br />Operazioni eseguite:</p>
<p style="font-size:1em;font-style: italic;margin-left:10px;font-weight: bold;">
Verifica data scadenza firma <spam style="text-align:center;font-size:1.5em;font-weight: bold;"> <a href="edit.php?post_type=circolari&page=Utility&action=versca">Verifica</a></spam>
</p>';
		$lista="";
		if (isset($_GET['action']) && $_GET['action']=="versca"){
			$GGscadenza=get_option("Circolari_GGScadenza");
			$Posts = get_posts('post_type=circolari&numberposts=-1');
			foreach($Posts as $post){
				$Adesione=get_post_meta($post->ID, "_sciopero",TRUE);
				$firma=get_post_meta($post->ID, "_firma",TRUE);
				$scadenza=get_post_meta($post->ID, "_scadenza",TRUE);
				if ((!empty($Adesione) or !empty($firma)) and empty($scadenza)){
	 				$lista.="			<li>$post->ID $post->post_title $post->post_date_gmt";
	 				if (isset($_GET['opt']) && $_GET['opt']=="aggsca"){
	 					$Scadenza=date('Y-m-d', strtotime(substr($post->post_date_gmt,0,10). " + $GGscadenza days"));
	 					if (update_post_meta($post->ID,"_scadenza",$Scadenza ))
	 						$lista.= ' <img src="'.Circolari_URL.'/img/verificato.png" alt="Icona Permessi" style="display:inline;margin-left:5px;"/>';
	 				}
				$lista.= "</li>";					
				}
			}
			if (!empty($lista)){
				echo "<h4>Circolari da firmare senza data di scadenza</h4>
		<ul>
				$lista
		</ul>";
			echo '<p style="text-align:left;font-size:1em;font-style: italic;">Aggiorna Data entro cui firmare = Datapubblicazione + '. $GGscadenza .' giorni <spam style="text-align:center;font-size:1.5em;font-weight: bold;"> <a href="edit.php?post_type=circolari&page=Utility&action=versca&opt=aggsca">Aggiorna</a></spam>
</p>
</div>';			
		}else
			echo "<h4>Tutte le circolari da firmare hanno la data di scadenza</h4>";
	}
}

function circolariG_Testata() {
?>
<script type='text/javascript'>
jQuery.noConflict();
(function($) {
	$(function() {
		$('.inviaadesione').click(function(){
			switch ($("input[type=radio][name=scelta]:checked").val()){
					case "1":
						s="Si";
						break;
					case "2":
						s="No";
						break;
					case "3":
						s="Presa Visione";
						break;
				}
			var answer = confirm("Circolare "+$(this).attr('rel') +"\nConfermi la scelta:\n\n   " + s +"\n\nAllo sciopero?")
			if (answer){
				return true;
			}
			else{
				return false;
			}					
		});
 });
})(jQuery);
</script>	
<?php
}

function circolariG_MenuPagine(){
	switch ($_REQUEST["op"]){
		case "Firme":
			circolariG_VisualizzaFirme($_REQUEST["post_id"]);
			break;
		case "Adesioni":
			circolariG_VisualizzaFirme($_REQUEST["post_id"],1);
			break;
		case "email":
			circolariG_SpostainNewsletter($_REQUEST["post_id"]);
			break;
		default:
			circolariG_Parametri();	
	}
}
function circolariG_uninstall() {
	global $wpdb;
// Eliminazione Tabelle data Base
	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->table_firme_circolari);
	$Circolari = get_posts( "post_type=circolari" );
	foreach ( $Circolari as $Circolare )
		set_post_type( $Circolare );	
}

function circolariG_SpostainNewsletter($IDPost){
$DatiPost=get_post( $IDPost,  ARRAY_A);
		$args = array(
			'post_type' => 'attachment',
			'numberposts' => null,
			'post_status' => null,
			'post_parent' => $IDPost); 
		$attachments = get_posts($args);
		$LinkAllegati="";
		if ($attachments) {
			$LinkAllegati.="<p>Allegati
			<ul>";
			foreach ($attachments as $attachment) {
				$LinkAllegati.="		<li><a href='$attachment->guid'>$attachment->post_title</a></li>";
			}
			$LinkAllegati.="</p>
			</ul>";	
		}
$my_post = array(
  		'post_title'    => $DatiPost['post_title'],
  		'post_content'  => "<p>Ciao [USER-NAME]</p>
<p>in data odierna è stata inserita la seguente circolare nel sito [SITE-NAME]</p>
<p>[POST-EXCERPT]</p>
<p>[POST-CONTENT]</p>".$LinkAllegati,
  		'post_status'   => 'publish',
  		'comment_status'   => 'closed',
  		'ping_status' => 'closed',
  		'post_author' => $DatiPost['post_author'],
  		'post_name' => $DatiPost['post_name'],
  		'post_type' => 'newsletter');
$post_id =wp_insert_post( $my_post,$errore );
echo '<div class="wrap">
	  	<img src="'.Circolari_URL.'/img/mail.png" alt="Icona Send email" style="display:inline;float:left;margin-top:10px;"/>
	  	<h2 style="margin-left:40px;">Crea NewsLetter 
	  	<a href="'.site_url().'/wp-admin/edit.php?post_type=circolari" class="add-new-h2 tornaindietro">Torna indietro</a></h2>';

	if($post_id>0){
		$recipients=Array();
		$recipients['list'][] = 1;
		$recipients['list'][] = 2;
		add_post_meta ( $post_id, "_easymail_recipients", $recipients );	
		add_post_meta ( $post_id, "_placeholder_easymail_post",  $IDPost);	
		add_post_meta ( $post_id, "_placeholder_post_imgsize", 'thumbnail' );	
		add_post_meta ( $post_id, "_placeholder_newsletter_imgsize", 'thumbnail' );	
		add_post_meta ( $post_id, "_easymail_theme", 'campaignmonitor_elegant.html' );	
		echo "<p style='font-weight: bold;font-size: medium;color:green;'>NewsLetter Creata correttamente</p> 
		<p style='font-weight: bold;font-style: italic;font-size: medium;'>Adesso dovete completare le operazioni di invio seguendo pochi e semplici passi:<ul style='list-style: circle outside;margin-left:20px;'>
			<li>Selezionare la gestione delle NewsLetter</li>
			<li>Entrare in modifica nella circolare appena creata (l'ultima, quella in cima alla lista)</li>
			<li>Selezionate i destinatari</li>
			<li>Memorizzare le modifiche</li>
			<li>Dall'elenco delle NewsLetter, sulla riga corrente cliccare su <em>Richiesto: Crea la lista dei destinatari</em></li>
		</ul>
		</p>";
		add_post_meta ( $IDPost, "_sendNewsLetter",date("d/m/y g:i O"));
	}else{
		echo "<p  style='font-weight: bold;font-size: medium;color:red;'>NewsLetter Non Creata correttamente, errore riportato:</p>";
	}
}
function circolariG_Parametri(){
	$DestTutti  =  get_option('Circolari_Visibilita_Pubblica');
	$GiorniScadenza  =  get_option('Circolari_GGScadenza');
	$UsaG=get_option('Circolari_UsaGroups');
	$NrCircolariHome = get_option('Circolari_NrCircHome');
	if ($UsaG=="si")
		$UsaG="  checked='checked'";
	else
		$UsaG="";
echo'
<div class="wrap">
	  	<img src="'.Circolari_URL.'/img/opzioni32.png" alt="Icona configurazione" style="display:inline;float:left;margin-top:10px;"/>
	  	<h2 style="margin-left:40px;">Configurazione Circolari</h2>
	  <form name="Circolari_cnf" action="'.get_bloginfo('wpurl').'/wp-admin/index.php" method="post">
	  <table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="pubblica">Gruppo Pubblico Circolari</label></th>
			<td><select name="pubblica" id="pubblica" >';
			$bloggroups =gcg_get_Circolari_Gruppi();
			foreach ($bloggroups as $gruppo) {
		        echo '<option value="'.$gruppo['Id'].'" ';
				if($DestTutti==$gruppo['Id']) 
					echo 'selected="selected"';
				echo '>'.$gruppo['Nome'].'</option>';	
			}
echo'</select></td>				
		</tr>
		<tr valign="top">
			<th scope="row"><label for="categoria">Categoria Circolari</label></th>
			<td>';
			wp_dropdown_categories('orderby=name&hide_empty=0&name=Categoria&id=categoria&selected='.get_option('Circolari_Categoria'));
echo'			</td>				
		</tr>
		<tr valign="top">
			<th scope="row"><label for="numcircolarifirma">Numero Circolari da visualizzare per pagina</label></th>
			<td>
				<input type="text" name="NCircolariPF" id="NCircolariPF" size="3" maxlength="3" value="'.get_option('Circolari_NumPerPag').'" />
			</td>				
		</tr>
		<tr valign="top">
			<th scope="row"><label for="GGScadenza">N. giorni entro cui firmare di default</label></th>
			<td>
				<input type="text" name="GGScadenza" id="GGScadenza" size="3" maxlength="3" value="'.$GiorniScadenza.'" />
			</td>				
		</tr>
		<tr valign="top">
			<th scope="row"><label for="NrCircHome">N. di circolari da visionare in home</label></th>
			<td>
				<input type="text" name="NrCircHome" id="NrCircHome" size="3" maxlength="3" value="'.$NrCircolariHome.'" />
			</td>				
		</tr>
	</table>
	    <p class="submit">
	        <input type="submit" name="Circolari_submit_button" value="Salva Modifiche" />
	    </p> 
	    </form>
	    </div>';
}

function circolariG_update_Impostazioni(){
    if($_POST['Circolari_submit_button'] == 'Salva Modifiche'){
	    update_option('Circolari_Visibilita_Pubblica',$_POST['pubblica'] );
	    update_option('Circolari_Categoria',$_POST['Categoria'] );
	    update_option('Circolari_NumPerPag',$_POST['NCircolariPF'] );
	    update_option('Circolari_GGScadenza',$_POST['GGScadenza'] );
		update_option('Circolari_NrCircHome',$_POST['NrCircHome'] );     
		header('Location: '.get_bloginfo('wpurl').'/wp-admin/edit.php?post_type=circolari'); 
	}
}

// Nuova Colonna Gestione  
function circolariG_NuoveColonne($defaults) {  
	if ($_GET['post_type']=="circolari"){
		$defaults['numero'] = 'Numero';  
	    $defaults['destinatari'] = 'Destinatari';
		$defaults['firme'] = 'Firme';    
		$defaults['scadenza'] = 'Firmare entro'; 
	    $defaults['gestionecircolari'] = 'Gestione';  
	}
   return $defaults;  
}  
  
// Visualizzazione nuova colonna Gestione  
function circolariG_NuoveColonneContenuto($column_name, $post_ID) {  
	global $wpdb;
 	if ($_GET['post_type']=="circolari"){
		if ($column_name == 'gestionecircolari') {  
			$firma=get_post_meta($post_ID, "_firma");
			$sciopero=get_post_meta($post_ID, "_sciopero");
			$Linkfirma="";
		    if ($firma[0]=="Si" )
				$Linkfirma='<a href="'.admin_url().'edit.php?post_type=circolari&page=circolari&op=Firme&post_id='.$post_ID.'">Firme</a> |';
			if($sciopero[0]=="Si")
				$Linkfirma='<a href="'.admin_url().'edit.php?post_type=circolari&page=circolari&op=Adesioni&post_id='.$post_ID.'">Adesioni</a> |';		    	if ( defined( 'ALO_EM_INTERVAL_MIN' ) ){
				$DataInvio = get_post_meta( $post_ID, "_sendNewsLetter", true); 
	    		if ($DataInvio){
					$res=$wpdb->get_results("SELECT post_id FROM $wpdb->postmeta Where meta_value=$post_ID And meta_key='_placeholder_easymail_post';");
					$Linkfirma.="Inviata in data ". $DataInvio.' <a href="'.admin_url().'post.php?post='.$res[0]->post_id.'&action=edit">Modifica NewsLetter</a>';
				}else
	            	$Linkfirma.='<a href="'.admin_url().'edit.php?post_type=circolari&page=circolari&op=email&post_id='.$post_ID.'">Invia per eMail</a>';
			}
			echo $Linkfirma;
	     } 
		 if ($column_name == 'numero'){
		 	$numero=get_post_meta($post_ID, "_numero",TRUE);
			$anno=get_post_meta($post_ID, "_anno",TRUE);
			echo $numero.'_'.$anno;
		 }
		 if ($column_name == 'firme'){
		 	$GDes=get_post_meta($post_ID, "_destinatari");
		 	$GDes=unserialize($GDes[0]);
		 	$NU=0;
		 	foreach($GDes as $Gruppo)
		 		$NU+=gcg_Get_User_Per_Gruppo($Gruppo);
			echo gcg_Get_Numero_Firme_Per_Circolare($post_ID)."/$NU";			
		}
		 if ($column_name == 'scadenza'){
		 	$scadenza=gcg_FormatDataItalianoBreve(get_post_meta($post_ID, "_scadenza",TRUE));
			echo $scadenza;
		 }
		if ($column_name == 'destinatari'){
			$Gruppi=gcg_get_Circolari_Gruppi();
			$Destinatari=get_post_meta($post_ID, "_destinatari");
			if(count($Destinatari)>0){
				$Destinatari=unserialize($Destinatari[0]);
				$Nomi_Des='';
					foreach($Gruppi as $Gruppo)
						if(array_search($Gruppo["Id"],$Destinatari)!==FALSE)
							$Nomi_Des.=$Gruppo["Nome"].", ";
				echo substr($Nomi_Des,0,strlen($Nomi_Des)-2);
			}
		}
	}
}  
function circolariG_crea_custom_post_type() {

 register_post_type('circolari', array(
  'labels' => array(
   'name' => __( 'Circolari' ),
   'singular_name' => __( 'Circolare' ),
   'add_new' => __( 'Aggiungi Circolare' ),
   'add_new_item' => 'Aggiungi nuova Circolare',
   'edit' => __( 'Modifica' ),
   'edit_item' => __( 'Modifica Circolare' ),
   'new_item' => __( 'Nuova Circolare' ),
   'items_archive' => __( 'Circolare Aggiornata' ),
   'view' => __( 'Visualizza Circolare' ),
   'view_item' => __( 'Visualizza' ),
   'search_items' => __( 'Cerca Circolare' ),
   'not_found' => __( 'Nessuna Circolare trovata' ),
   'not_found_in_trash' => __( 'Nessuna Circoalre trovata nel cestino' ),
   'parent' => __( 'Circolare superiore' )),
   'public' => true,
   'show_ui' => true,
   'show_in_admin_bar' => true,
   'menu_position' => 5,
   'capability_type' => 'post',
   'hierarchical' => false,
   'has_archive' => true,  
   'menu_icon' => plugins_url( 'img/circolare.png', __FILE__ ),
//   'taxonomies' => array('category'),  
   'supports' => array('title', 'editor', 'author','excerpt')));
}
// add links/menus to the admin bar

function circolariG_admin_bar_render() {
	global $wp_admin_bar;
	$NumCircolari=gcg_GetCircolariDaFirmare("N");
	if ($NumCircolari>0)
		$VisNumCircolari=' <span style="background-color:red;">&nbsp;'.$NumCircolari.'&nbsp;</span>';
	else
		$VisNumCircolari="";
	$wp_admin_bar->add_menu( array(
		'id' => 'fc', // link ID, defaults to a sanitized title value
		'title' => 'Circolari '.$VisNumCircolari, // link title
		'href' => site_url().'/wp-admin/edit.php?post_type=circolari&page=Firma', // name of file
		'meta' => array(  'title' => 'Circolari da Firmare' )));
}

function circolariG_Help( $contextual_help, $screen_id, $screen ) { 
	if ( !(stripos($screen->id,'circolari' )===FALSE)) {

		$contextual_help = '<h2>Prodotto</h2>';

	} elseif ( 'edit-product' == $screen->id ) {

		$contextual_help = '<h2>Modifica</h2>
';

	}
	return $contextual_help;
}

function circolariG_updated_messages( $messages ) {
	global $post, $post_ID;
    $messages['circolari'] = array(
	0 => '', 
	1 => sprintf('Circolare aggiornata. <a href="%s">Visualizza Circolare</a>', esc_url( get_permalink($post_ID) ) ),
	2 => 'Circolare aggiornata',
/* translators: %s: date and time of the revision */
	3 => isset($_GET['circolari']) ? sprintf( 'Circolare ripristinata alla versione %s', wp_post_revision_title( (int) $_GET['circolari'], false ) ) : false,
	4 => sprintf( 'Circolare pubblicata. <a href="%s">Visualizza Circolare</a>', esc_url( get_permalink($post_ID) ) ),
	5 => 'Circolare memorizzata',
	6 => sprintf( 'Circolare inviata. <a target="_blank" href="%s">Anteprima Circolare</a>', esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	7 => sprintf( 'Circolare schedulata per: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Anteprima circolare</a>',date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
	8 => sprintf( 'Bozza Circolare aggiornata. <a target="_blank" href="%s">Anteprima Circolare</a>', esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
);
return $messages;
}
function circolariG_salva_dettagli( $post_id ){
	global $wpdb,$table_prefix;
		if ( $_POST['post_type'] == 'circolari' ) {	
			$Destinatari=array();
			$Gruppi=gcg_get_Circolari_Gruppi();
			foreach($Gruppi as $Gruppo)
				if(isset($_POST["Gruppo_".$Gruppo["Id"]]))
					$Destinatari[]=intval($Gruppo["Id"]);
			$Circolari=get_option('Circolari_Categoria');
			wp_set_post_categories( $post_id, array($Circolari) );
			$term_list = wp_get_post_terms($post_id, 'gruppiutenti', array("fields" => "names"));
			if (count($term_list)==0) {
				$DestTutti=get_option('Circolari_Visibilita_Pubblica');
				$Destinatari[]=(int)$DestTutti;
			}
			if ($_POST["scadenza"])
				update_post_meta( $post_id, '_scadenza', gcg_FormatDataDB($_POST["scadenza"]));
			else{
				update_post_meta( $post_id, '_scadenza', gcg_FormatDataDB($_POST["scadenza"],get_option('Circolari_GGScadenza')));
			}
			update_post_meta( $post_id, '_numero', $_POST["numero"]);
			update_post_meta( $post_id, '_anno', $_POST["anno"]);
			update_post_meta( $post_id, '_firma', $_POST["firma"]);
			update_post_meta( $post_id, '_sciopero', $_POST["sciopero"]);
			update_post_meta( $post_id, '_destinatari', serialize($Destinatari));
		}
}
function circolariG_crea_box(){
  add_meta_box('parametri', 'Parametri Circolari', 'circolariG_crea_box_parametri', 'circolari', 'advanced', 'high');
 /* add_meta_box('prog', 'Progressivo', 'circolariG_crea_box_progressivo', 'circolari', 'advanced', 'high');
  add_meta_box('firma', 'Richiesta Firma', 'circolariG_crea_box_firma', 'circolari', 'advanced', 'high');
  add_meta_box('destinatari', 'Destinatari', 'circolariG_crea_box_destinatari', 'circolari', 'advanced', 'high');*/
}

function circolariG_crea_box_parametri( $post ){
echo "<h4>Firmare entro</h4>";	
circolariG_crea_box_data_scadenza($post);
echo "<h4>Progessivo</h4>";
circolariG_crea_box_progressivo($post);
echo "<h4>Firme</h4>";
circolariG_crea_box_firma($post);
echo "<h4>Visibilit&agrave;</h4>";
circolariG_crea_box_destinatari($post);
}

function circolariG_crea_box_data_scadenza( $post ){
$scadenza=gcg_FormatDataItalianoBreve(get_post_meta($post->ID, "_scadenza",TRUE));
echo "<label for='scadenza'>Data</label> <input type='text' name='scadenza' value='".$scadenza."' size='8' style='text-align:left' id='DataScadenza'/>
	<br />" ;
}

function circolariG_crea_box_progressivo( $post ){
$numero=get_post_meta($post->ID, "_numero");
$anno=get_post_meta($post->ID, "_anno");
$anno=$anno[0];
$numero=$numero[0];
if ($anno=="" or !$anno){
	$anno=date("Y");
	$canno=strval(date("y"))+1;
	if (date("n")>8)
		$anno=$anno."/".$canno;
	else	
		$anno=($anno-1)."/".date("y");
}
if ($numero=="" or !$numero)
	$numero=circolariG_NewNumCircolare();
echo '
<p  class="description"> 	
	<label for="NumeroCircolare">Numero</label>/<label for="AnnoCircolare">Anno</label>
	<input type="text" id ="NumeroCircolare" name="numero" value="'.$numero.'" size="5" style="text-align:right"/>_<input type="text" id="AnnoCircolare" name="anno" value="'.$anno.'" size="5"/>
</p>';
}

function circolariG_crea_box_destinatari( $post){
	$Gruppi=gcg_get_Circolari_Gruppi();
	$Destinatari=get_post_meta($post->ID, "_destinatari");
	if (count($Destinatari)==0)
		$Destinatari=array();
	else
		$Destinatari=unserialize($Destinatari[0]);
	foreach($Gruppi as $Gruppo){
		if(array_search($Gruppo["Id"],$Destinatari)!==FALSE)
			$selGruppo=' checked="checked"';
		else
			$selGruppo="";
		echo '<input type="checkbox" id="Gruppo_'.$Gruppo["Id"].'" name="Gruppo_'.$Gruppo["Id"].'" value="1" '.$selGruppo.'/>
	 		<label for="Gruppo_"'.$Gruppo["Id"].'">'.$Gruppo["Nome"].'</label><br />';
	}
}

function circolariG_crea_box_firma( $post ){
$firma=get_post_meta($post->ID, "_firma");
if($firma[0]=="Si")
	$firma='checked="checked"';
 echo "
 	<p class='description'>
 		<input type='checkbox' id='ImpostaFirma' name='firma' value='Si' $firma />
 		<label for='ImpostaFirma'>E' richiesta la firma alla circolare</label>
	</p>" ;
$sciopero=get_post_meta($post->ID, "_sciopero");
if($sciopero[0]=="Si")
	$sciopero='checked="checked"';
 echo "		
 	<p class='description'>
		<input type='checkbox' id='ImpostaSciopero' name='sciopero' value='Si' $sciopero />
 		<label for='ImpostaSciopero'>La circolare si riferisce ad uno sciopero.<br />Bisogna indicare l'adesione/non adesione o la presa visione</label>
	</p>" ;
}

function circolariG_NewNumCircolare(){
	$args = array( 'numberposts' => '1','post_type'=> 'circolari','post_status' => 'publish','meta_key' => '_anno','meta_value' => $numero);
	$ultimo=wp_get_recent_posts($args);
	$ID=$ultimo[0]['ID'];
	$numero=get_post_meta($ID, "_numero");
	return $numero[0]+1;
}


function circolariG_VisualizzaFirme($post_id,$Tipo=0){
global $GestioneScuola;
$numero=get_post_meta($post_id, "_numero");
$anno=get_post_meta($post_id, "_anno");
$circolare=get_post($post_id);
// Inizio interfaccia
echo' 
<div class="wrap">
	      <img src="'.Circolari_URL.'/img/firma24.png" alt="Icona Atti" style="display:inline;float:left;margin-top:10px;"/>
		  
<h2 style="margin-left:40px;">Circolare n°'.$numero[0].'_'.$anno[0].'<br /><strong>'.$circolare->post_title.'</strong></h2>
<div id="col-container">
	<div class="col-wrap">';
//$globale=get_post_meta($post_id, '_visibilita_generale');
$fgs=get_post_meta($post_id, "_destinatari");
$fgs=unserialize($fgs[0]);
if(!empty($fgs)){
	foreach($fgs as $fg){
		$Elenco.="<em>".gcg_get_Circolari_Gruppo($fg)."</em> - ";
	}
}
echo'	
<div class="col-wrap">
		<h3>Visibilit&aacute;</h3>
			<p>'.$Elenco.'</p>
</div><!-- /col-wrap -->';
$utenti=gcg_Get_Users_per_Circolare($post_id);
if ($Tipo==1)
	$sottrai=1;
else	
	$sottrai=0;
echo '
	<div>
		<table  id="TabellaCircolari" class="widefat"  cellspacing="0" width="99%">
			<thead>
				<tr>
					<th style="width:'.(20-$sottrai).'%;">User login</th>
					<th style="width:'.(30-$sottrai).'%;" id="ColOrd" sorted="1">Cognome</th>
					<th style="width:'.(15-$sottrai).'%;">Gruppo</th>
					<th style="width:'.(15-$sottrai).'%;">Data Firma</th>';
if ($Tipo==1)
	echo '
				<th style="width:12%;">Adesione</th>';				
echo '
				<th>IP</th>
			</tr>
		</thead>
		<tboby>';
foreach($utenti as $utente){
	$user=get_user_by("id",$utente);
	$firma=gcg_get_Firma_Circolare($post_id,$user->ID);
	$gruppiutente=gcg_get_Gruppi_Utente($user->ID);
	echo '
				<tr>
					<td>'.$user->user_login.'</td>
					<td>'.$user->display_name.'</td>
					<td>'.$gruppiutente.'</td>
					<td>'.$firma->datafirma.'</td>';
	if ($Tipo==1){
		switch ($firma->adesione){
			case 1:
				$desad="Si";
				break;
			case 2:
				$desad="No";
				break;
			case 3:	
				$desad="Presa Visione";
				break;
			default:
				$desad="Non Firmata";
		}
		echo '
					<td>'.$desad.'</td>';
	}
	echo '
					<td>'.$firma->ip.'</td>
				</tr>';
}
echo'
			</tbody>
		</table>
</div>
';
}	

?>