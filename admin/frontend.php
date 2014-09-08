<?php
/**
 * Gestione Circolari Groups
 * 
 * @package Gestione Circolari Groups
 * @author Scimone Ignazio
 * @copyright 2011-2014
 * @since 1.6
 */

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

if (isset($_REQUEST['anno']))
	$Anno=$_REQUEST['anno'];
else
	$Anno=date("Y");
$Mese=0;
if (isset($_REQUEST['mese']))
	$Mese=$_REQUEST['mese'];
$ret=circolariG_Lista_Circolari($Anno,$Mese);

function circolariG_Lista_Circolari($Anno,$Mese){
$Contenuto="";
$IdCircolari=get_option('Circolari_Categoria');
$mesecorrente = date('n');
if (isset($_REQUEST['Anno']))
	$annocorrente = $_REQUEST['Anno'];
else
	$annocorrente = date('Y');
if (isset($_REQUEST['Mese']))
	$mesecorrente=$_REQUEST['Mese'];
elseif(isset($_REQUEST['Anno']))
	$mesecorrente="";
else
	$mesecorrente=date('n');
$args = array( 'category' => $IdCircolari,
		       'post_type' => array('post','circolari'),
			   'year' => $annocorrente,
			   'monthnum' => $mesecorrente,
			   'posts_per_page'  => -1,
			   'post_status' => 'publish');
$Circolari = get_posts($args);
if (empty($Circolari)){
	$Contenuto.='<h3>Non risultano circolari per '.$mesecorrente.' '.$annocorrente.' verranno visualizzate le ultime 5</h3>';
	$args = array( 'category' => $IdCircolari,
		       'post_type' => array('post','circolari'),
			   'posts_per_page'  => 5,
			   'post_status' => 'publish');
	$Circolari = get_posts($args);
}
$Contenuto.=' <div>';
//print_r($Circolari);
foreach($Circolari as $post) {
	$visibilita=get_post_meta($post->ID, "_visibilita");
 	if(count($visibilita)==0)
 		$visibilita="p";
 	else 
 		$visibilita=$visibilita[0];
	if ((gcg_Is_Circolare_per_User($post->ID)) or $visibilita=="p"){
			$Gruppi=gcg_get_Circolari_Gruppi();
			$Destinatari=get_post_meta($post->ID, "_destinatari");
			$Elenco="";
			if(count($Destinatari)>0){
				$Destinatari=unserialize($Destinatari[0]);
				$Nomi_Des='';
					foreach($Gruppi as $Gruppo)
						if(array_search($Gruppo["Id"],$Destinatari)!==FALSE)
							$Nomi_Des.=$Gruppo["Nome"].", ";
				$Elenco=substr($Nomi_Des,0,strlen($Nomi_Des)-2);
			}
		$Contenuto.='
		<div style="margin-bottom:15px;padding:3px;">';
		$numero=get_post_meta($post->ID, "_numero",TRUE);
		$anno=get_post_meta($post->ID, "_anno",TRUE);
		$Contenuto.='
			<h4><a href="'.get_permalink($post->ID).'">'.$post->post_title.'</a></h4>
			<div style="font-weight: bold;font-size:0.8em;margin-top:3px;">Del '.gcg_FormatDataItaliano($post->post_date).' Numero '.$numero.'_'.$anno.'</div> 
			<div style="height:30px;">
				<div style="display:inline;">
					<img src="'.Circolari_URL.'/img/tipo.png" style="border:0;float:left;" alt="Icona tipo circolare"/>
				</div>
				<div style="display:inline;vertical-align:top;">
					<p style="font-style:italic;font-weight:bold;font-size:0.9em;display:inline;margin-top:3px;">'.$post->post_type.'</p>
				</div>';
		if ($post->post_type=="circolari")
			$Contenuto.='
				<div style="display:inline;">
					<img src="'.Circolari_URL.'/img/destinatari.png" style="border:0;" alt="Icona destinatari"/>
				</div>
				<div style="display:inline;vertical-align:top;">
					<p style="font-style:italic;font-weight:bold;font-size:0.9em;display:inline;margin-top:3px;">'.$Elenco.'</p>
				</div>';
		if (!post_password_required( $post->ID ))
			$riassunto=	$post->post_excerpt;
		else{
			$riassunto="";
		}
		if (!empty($post->post_password))
			$Contenuto.='
				<div style="display:inline;">
					<img src="'.Circolari_URL.'img/protetto.png" style="border:0;" alt="Icona protezione"/>
				</div>
				<div style="display:inline;vertical-align:top;">
					<p style="font-style:italic;font-size:0.8em;display:inline;margin-top:3px;">Contenuto Protetto</p>
				</div>';	
		if (!gcg_Is_Circolare_Firmata($post->ID)){
			if (get_post_meta($post->ID, "_sciopero",TRUE)=="Si")
				$Tipo="Esprimere adesione";
			else
			if (get_post_meta($post->ID, "_firma",TRUE)=="Si")
				$Tipo="Firmare";
			$Contenuto.='
				<div style="display:inline;">
					<img src="'.Circolari_URL.'/img/firma.png" style="border:0;" alt="Icona firma o presa visione"/>
				</div>
				<div style="display:inline;vertical-align:top;">
					<p style="font-style:italic;font-weight:bold;font-size:0.9em;display:inline;margin-top:3px;color:red;">'.$Tipo.'</p>
				</div>';	
		}
		$Contenuto.='	
			</div>
			<div style="margin-bottom:5px;">
				<em>'.$riassunto .'</em>
			</div>
			<hr />
		</div>';
	}
}
$Contenuto.= '
		</div>
		<div style="clear:both"></div>';
return $Contenuto;
}
?>