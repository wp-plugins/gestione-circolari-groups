<?php
/**
 * Gestione Circolari Groups- Widget
 * 
 * @package Gestione Circolari Groups
 * @author Scimone Ignazio
 * @copyright 2011-2014
 * @ver 2.2
 */

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

class NavCircolariWidget extends WP_Widget
{
	public function __construct()
	{
	   parent::WP_Widget("nav_circolari", 'Navigazione Circolari', array('description' => 'Utilizzare questo widget per implementare nel lato pubblico del sito l\'archivio per Anni/Mesi delle circolari codificate',array( 'width' => 300, 'height' => 350)));
	 }
    
	public function form($instance)
    {
    
	 $defaults = array(
 		'titolo' => 'Archivio Circolari',
		'pagina_circolari' => NULL
        );
        $instance = wp_parse_args( (array) $instance, $defaults );?>
        <p>
            <label for="<?php echo $this->get_field_id( 'titolo' ); ?>">
                Titolo widget:
            </label>
            <input type="text" id="<?php echo $this->get_field_id( 'titolo' ); ?>" name="<?php echo $this->get_field_name( 'titolo' ); ?>" value="<?php echo $instance['titolo']; ?>" size="30" />
        </p>
		<p>
            <label for="<?php echo $this->get_field_id( 'pagina_circolari' ); ?>">
               Pagina Circolari:
            </label>
		<select id="<?php echo $this->get_field_id( 'pagina_circolari' ); ?>" name="<?php echo $this->get_field_name( 'pagina_circolari' ); ?>" style="width:270px;"> 
		 <option value=""><?php echo esc_attr( __( 'Select page' ) ); ?></option> 
		 <?php 
		  $pages = get_pages(); 
		  foreach ( $pages as $pagg ) {
		    if (get_page_link( $pagg->ID ) == $instance['pagina_circolari'] ) 
				$Selezionato= 'selected="selected"';
			else
				$Selezionato="";
		  	$option = '<option '.$Selezionato.' value="' . get_page_link( $pagg->ID ) . '">';
			$option .= $pagg->post_title;
			$option .= '</option>';
			echo $option;
		  }
		 ?>
		</select>
        </p>
       <?php
    }


public function widget( $args, $instance )
    {
 		global $post;
 	    extract( $args );
		$CurPage='http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		if (substr($CurPage,0,strlen($instance['pagina_circolari']))!=$instance['pagina_circolari'])
			return;
        $titolo = apply_filters('widget_title', $instance['titolo'] );
		if ($titolo=='')
			$titolo="Circolari";
		echo $before_widget;
        echo $before_title .$titolo. $after_title;
		echo gcg_Circolari_ElencoAnniMesi($instance['pagina_circolari']);
 	   echo $after_widget;
    }

public function update( $new_instance, $old_instance )
	{
			$instance = $old_instance;
	        $instance['titolo'] = strip_tags( $new_instance['titolo'] );
	        $instance['pagina_circolari'] = strip_tags( $new_instance['pagina_circolari'] );
			return $instance;
	}
}	
function circolariG_NavCircolariWidget_register()
{
    register_widget( 'NavCircolariWidget' );
}

add_action( 'widgets_init', 'circolariG_NavCircolariWidget_register' );


?>