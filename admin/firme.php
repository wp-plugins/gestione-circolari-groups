<?php 
/**
 * Gestione Circolari Groups- Funzioni Gestione Firme
 * 
 * @package Gestione Circolari Groups
 * @author Scimone Ignazio
 * @copyright 2011-2014
 * @ver 1.0
 */
 
function circolariG_GestioneFirme()
{
global $msg;
echo'
		<div class="wrap">
			<img src="'.Circolari_URL.'/img/atti32.png" alt="Icona Firma" style="display:inline;float:left;margin-top:10px;"/>
		<h2 style="margin-left:40px;">Circolari da Firmare</h2>
		</div>';
if($msg!="") 
	echo '<div id="message" class="updated"><p>'.$msg.'</p></div>';
		circolariG_VisualizzaTabellaCircolari();		
}

function circolariG_VisualizzaTabellaCircolari(){
$Circolari=get_option('Pasw_Comunicazioni');
$NumCircolari =gcg_GetNumCircolariDaFirmare("N");
$NumPagine=intval($NumCircolari/get_option('Circolari_NumPerPag'));	
if ($NumPagine<$NumCircolari/get_option('Circolari_NumPerPag'))
	$NumPagine++;
if ($NumPagine>1){
	$mTop="0";
	if (!isset($_GET['npag'])){
		$CurPage=1;
		$OSPag=0;
	}else{
		$OSPag=($_GET['npag']-1)*get_option('Circolari_NumPerPag');
		$CurPage=$_GET['npag'];
	}
	if ($CurPage==1){
		$Dietro=" disabled";
		$Pre=1;	
	}else{
		$Dietro="";
		$Pre=$CurPage-1;			
	}
	if ($CurPage==$NumPagine){
		$Avanti=" disabled";
		$Suc=$NumPagine;
	}else{
		$Avanti="";
		$Suc=$CurPage+1;
	}
		
	echo '
	<div class="tablenav top">
		<div class="tablenav-pages">
			<span class="displaying-num">'.$NumCircolari.' circolari</span>
			<span class="pagination-links">
				<a class="first-page'.$Dietro.'" title="Vai alla prima pagina" href="'.get_bloginfo("wpurl").'/wp-admin/edit.php?post_type=circolari&page=Firma">&laquo;</a>
				<a class="prev-page'.$Dietro.'" title="Torna alla pagina precedente." href="'.get_bloginfo("wpurl").'/wp-admin/edit.php?post_type=circolari&page=Firma&npag='.$Pre.'">&lsaquo;</a>
			<span class="paging-input">
				<input class="current-page" title="Pagina corrente." type="text" name="paged" value="'.$CurPage.'" size="2" /> di <span class="total-pages">'.$NumPagine.'</span>
			</span>
				<a class="next-page'.$Avanti.'" title="Vai alla pagina successiva" href="'.get_bloginfo("wpurl").'/wp-admin/edit.php?post_type=circolari&page=Firma&npag='.$Suc.'">&rsaquo;</a>
				<a class="last-page'.$Avanti.'" title="Vai all&#039;ultima pagina" href="'.get_bloginfo("wpurl").'/wp-admin/edit.php?post_type=circolari&page=Firma&npag='.$NumPagine.'">&raquo;</a>
			</span>
		</div>
	</div>';
}else{
	$mTop="20";
}
$Posts = get_posts('post_type=circolari&posts_per_page='.get_option('Circolari_NumPerPag').'&offset='.$OSPag);

echo '
<div style="width:100%;margin-top:'.$mTop.'px;">
	<table class="widefat">
		<thead>
			<tr>
				<th style="width:5%;">N°</th>
				<th style="width:45%;">Titolo</th>
				<th style="width:15%;">Tipo</th>
				<th style="width:20%;">Firma</th>
				<th>Data</th>
			</tr>
		</thead>
		<tboby>';
foreach($Posts as $post){
	$Adesione=get_post_meta($post->ID, "_sciopero");
	if (gcg_Is_Circolare_per_User($post->ID)){
		$TipoCircolare="Circolare";
		$Campo_Firma_Adesione="";
		if ($Adesione[0]=="Si"){			
			$TipoCircolare="Circolare con Adesione";
			switch (gcg_get_Circolare_Adesione($post->ID)){
			case 1:
				$Campo_Firma_Adesione=": adesione Si";
				break;
			case 2:
				$Campo_Firma_Adesione=": adesione No";		
				break;
			case 3:
				$Campo_Firma_Adesione=": adesione Presa Visione";				
				break;
			}
		}	
		$firma=get_post_meta($post->ID, "_firma");
		$BaseUrl=admin_url()."edit.php";
		setup_postdata($post);
		if($firma[0]=="Si"){
			if (gcg_Is_Circolare_Firmata($post->ID)){
				$Campo_Firma="Firmata".$Campo_Firma_Adesione;
			}
			else{
				if ($Adesione[0]=="Si"){			
					$Campo_Firma='<form action="'.$BaseUrl.'"  method="get" style="display:inline;">
						<input type="hidden" name="post_type" value="circolari" />
						<input type="hidden" name="page" value="Firma" />
						<input type="hidden" name="op" value="Adesione" />
						<input type="hidden" name="pid" value="'.$post->ID.'" />
						<input type="radio" name="scelta" class="s1-'.$post->ID.'" value="1"/>Si 
						<input type="radio" name="scelta" class="s2-'.$post->ID.'" value="2"/>No 
						<input type="radio" name="scelta" class="s3-'.$post->ID.'" value="3" checked="checked"/>Presa Visione
						<input type="submit" name="inviaadesione" class="button inviaadesione" id="'.$post->ID.'" value="Firma" rel="'.$post->post_title.'"/>
					</form>';
				}else
					$Campo_Firma='<a href="'.$BaseUrl.'?post_type=circolari&page=Firma&op=Firma&pid='.$post->ID.'">Firma Circolare</a>';
			}
			$dati_firma=gcg_get_Firma_Circolare($post->ID);
			echo "
					<tr>
						<td> ".gcg_GetNumeroCircolare($post->ID)."</td>
						<td>
						<a href='".get_permalink( $post->ID )."'>
						$post->post_title
						</a>
						</td>
						<td>$TipoCircolare</td>
						<td>$Campo_Firma</td>
						<td>$dati_firma->datafirma</td>
					</tr>";
		}	
	}
}
echo '
			</tbody>
		</table>
	</div>';
}
?>