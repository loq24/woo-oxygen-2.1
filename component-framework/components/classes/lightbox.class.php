<?php

/**
 * Lightbox Component Class
 * 
 * @since 2.0
 * @author Louis & Ilya
 */


class Lightbox_block extends CT_Component{

    public $param_array = array();
    public $data_array = array();
    var $js_added = false;

    function __construct($options) {

        // run initialization
        $this->init( $options );

        // Add shortcodes
        add_shortcode( $this->options['tag'], array( $this, 'shortcode' ) );

        for ( $i = 2; $i <= 16; $i++ ) {
            add_shortcode( $this->options['tag'] . "_" . $i, array( $this, 'shortcode' ) );
        }

        // change component button place
        remove_action("ct_toolbar_fundamentals_list", array( $this, "component_button" ) );
        add_action("oxygen_helpers_components_interactive", array( $this, "component_button" ) );

        // output styles
        add_filter("oxygen_default_classes_output", array( $this, "css_output" ) );

        // generate defaults styles class
        add_action("oxygen_default_classes_output", array( $this, "generate_defaults_css" ) );

        // generate user styles class
        add_filter("oxygen_user_classes_output", array( $this, "generate_classes_css"), 10, 7);

        // generate #id stlyes
        add_filter("oxy_component_css_styles", array( $this, "generate_id_css"), 10, 5);

        add_action( 'wp_footer', array( $this, 'output_js' ) );
        add_action( 'wp_head', array( $this, 'output_vendor_css' ) );
    }

    /**
     * Generate CSS based on shortcode params
     * 
     * @since 2.0
     * @author Ilya
     */

    function generate_defaults_css() {
        
        $options  = $this->set_options(
                // use fake JSON object to prevent set_options from stop due to empty object
                // TODO: make proper changes to set_options to avoid this hack
                array("ct_options"=>'{"key":1}')
            );

        $options['selector'] = ".oxy-lightbox";

        echo $this->generate_css($options, true);
    }


    /**
     * Generate CSS for user custom classes
     * 
     * @since 2.0
     * @author Ilya
     */

    function generate_classes_css($css, $class, $state, $options, $is_media = false, $is_selector = false, $defaults) {

        if ($is_selector) {
            return $css;
        }

        $is_component = false;

        foreach ($options as $key => $value) {
            if (strpos($key,"lightbox_")!==false) {
                $is_component = true;
                break;
            }
        }

        $options['selector'] = ".".$class;
        if ($is_component) {
            $css .= $this->generate_css($options, true, $defaults[$this->options['tag']]);
            $is_component = false;
        }

        return $css;
    }


    /**
     * Generate ID styles
     * 
     * @since 2.0
     * @author Ilya
     */

    function generate_id_css($styles, $states, $selector, $class_obj, $defaults) {

        if ($class_obj->options['tag'] != $this->options['tag']){
            return $styles;
        }
        
        $params = $states['original'];
        $params['selector'] = $selector;

        return $styles . $this->generate_css($params, false, $defaults);
    }
       
    /**
     * Generate CSS for arrays parameters only
     * 
     * @since 2.0
     * @author Louis
     */

    function generate_css($params=false, $class=false, $defaults=array()) {

    }

    /**
     * Output vendor css
     * 
     * @since 2.1
     * @author Lougie Q.
     */

    function output_vendor_css() {
        wp_enqueue_style( 'fancybox-css', CT_FW_URI . '/vendor/fancybox/dist/jquery.fancybox.min.css');
    }

    /**
     * Default CSS output
     * 
     * @since 2.0
     * @author Ilya
     */

    function css_output() {

        $defaultcss = file_get_contents(plugin_dir_path(__FILE__)."lightbox/lightbox.css");

        echo $defaultcss;
    }


    /**
     * Shortcode output
     * 
     * @since 2.0
     * @author Louis & Ilya
     */

    function shortcode($atts, $content) {

        $options = $this->set_options( $atts );

        $this->param_array[$options['id']] = $options;

        ob_start(); ?>

        <div id='<?php echo esc_attr($options['selector']); ?>' class='<?php echo esc_attr($options['classes']); ?>'>
          <div class='oxy-lightbox-wrap'>
            <?php var_dump($content); ?>
            <?php echo 'Yeahhhhh!!!'; //$this->output_builtin_shortcodes( $content ); ?>
          </div>
        </div>

        <?php $html = ob_get_clean();

        return $html;
    }


    /**
     * Output JS for toggle menu in responsive mode
     *
     * @since 2.0
     * @author Ilya K.
     */
    
    function output_js() { 
        // include Unslider
        wp_enqueue_script( 'fancybox-js', CT_FW_URI . '/vendor/fancybox/dist/jquery.fancybox.min.js');
        ?>
        <script type="text/javascript">

            jQuery('document').ready(function($) {
                $('[data-fancybox]').fancybox({
                    toolbar  : false,
                    smallBtn : true,
                    iframe : {
                        preload : false,
                        css : {
                            width : '400px'
                        },
                        attr: {
                          scrolling: "no"
                        }
                    }
                });
            });

        </script>
    
    <?php }   
}

/**
* Returns all lightbox posts from `lightbox_woo` post type
*
* @since 2.1
* @author Lougie Q.
*/
function get_all_lightbox_posts(){
    global $post;
    $args = array('numberposts' => -1,'post_type' => 'woo_lightbox' );
    $posts = get_posts( $args );
    $ids = [];
    $ids[''] = "&nbsp;";
    foreach ($posts as $post) {
        $ids[$post->ID] = $post->post_title;
    }
    return $ids;
}

$lightBox = new Lightbox_block( array(
            'name'  => __('Lightbox','oxygen'),
            'tag'   => 'oxy_lightbox',
            'params'=> array(
                 array(
                        "type"          => "dropdown",
                        "heading"       => __("Choose Lightbox Layout", "oxygen"),
                        "param_name"    => "lightbox-layout",
                        "value"         => get_all_lightbox_posts(),
                    )
            ),        

        )
);