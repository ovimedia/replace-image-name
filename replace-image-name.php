<?php
/*
Plugin Name: Replace Image Name
Description: Replace the special characters of all image names
Author: Ovi García - ovimedia.es
Author URI: http://www.ovimedia.es/
Text Domain: replace-image-name
Version: 0.1
Plugin URI: https://github.com/ovimedia/replace-image-name
*/

if ( ! defined( 'ABSPATH' ) ) exit; 

if ( ! class_exists( 'replace_image_name' ) ) 
{
	class replace_image_name 
    {        
        function __construct() 
        {   
            add_action( 'init', array( $this, 'rin_load_languages') );
            add_action( 'admin_menu', array( $this, 'rin_admin_menu' )); 
        }

        public function rin_load_languages() 
        {
            load_plugin_textdomain( 'replace-image-name', false, '/'.basename( dirname( __FILE__ ) ) . '/languages/' ); 
        }

        public function rin_admin_menu() 
        {	
            $menu = add_menu_page( 'Replace Image Name', 'Replace Image Name', 'read',  
                                  'replace-image-name', array( $this,'rin_options'), 'dashicons-images-alt2', 70);
        }    

        public function rin_options()
        {           
            setlocale(LC_CTYPE, 'cs_CZ');

            if(isset($_REQUEST["replace_names"] )) 
            {
                $args = array(
                'numberposts' =>   -1,
                'post_type' => "attachment"
                ); 

                $posts = get_posts($args);

                $sizes = get_intermediate_image_sizes(); 

                $uploads = wp_upload_dir(); 
                
                $uploads["basedir"];

                foreach($posts as $post)
                {
                    $post_meta_wp_ataced_file = get_post_meta( $post->ID, "_wp_attached_file", true );

                    if(iconv('UTF-8', 'ASCII//TRANSLIT', $post_meta_wp_ataced_file) != $post_meta_wp_ataced_file)
                    {
                        $post_meta_wp_ataced_alt = get_post_meta( $post->ID, "_wp_attachment_image_alt", true );

                        rename($uploads["basedir"]."/".$post_meta_wp_ataced_file, $uploads["basedir"]."/".iconv('UTF-8', 'ASCII//TRANSLIT', $post_meta_wp_ataced_file) );
                            
                        foreach($sizes as $size)
                        {
                            $img = wp_get_attachment_image_src($post->ID, $size);
                            
                            $newimg = str_replace($uploads["baseurl"], $uploads["basedir"], $img[0]);

                            rename($newimg, iconv('UTF-8', 'ASCII//TRANSLIT', $newimg) );   
                        }    

                        $updates = array(
                            "ID" => $post->ID,
                            "post_title" => iconv('UTF-8', 'ASCII//TRANSLIT', $post->post_title),
                            "post_content" => iconv('UTF-8', 'ASCII//TRANSLIT', $post->post_content),
                            "post_excerpt" => iconv('UTF-8', 'ASCII//TRANSLIT', $post->post_excerpt)
                        );

                        wp_update_post($updates);  

                        update_post_meta( $post->ID, "_wp_attached_file", iconv('UTF-8', 'ASCII//TRANSLIT', $post_meta_wp_ataced_file));
                        update_post_meta( $post->ID, "_wp_attachment_image_alt", iconv('UTF-8', 'ASCII//TRANSLIT', $post_meta_wp_ataced_alt));

                    }
                }

                echo "<p>".translate( 'Images names changed succesfully.', 'replace-image-name' )."</p>";
            }

            ?>

            <form action="<?php echo get_admin_url()."admin.php?page=replace-image-name"; ?>" method="post" >

                <h4><?php echo translate( 'Images with special names.', 'replace-image-name' ); ?></h4>

                <?php 

                $vals = array(
                    'numberposts' =>   -1,
                    'post_type' => "attachment"
                ); 

                $posts = get_posts($vals);

                echo "<table>";
                echo "<tr><td>".translate( 'Original name', 'replace-image-name' )."</td><td>".translate( 'New name', 'replace-image-name' )."</td></tr>";

                foreach($posts as $post)
                {
                    $post_meta_wp_ataced_file = get_post_meta( $post->ID, "_wp_attached_file", true );
                    
                    if(iconv('UTF-8', 'ASCII//TRANSLIT', $post_meta_wp_ataced_file) != $post_meta_wp_ataced_file)
                        echo "<tr><td><p>".$post_meta_wp_ataced_file."</p></td><td><p>".iconv('UTF-8', 'ASCII//TRANSLIT', $post_meta_wp_ataced_file)."</p></td></tr>";                 
                }

                ?>

                </table>

                <input type="hidden" value="replace_names" name="replace_names" id="replace_names" />

                <input type="submit" class="button button-primary"  value="<?php echo translate( 'Replace image names', 'replace-image-name' ); ?>" />

            </form> 

            <?php
        }
    }
}

$GLOBALS['replace_image_name'] = new replace_image_name();   
    
?>
