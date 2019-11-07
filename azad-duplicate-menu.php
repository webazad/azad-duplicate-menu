<?php
/* 
Plugin Name: Azad Duplicate Menu
Description: The easiest way to duplicate menuyour wordpress menu.
Plugin URI: gittechs.com/plugin/azad-duplicate-menu
Author: Md. Abul Kalam Azad
Author URI: gittechs.com/author
Author Email: webdevazad@gmail.com
Version: 0.0.0.1
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: azad-duplicate-menu
Domain Path: /languages
*/

defined( 'ABSPATH' ) || exit;

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
$plugin_data = get_plugin_data( __FILE__ );

define( 'adm_url', plugin_dir_url( __FILE__ ) );
define( 'adm_path', plugin_dir_path( __FILE__ ) );
define( 'adm_plugin', plugin_basename( __FILE__ ) );
define( 'adm_version', $plugin_data['Version'] );
define( 'adm_name', $plugin_data['Name'] );

$azad_duplicate_menu = new Azad_Duplicate_Menu();
class Azad_Duplicate_Menu{
    public function __construct(){
        add_action('admin_menu',array($this,'admin_menu'));
    }
    public function admin_menu(){
        add_theme_page(__('Duplicate Menu','azad-duplicate-menu'),__('Duplicate Menu','azad-duplicdate-menu'),'edit_theme_options','duplicate-menu',array($this,'options_screen'));
    }
    public function duplicate($id=null,$name=null){
        if(empty($id) || empty($name)){
            return false;
        }
        $id = intval($id);
        $name = sanitize_text_field($name);
        $source = wp_get_nav_menu_object($id);
        $source_items = wp_get_nav_menu_items($id);
        $new_id = wp_create_nav_menu($name);

        if(! $new_id){
            return false;
        }
        $rel = array();
        $i = 1;
        foreach($source_items as $menu_item){
            $args = array(
                'menu-item-db-id'       => $menu_item->db_id,
                'menu-item-object-id'   => $menu_item->object_id,
                'menu-item-object'      => $menu_item->object,
                'menu-item-position'    => $i,
                'menu-item-type'        => $menu_item->type,
                'menu-item-title'       => $menu_item->title,
                'menu-item-url'         => $menu_item->url,
                'menu-item-description' => $menu_item->description,
                'menu-item-attr-title'  => $menu_item->attr_title,
                'menu-item-target'      => $menu_item->target,
                'menu-item-classes'     => implode(' ',$menu_item->classes),
                'menu-item-xfn'         => $menu_item->xfn,
                'menu-item-status'      => $menu_item->post_status
            );
            $parent_id = wp_update_nav_menu_item($new_id, 0, $args);
            $rel[$menu_item->db_id] = $parent_id;
            if($menu_item->menu_item_parent){
                $args['menu-item-parent-id'] = $rel[$menu_item->menu_item_paernt];
                $parent_id = wp_update_nav_menu_item($new_id, $parent_id, $args);
            }
            // Allow developers to run any custom functionality they'd like.
            do_action('duplicate_menu_item',$menu_item, $args); 
            $i++;
        }
        return $new_id;
    }
    public function options_screen(){ 
        $nav_menus = wp_get_nav_menus();
        ?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32"><br/></div>
            <h2 class=""><?php _e('Duplicate Menu','azad-duplicate-menu'); ?></h2>
            <?php if(!empty($_POST) && wp_verify_nonce($_POST['duplicate_menu_nonce'],'duplicate_menu')) : 
            
                $source = intval($_POST['source']);
                $destination = sanitize_text_field($_POST['new_menu_name']);

                $new_menu_id = $this->duplicate($source,$destination);
            ?>
                <div id="message" class="updated">
                <p>
                <?php
                    if($new_menu_id){
                        _e('Menu duplicated.','azad-duplicate-menu');
                    }else{
                        _e('There was a problem duplicating your menu. No action was taken...','azad-duplicate-menu');
                    }
                ?></p>
                </div>                
            <?php endif; ?>
            <?php if(empty($nav_menus)): ?>
                <p><?php _e('You have not created any nav menu yet...','azad-duplicate-menu'); ?></p>
            <?php else: ?>
                <form method="post" action="">
                    <?php wp_nonce_field('duplicate_menu','duplicate_menu_nonce'); ?>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">
                                <label for="source"><?php _e('Duplicate this menu','azad-duplicate-menu'); ?></label>
                            </th>
                            <td>
                                <select name="source">
                                    <?php foreach((array)$nav_menus as $nav_menu) : ?>
                                        <option value="<?php echo esc_attr($nav_menu->term_id); ?>">
                                            <?php echo esc_html($nav_menu->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span><?php _e('And call it ','azad-duplicate-menu'); ?></span>
                                <input name="new_menu_name" type="text" id="new_menu_name" class="regular-text" value=""/>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" name="submit" id="submit" class="button-primary" value="Duplicate Menu"/>
                    </p>
                </form>
            <?php endif; ?>
        </div>
    <?php }    
    public function __destruct(){}
}