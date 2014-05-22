<?php
/**
 * Gestione Circolari Groups- Funzioni libreia generale
 * 
 * @package Gestione Circolari
 * @author Scimone Ignazio
 * @copyright 2011-2014
 * @ver 1.3
 */
 
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }


function gcg_FormatDataItaliano($Data){
	$mesi = array('', 'Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio',  'Agosto', 'Settembre', 'Ottobre', 'Novembre','Dicembre');
	$giorni = array('Domenica','Lunedì','Martedì', 'Mercoledì', 'Giovedì', 'Venerdì','Sabato');
	list($anno,$mese,$giorno) = explode('-',substr($Data,0,10)); 
	return $giorno.' '.substr($mesi[intval($mese)],0,3).' '.$anno;
}
function gcg_GetNumeroCircolare($PostID){
	$numero=get_post_meta($PostID, "_numero");
	$numero=$numero[0];
	$anno=get_post_meta($PostID, "_anno");
	$anno=$anno[0];
	$NumeroCircolare=$numero.'/'.$anno ;
return $NumeroCircolare;
}

function gcg_Is_Circolare_Da_Firmare($IDCircolare){
	global $wpdb, $current_user;
	get_currentuserinfo();
	$destinatari=gcg_Get_Users_per_Circolare($IDCircolare);
	$DaFirmare=get_post_meta( $IDCircolare, "_firma",true);
	$PresaVisione=get_post_meta( $IDCircolare, "_sciopero",true);
	if (in_array($current_user->ID,$destinatari) and ($DaFirmare=="Si" or $PresaVisione=="Si"))
		return TRUE;
	else
		return FALSE;
}

function gcg_Is_Circolare_Firmata($IDCircolare){
	global $wpdb, $current_user;
	get_currentuserinfo();
	if (!gcg_Is_Circolare_Da_Firmare($IDCircolare))
		return TRUE;
	$ris=$wpdb->get_results("SELECT * FROM $wpdb->table_firme_circolari WHERE post_ID=$IDCircolare AND user_ID=$current_user->ID;");
	if (!empty($ris))
		return TRUE;
	else
		return FALSE;	
}
function gcg_get_Circolare_Adesione($IDCircolare){
	global $wpdb, $current_user;
	get_currentuserinfo();
	$ris=$wpdb->get_results("SELECT * FROM $wpdb->table_firme_circolari WHERE post_ID=$IDCircolare AND user_ID=$current_user->ID;");
	if (!empty($ris))
		return $ris[0]->adesione;
	else
		return "";	
}

function gcg_get_Firma_Circolare($IDCircolare,$IDUser=-1){
	global $wpdb, $current_user;
	if ($IDUser==-1){
		get_currentuserinfo();
		$IDUser=$current_user->ID;
	}
	$ris=$wpdb->get_results("SELECT datafirma,ip,adesione FROM $wpdb->table_firme_circolari WHERE post_ID=$IDCircolare AND user_ID=$IDUser;");
	if (!empty($ris))
		return $ris[0];
	else
		return FALSE;	
}

