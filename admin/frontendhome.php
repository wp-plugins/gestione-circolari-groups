<?php

/**
 * Gestione Circolari Groups
 * 
 * @package Gestione Circolari Groups
 * @author Scimone Ignazio
 * @copyright 2011-2014
 * @ver 2.0
 */


if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

	$NumberCirc=get_option('Circolari_NrCircHome');
	$ret=Lista_Circolari($NumberCirc);

	function Lista_Circolari($NumberCirc){
		$Contenuto="";
		$IdCircolari=get_option('Circolari_Categoria');
		$args = array( 'category' => $IdCircolari,
		   'post_type' => array('post','circolari'),
		   'numberposts'=>$NumberCirc,
		   'post_status' => 'publish');
		$Circolari = get_posts($args);
		$Contenuto.=' <div>';
		
		foreach($Circolari as $post) {
			$visibilita=get_post_meta($post->ID, "_visibilita");
			if(count($visibilita)==0)
				$visibilita="p";
			else 
				$visibilita=$visibilita[0];

		if ((Is_Circolare_per_User($post->ID) and $visibilita=="d") or $visibilita=="p"){
			$fgs = wp_get_object_terms($post->ID, 'gruppiutenti');
			$Elenco="";
			if(!empty($fgs)){

				foreach($fgs as $fg){

					$Elenco.="<em>".$fg->name."</em> - ";

				}

				$Elenco=substr($Elenco,0,strlen($Elenco)-3);

			}

			$Contenuto.='<h3>'.FormatDataItaliano($post->post_date).' - 

			<a href="'.get_permalink($post->ID).'">'.$post->post_title.'</a></h3>';

			/*if ($post->post_type=="circolari")

				$Contenuto.='<p>Destinatari => <spam style="font-style: italic; font-weight: bold;font-size:0.9em;">'.$Elenco.'</spam></p>'; */

		   

		}

	}

	$Contenuto.= '

			</div>

			<div style="clear:both"></div>';

	return $Contenuto;

	}



?>