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
        add_shortcode( $this->options['tag'], array( $this, 'add_shortcode' ) );

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
     * Add shortcode to WordPress
     *
     * @since 0.1
     */

    function add_shortcode( $atts, $content, $name ) {
        if ( ! $this->validate_shortcode( $atts, $content, $name ) ) {
            return '';
        }

        $options = $this->set_options( $atts );
        
        ob_start(); 
        $maxWidth = esc_attr($options['modal_max_width']);
        $maxHeight = esc_attr($options['modal_max_height']);
        ?><a data-fancybox data-type="iframe" data-src="<?php echo get_permalink(esc_attr($options['lightbox_layout'])); ?>" id="<?php echo esc_attr($options['selector']); ?>" class="<?php echo esc_attr($options['classes']); ?>" href="javascript:;">
                <?php echo do_shortcode( $content ); ?>
            </a>
            <script type="text/javascript">
                jQuery('document').ready(function($) {
                    $("#<?php echo esc_attr($options['selector']); ?>").fancybox({
                        toolbar  : false,
                        smallBtn : true,
                        iframe : {
                            css : {
                                maxWidth : '<?php echo ($maxWidth) ? $maxWidth."px" : '100%'; ?>',
                                maxHeight : '<?php echo ($maxHeight) ? $maxHeight."px" : '100%'; ?>',
                            },
                            attr: {
                              scrolling: "no"
                            }
                        }
                    });
                });
            </script>
        <?php

        return ob_get_clean();
    }


    /**
     * Output JS for toggle menu in responsive mode
     *
     * @since 2.0
     * @author Ilya K.
     */
    
    function output_js($atts) { 
        // include Unslider
        wp_enqueue_script( 'fancybox-js', CT_FW_URI . '/vendor/fancybox/dist/jquery.fancybox.min.js');
    }   
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
                        "param_name"    => "lightbox_layout",
                        "value"         => get_all_lightbox_posts(),
                    ),
                 array(
                        "type"          => "textfield",
                        "heading"       => __("Modal max width"),
                        "param_name"    => "modal_max_width",
                        "value"         => "",
                        "css"           => false,
                    ),
                 array(
                        "type"          => "textfield",
                        "heading"       => __("Modal max height"),
                        "param_name"    => "modal_max_height",
                        "value"         => "",
                        "css"           => false,
                    ),
                 array(
                        "type"          => "content",
                        "param_name"    => "ct_content",
                        "value"         => "Double-click to edit lightbox text link.",
                        "css"           => false,
                    ),
                    array(
                        "type"          => "font-family",
                        "heading"       => __("Font Family", "oxygen"),
                        "css"           => false,
                    ),
                    array(
                        "type"              => "colorpicker",
                        "param_name"        => "color",
                        "heading"           => __("Text Color", "oxygen"),
                        "hide_wrapper_end"  => true,
                    ),
                    array(
                        "type"              => "colorpicker",
                        "param_name"        => "hover_color",
                        "heading"           => __("Hover Color", "oxygen"),
                        "hide_wrapper_start"=> true,
                        "state_condition"   => "!=hover"
                    ),
                    array(
                        "type"          => "slider-measurebox",
                        "heading"       => __("Font Size", "oxygen"),
                        "param_name"    => "font-size",
                    ),
                    array(
                        "type"          => "dropdown",
                        "heading"       => __("Font Weight", "oxygen"),
                        "param_name"    => "font-weight",
                        "value"         => array (
                                            ""      => "&nbsp;",
                                            "100" => "100",
                                            "200" => "200",
                                            "300" => "300",
                                            "400" => "400",
                                            "500" => "500",
                                            "600" => "600",
                                            "700" => "700",
                                            "800" => "800",
                                            "900" => "900",
                                        ),
                    ),
                    array(
                        "type"          => "checkbox",
                        "heading"       => __("Underline", "oxygen"),
                        "label"         => __("Underline link text", "oxygen"),
                        "param_name"    => "text-decoration",
                        "value"         => "none",
                        "true_value"    => "underline",
                        "false_value"   => "none",
                    )
            ),      
            'advanced'  => array(
                "positioning" => array(
                    "values" => array (
                        'display' => 'inline-block',
                    )
                ),
                'typography' => array(
                    'values' => array (
                        'font-size'     => "",
                        'font-weight'   => "",
                    )
                ),
                'allowed_html'      => 'post',
                'allow_shortcodes'  => false,
            ),
            'content_editable' => true,  

        )
);