function gcg_GetNumCircolariDaFirmare($Tipo="N"){
	global $wpdb;
	$ris=$wpdb->get_results("SELECT * 
	FROM ($wpdb->posts left join $wpdb->postmeta on $wpdb->posts.ID = $wpdb->postmeta.post_id)
	Where  $wpdb->posts.post_type='circolari' 
	   and $wpdb->posts.post_status='publish' 
	   and ($wpdb->postmeta.meta_key='_firma' and $wpdb->postmeta.meta_value ='Si')");
	if (empty($ris))
		return 0;	
	if ($Tipo=="N")
		$Circolari=0;
	else
		$Circolari=array();
	foreach($ris as $riga){
		$Vis=gcg_Is_Circolare_per_User($riga->ID);
		if (!gcg_Is_Circolare_Firmata($riga->ID) and $Vis)
			if ($Tipo=="N" and $Vis){
				$Circolari++;
			}else{
				$Circolari[]=$riga;
			}
	}
	return $Circolari;
}
function gcg_GetNumCircolariFirmate($Tipo="N"){
	global $wpdb;
	$ris=$wpdb->get_results("SELECT * 
	FROM ($wpdb->posts left join $wpdb->postmeta on $wpdb->posts.ID = $wpdb->postmeta.post_id)
	Where  $wpdb->posts.post_type='circolari' 
	   and $wpdb->posts.post_status='publish' 
	   and ($wpdb->postmeta.meta_key='_firma' and $wpdb->postmeta.meta_value ='Si')");
	if (empty($ris))
		return 0;	
	if ($Tipo=="N")
		$Circolari=0;
	else
		$Circolari=array();
	foreach($ris as $riga){
		$Vis=gcg_Is_Circolare_per_User($riga->ID);
		if (gcg_Is_Circolare_Firmata($riga->ID) and $Vis)
			if ($Tipo=="N" and $Vis){
				$Circolari++;
			}else{
				$Circolari[]=$riga;
			}
	}
	return $Circolari;
}

function gcg_Get_Users_per_Circolare($IDCircolare){
$DestTutti=get_option('Circolari_Visibilita_Pubblica');
$Destinatari=get_post_meta($IDCircolare, "_destinatari");
$Destinatari=unserialize($Destinatari[0]);
$ListaUtenti=get_users();
$UtentiCircolare=array();
$Tutti="";
if (!$Destinatari)
	$Tutti="*";
else
	if (in_array($DestTutti,$Destinatari))
		$Tutti="*";
if($Tutti=="*")	{
	foreach($ListaUtenti as $utente)
		$UtentiCircolare[]=$utente->ID;
	return $UtentiCircolare;
}
foreach($ListaUtenti as $utente){
	if (gcg_Is_Circolare_per_User($IDCircolare,$utente->ID))
		$UtentiCircolare[]=$utente->ID;
}
return $UtentiCircolare;
}
function gcg_get_Circolari_Gruppo($IdCircolare){
	global $wpdb,$table_prefix;
	$Gruppi=array();
	$Sql="Select group_id, name From ".$table_prefix."groups_group Where group_id=".$IdCircolare;
	$Records=$wpdb->get_results($Sql,ARRAY_A);
	$Gruppo=$Records[0]["name"];
	return $Gruppo;
}

function gcg_get_Gruppi_Utente($IdUser){
	global $wpdb,$table_prefix;
	$Gruppi="";
	$Sql="SELECT * FROM ".$table_prefix."groups_user_group INNER JOIN "
				         .$table_prefix."groups_group on "
				         .$table_prefix."groups_user_group.group_id=".$table_prefix."groups_group.group_id 
				   WHERE user_id=%d";
	$Records=$wpdb->get_results($wpdb->prepare($Sql,$IdUser),ARRAY_A);
	foreach( $Records as $Record){
		$Gruppi.=$Record["name"]." - ";
	}
	return substr($Gruppi,0,strlen($Gruppi)-3);
}

function gcg_get_Circolari_Gruppi(){
	global $wpdb,$table_prefix;
	$Gruppi=array();
	$Sql="Select group_id, name From ".$table_prefix."groups_group Where group_id>1";
	$Records=$wpdb->get_results($Sql,ARRAY_A);
	foreach( $Records as $Record)
		$Gruppi[]=array("Id"=>$Record["group_id"],
					  "Nome"=>$Record["name"]);
	return $Gruppi;
}

function gcg_MeseNL($mese){
	$mesi=array("Gennaio","Febbraio","Marzo","Aprile","Maggio","Giugno","Luglio","Agosto","Settembre","Ottobre","Novembre","Dicembre");
	return $mesi[$mese-1];
}
function gcg_Is_Circolare_per_User($IDCircolare,$IDUser=-1){
	global $current_user,$wpdb,$table_prefix;;
	if(get_post_type($IDCircolare)!="circolari")
		return TRUE;
	if ($IDUser==-1){
		get_currentuserinfo();
		$IDUser=$current_user->ID;
	}
	$Vis=FALSE;
	$DestTutti=get_option('Circolari_Visibilita_Pubblica');
	if($DestTutti===FALSE)
		$DestTutti=-1;
	$GDes=get_post_meta($IDCircolare, "_destinatari");
	$GDes=unserialize($GDes[0]);
	if (in_array($DestTutti,$GDes))
		$Vis=TRUE;
	else
		foreach($GDes as $Gruppo){
			$Sql=$wpdb->prepare("Select * From ".$table_prefix."groups_user_group Where user_id=%d and group_id=%d",$IDUser,$Gruppo);
			$Records=$wpdb->get_results($Sql,ARRAY_A);
			if (count($Records)==1)
				return TRUE;
		}
	return $Vis;
}
function gcg_FirmaCircolare($IDCircolare,$Pv=-1){
	global $wpdb, $current_user;
	get_currentuserinfo();
	if ( false === $wpdb->insert(
		$wpdb->table_firme_circolari ,array(
				'post_ID' => $IDCircolare,
				'user_ID' => $current_user->ID,
				'ip' => $_SERVER['REMOTE_ADDR'],
				'adesione' => $Pv))){
// echo "Sql==".$wpdb->last_query ."    Ultimo errore==".$wpdb->last_error;
		$err=$wpdb->last_error;
        return "La Circolare Num. ".gcg_GetNumeroCircolare($IDCircolare)." &egrave; gi&agrave; stata Firmata (msg: ".$err.")";
	}else{
		return "Circolare Num. ".gcg_GetNumeroCircolare($IDCircolare)." Firmata correttamente";
	}
}
function gcg_Get_User_Per_Gruppo($IdGruppo){
	global $wpdb,$table_prefix;
	$tabella=$table_prefix."groups_user_group";
	if ($IdGruppo==get_option('Circolari_Visibilita_Pubblica'))
		return 	$wpdb->get_var("Select count(*) FROM $wpdb->users");
	else
		return $wpdb->get_var($wpdb->prepare(
					"Select count(*) FROM $tabella WHERE group_id=%d",
					$IdGruppo));
}
function gcg_Get_Numero_Firme_Per_Circolare($IDCircolare){
	global $wpdb;
	return $wpdb->get_var($wpdb->prepare(
			"Select count(*) FROM $wpdb->table_firme_circolari WHERE post_ID=%d",
			$IDCircolare));
}
function gcg_Circolari_ElencoAnniMesi($urlCircolari){

global $wpdb,$table_prefix;
$Circolari=get_option('Circolari_Categoria');
$PaginaCircolari=get_option('Circolari_Categoria');
$Ritorno="<ul>
";
//echo $tipo."  ".$Categoria."  ".$Anno;
$mesi = array("","Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre","Novembre", "Dicembre");

	$Sql='SELECT year('.$table_prefix.'posts.post_date) as anno  
		FROM '.$table_prefix.'posts JOIN '.$table_prefix.'term_relationships ON '.$table_prefix.'posts.ID = '.$table_prefix.'term_relationships.object_id
                                    JOIN '.$table_prefix.'term_taxonomy ON '.$table_prefix.'term_taxonomy.term_taxonomy_id = '.$table_prefix.'term_relationships.term_taxonomy_id
		WHERE post_type IN ("post","circolari") and post_status="publish" and '.$table_prefix.'term_taxonomy.term_id='.$Circolari.' 
		group by year('.$table_prefix.'posts.post_date)
		order by year('.$table_prefix.'posts.post_date) DESC;';


	$Anni=$wpdb->get_results($Sql,ARRAY_A );

		foreach( $Anni as $Anno){
			$SqlMese='
SELECT month('.$table_prefix.'posts.post_date) as mese  
FROM '.$table_prefix.'posts JOIN '.$table_prefix.'term_relationships ON '.$table_prefix.'posts.ID = '.$table_prefix.'term_relationships.object_id
                            JOIN '.$table_prefix.'term_taxonomy ON '.$table_prefix.'term_taxonomy.term_taxonomy_id = '.$table_prefix.'term_relationships.term_taxonomy_id
WHERE post_type IN ("post","circolari") and post_status="publish" 
	and '.$table_prefix.'term_taxonomy.term_id='.$Circolari.'
	and year('.$table_prefix.'posts.post_date)='.$Anno["anno"].' 
group by month('.$table_prefix.'posts.post_date)
order by month('.$table_prefix.'posts.post_date) DESC;';

			$Ritorno.='<li><a href="'.$urlCircolari.'?Anno='.$Anno["anno"].'" title="link agli articoli dell\'anno '.$Anno["anno"].'">'.$Anno["anno"].'</a></li>';
	
			$Mesi=$wpdb->get_results($SqlMese,ARRAY_A );
			foreach( $Mesi as $Mese){
				$Ritorno.='<li style="margin-left:10px;"><a href="'.$urlCircolari.'?Anno='.$Anno["anno"].'&amp;Mese='.$Mese['mese'].'" title="link agli articoli dell\'anno '.$Anno["anno"].' Mese '.$Mese['mese'].'">'.$mesi[$Mese['mese']].'</a></li>';
			}
		}
$Ritorno.="</ul>";
return $Ritorno;

}
?>