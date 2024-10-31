<?php
/**
 * Plugin Name: RDP Wiki-Press Embed
 * Plugin URI: http://www.robert-d-payne.com/
 * Description: Enables the inclusion of MediaWiki pages and PediaPress book pages into your own blog page or post through the use of shortcodes. Forked from: <a href="http://wordpress.org/plugins/rdp-wiki-press-embed/" target="_blank">Wiki Embed plugin</a>.
 * Version: 2.4.7
 * Author: Robert D Payne
 * Author URI: http://www.robert-d-payne.com/
 *
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU 
 * General Public License as published by the Free Software Foundation; either version 2 of the License, 
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without 
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, write 
 * to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA * This program is free software; you can redistribute it and/or modify it under the terms of  the GNU 
 * General Public License as published by the Free Software Foundation; either version 2 of the License, 
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without 
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, write 
 * to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 * 
/--------------------------------------------------------------------\
|                                                                    |
| License: GPL                                                       |
|                                                                    |
| WikiEmbed - embed multiple mediawiki page into your post or page   |
| Copyright (C) 2008, OLT, www.olt.ubc.com                   	     |
| All rights reserved.                                               |
|                                                                    |
| This program is free software; you can redistribute it and/or      |
| modify it under the terms of the GNU General Public License        |
| as published by the Free Software Foundation; either version 2     |
| of the License, or (at your option) any later version.             |
|                                                                    |
| This program is distributed in the hope that it will be useful,    |
| but WITHOUT ANY WARRANTY; without even the implied warranty of     |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
| GNU General Public License for more details.                       |
|                                                                    |
| You should have received a copy of the GNU General Public License  |
| along with this program; if not, write to the                      |
| Free Software Foundation, Inc.                                     |
| 51 Franklin Street, Fifth Floor                                    |
| Boston, MA  02110-1301, USA                                        |   
|                                                                    |
\--------------------------------------------------------------------/
*/
define('RDP_WE_PLUGIN_BASENAME', plugin_basename(__FILE__));
$dir = plugin_dir_path( __FILE__ );
define('RDP_WE_PLUGIN_BASEDIR', $dir);
/* Call-to-Action button default values */
define('PPE_CTA_BUTTON_TEXT', 'Download FREE eBook Edition');
define('PPE_CTA_BUTTON_WIDTH', '250');
define('PPE_CTA_BUTTON_TOP_COLOR', '#eded00');
define('PPE_CTA_BUTTON_BOTTOM_COLOR', '#bd7f04');
define('PPE_CTA_BUTTON_FONT_COLOR', '#ffffff');
define('PPE_CTA_BUTTON_FONT_HOVER_COLOR', '#444444');
define('PPE_CTA_BUTTON_BORDER_COLOR', '#eda933');
define('PPE_CTA_BUTTON_BOX_SHADOW_COLOR', '#fed897');
define('PPE_CTA_BUTTON_TEXT_SHADOW_COLOR', '#cd8a15');
    
// admin side 
if(is_admin()){
    require( 'admin/admin-overlay.php' );
    require( 'admin/admin.php' );    
}

class Wiki_Embed {
    static $instance;
    public $options; // GLOBAL Options 
    public $version; 
    public $content_count; // wiki content count needed by the shortcode 
    public $wikiembeds; 

    public $pre_load_scripts;
    public $load_scripts;

    public $tabs_support;
    public $accordion_support;

    /**
     * __construct function.
     * 
     * @access public
     * @return void
     */
    function __construct() {
            self::$instance = $this;

            // set the default wiki embed value if the ones from the Options are not set
            $this->options       = shortcode_atts( $this->default_settings(), get_option( 'wikiembed_options' ) );
            $this->wikiembeds    = get_option( 'wikiembeds' ); // we might not need to load this here at all...
            if(empty($this->wikiembeds))$this->wikiembeds = array();
            
            $this->content_count = 0; 
            $this->version       = '2.4.7';
            

            add_action( 'init', array( $this, 'init' ) );
            if(is_admin())return;
            // display a page when you are clicked from a wiki page
            add_action( 'template_redirect', array( $this, 'load_page' ) );
            add_filter( 'posts_join', array( $this, 'search_metadata_join' ) );
            add_filter( 'posts_where', array( $this, 'search_metadata_where' ) );
            add_filter( 'sf_posts_query', array( $this, 'search_metadata_ajaxy' ) );
    }
    
   /**
     * enqueueStyles function.
     * 
     * @access public
     * @return void
     */    
    function enqueue_styles(){
        $this->tabs_support = get_theme_support('tabs');
        $this->accordion_support = get_theme_support( 'accordions' );

        if ( $this->tabs_support[0] == 'twitter-bootstrap' || $this->accordion_support[0] == 'twitter-bootstrap' ) {
                require_once( 'support/twitter-bootstrap/action.php' );
        }

        if ( $this->tabs_support[0] == 'twitter-bootstrap' ) {
                wp_register_script( 'twitter-tab-shortcode' , plugins_url('support/twitter-bootstrap/twitter.bootstrap.tabs.js', __FILE__), array( 'jquery' ), '1.0', true );
        }        
        
        // ADD styling 
        $this->options['tabs-style'] = ( empty( $this->tabs_support ) ? $this->options['tabs-style'] : 0 );
        $this->options['accordion-style'] = ( empty( $this->accordion_support ) ? $this->options['accordion-style'] : 0 );

        // embed this if tabs enabled style
        if ( $this->options['tabs-style'] ) {
                wp_enqueue_style( 'wiki-embed-tabs', plugins_url( '/rdp-wiki-press-embed/resources/css/tabs.css' ), false, $this->version ); 		
        }

        if ( $this->options['accordion-style'] ) {
                wp_enqueue_style( 'wiki-embed-accordion', plugins_url( '/rdp-wiki-press-embed/resources/css/accordion.css' ), false, $this->version ); 
        }

        // add some great wiki styling 
        if ( $this->options['style'] ) {
                wp_enqueue_style( 'wiki-embed-style', plugins_url( '/rdp-wiki-press-embed/resources/css/wiki-embed.css' ), false, $this->version, 'screen' );
        }
        
        $filename = get_stylesheet_directory() . '/wiki.custom.css';
        if (file_exists($filename)) {
            wp_register_style( 'rdp-we-style-custom', get_stylesheet_directory_uri() . '/wiki.custom.css' );
            wp_enqueue_style( 'rdp-we-style-custom' );
        }          
        
    }//enqueue_styles
    

    /**
     * register_scripts function.
     * 
     * @access public
     * @return void
     */
    function register_scripts($url) {
            wp_register_script( 'wiki-embed-tabs', plugins_url( '/rdp-wiki-press-embed/resources/js/tabs.js' ), array( "jquery", "jquery-ui-tabs" ), $this->version, true );
            wp_register_script( 'wiki-embed-accordion', plugins_url( '/rdp-wiki-press-embed/resources/js/accordion.js' ), array( "jquery", "jquery-ui-accordion" ), $this->version, true );

            $params = array('target_url' => $url);
            
            switch ( $this->options['wiki-links'] ) {
                    case "overlay":
                        // embed this if tabs enabled
                        wp_register_script( 'jquery-colorbox', plugins_url( '/rdp-wiki-press-embed/resources/js/jquery.colorbox.min.js'),array("jquery"), "1.3.20.2", true );
                        wp_register_script( 'wiki-embed-overlay', plugins_url( '/rdp-wiki-press-embed/resources/js/overlay.js'),array( "jquery-colorbox", "jquery" ), $this->version, true );
                        
                        $params['ajaxurl'] = admin_url('admin-ajax.php');
                        
                        wp_localize_script( 'wiki-embed-overlay', 'WikiEmbedSettings', $params );
                        wp_enqueue_style( 'jquery-colorbox', plugins_url( '/rdp-wiki-press-embed/resources/css/colorbox.css'),false, $this->version, 'screen');
                        $this->pre_load_scripts[] = 'jquery-colorbox';
                        $this->pre_load_scripts[] = 'wiki-embed-overlay';
                        break;
                    case "new-page":
                        wp_register_script( 'wiki-embed-new-page', plugins_url( '/rdp-wiki-press-embed/resources/js/new-page.js' ), array( "jquery" ), $this->version, true );
                        $this->pre_load_scripts[] = 'wiki-embed-new-page';
                        $params['siteurl'] = get_site_url();
                        $params['ajaxurl'] = admin_url('admin-ajax.php');
                        wp_localize_script( 'wiki-embed-new-page', 'WikiEmbedSettings', $params );

                        if ( current_user_can( 'publish_pages' ) || current_user_can('unfiltered_html') ) {
                                wp_register_script( 'wiki-embed-site-admin', plugins_url( '/rdp-wiki-press-embed/resources/js/site-admin.js'),array( "jquery", 'wiki-embed-new-page' ), $this->version, true );
                                $this->pre_load_scripts[] = 'wiki-embed-site-admin';
                        }
                        break;
                    case 'overwrite':
                        wp_register_script( 'wiki-embed-overwrite', plugins_url( '/rdp-wiki-press-embed/resources/js/wiki-embed-overwrite.js' ), array( "jquery" ), $this->version, true );
                        wp_localize_script( 'wiki-embed-overwrite', 'WikiEmbedSettings', $params );
                        $this->pre_load_scripts[] = 'jquery-query';
                        $this->pre_load_scripts[] = 'wiki-embed-overwrite';
                        break;	                    
                    default:
            }

    }//register_scripts

    /**
     * print_scripts function.
     * 
     * @access public
     * @return void
     */
    function print_scripts() {
            if ( ! is_array( $this->load_scripts ) ) {
                    return;
            }

            foreach ( $this->load_scripts as $script ) {
                    wp_print_scripts( $script );
            }
    }

    /**
     * init function.
     * 
     * @access public
     * @return void
     */
    function init() {
        // ajax stuff needed for the overlay	
        if ( defined( 'DOING_AJAX' ) ) {
            add_action( 'wp_ajax_wiki_embed', array( $this, 'overlay_ajax' ) );
            add_action( 'wp_ajax_nopriv_wiki_embed', array( $this, 'overlay_ajax' ) );
        }        

        $this->enqueue_styles();

        add_filter( 'page_link', array( $this, 'page_link' ) );

        // wiki embed shortcode
        require_once 'resources/rdpWEPPE.php';
        add_shortcode( 'wiki-embed', array( $this, 'shortcode' ) );
        
        // pediapress gallery shortcode
        require_once 'resources/rdpWEPPGallery.php';
        $oRDP_WE_PPGALLERY = new RDP_WE_PPGALLERY($this->version, $this->options);

        add_action( 'wp_footer', array( $this, 'print_scripts' ) );
        add_action( 'save_post', array( $this, 'save_meta'), 10, 3 );
        
        $this->customRSS();
        
        if (is_admin() ) return;
        if(!wp_script_is( 'jquery-url', 'registered' )){
            wp_register_script( 'jquery-url', plugins_url( 'resources/js/url.min.js' , __FILE__ ), array( 'jquery','jquery-query' ), '1.0', TRUE );
            wp_enqueue_script( 'jquery-url');
        }        
        // global wiki content replace
        $fGlobalCR = (isset($this->options['default']['global-content-replace']))? $this->options['default']['global-content-replace'] : 0;
        if(!is_numeric($fGlobalCR))$fGlobalCR = 0;
        $text_string = empty($this->options['security']['whitelist'])? '' : $this->options['security']['whitelist'];
        if($fGlobalCR && !empty($text_string)){

            wp_enqueue_script( 'rdp-wcr', plugins_url( 'resources/js/script.wcr.js' , __FILE__ ), array( 'jquery','jquery-query','jquery-url' ), '1.0', TRUE);
            $str = preg_replace('#\s+#',',',trim($text_string));
            $params = array(
                'domains' => $str
            );
            wp_localize_script( 'rdp-wcr', 'rdp_wcr', $params ); 
            add_filter( 'the_content', array( &$this, 'content' ),101 );

        }

        if($this->options['wiki-links'] == 'overwrite')add_filter( 'template_include', array( &$this, 'page_template' ), 99 );

    }//init
    
function customRSS(){
        add_feed('pediapress',  array( $this, 'customRSSFunc' ));
}  

function customRSSFunc(){
    $termIDs = '';
    $catNames = array();
    $tagNames = array();
    global $wp_query;
    
    foreach($wp_query->tax_query->queries as $taxQuery){
        switch ($taxQuery['field']) {
            case 'term_id':
                foreach($taxQuery['terms'] as $termID){
                    $oTerm = get_term_by('id', $termID, $taxQuery['taxonomy']);
                    if(!empty($oTerm)){
                        if(strlen($termIDs) > 0) $termIDs.= ',';
                        $termIDs.= $termID;
                        if($taxQuery['taxonomy'] == 'category')$catNames[] = $oTerm->name;
                        if($taxQuery['taxonomy'] == 'post_tag')$tagNames[] = $oTerm->name;
                    }
                }

                break;
            case 'slug':
                foreach($taxQuery['terms'] as $termSlug){
                    $oTerm = get_term_by('slug', $termSlug, $taxQuery['taxonomy']);
                    if(!empty($oTerm)){
                        if(strlen($termIDs) > 0) $termIDs.= ',';
                        $termIDs .= $oTerm->term_id;
                        if($taxQuery['taxonomy'] == 'category')$catNames[] = $oTerm->name;
                        if($taxQuery['taxonomy'] == 'post_tag')$tagNames[] = $oTerm->name;                        
                    }
                }
                break;
            case 'name':
                foreach($taxQuery->terms as $termName){
                    $oTerm = get_term_by('name', $termName, $taxQuery['taxonomy']);
                    if(!empty($oTerm)){
                        if(strlen($termIDs) > 0) $termIDs.= ',';
                        $termIDs .= $oTerm->term_id;
                        if($taxQuery['taxonomy'] == 'category')$catNames[] = $oTerm->name;
                        if($taxQuery['taxonomy'] == 'post_tag')$tagNames[] = $oTerm->name;                        
                    }
                }
                break;                
                
            default:
                break;
        }
        
    }

    header('Content-Type: '.feed_content_type('rss-http').'; charset='.get_option('blog_charset'), true);
    $sRSS = '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; 
    $sRSS .= '<rss version="2.0"><channel>';
    $sChannelTitle = get_bloginfo('name') . ' PediaPress Feed';
    $nCatNames = count($catNames);
    $sChannelDescription = 'PediaPress Feed';

    if($nCatNames):
        switch ($nCatNames) {
            case 1:
                $sChannelDescription .= " for the " . $catNames[0] . " Category" ;
                break;
            default:
                $sChannelDescription .= " for the " . implode(', ', $catNames) . " Categories" ;
                break;
        }    
    endif;

    $nTagNames = count($tagNames);
    if($nCatNames && $nTagNames)$sChannelDescription .= ' And/Or';

    if($nTagNames):
        switch ($nTagNames) {
            case 1:
                $sChannelDescription .= " for the "  . $tagNames[0] ." Tag";
                break;
            default:
                break;
                $sChannelDescription .= " for the " . implode(', ', $tagNames) . " Tags" ;        
        }    
    endif;


    $sRSS .= "<title><![CDATA[$sChannelTitle]]></title>";
    $Path=$_SERVER['REQUEST_URI'];
    $URI=site_url().$Path;
    $sRSS .= "<link><![CDATA[{$URI}]]></link>";
    $sRSS .= "<description>{$sChannelDescription}</description>";
    $ESTTZ = new DateTimeZone('America/New_York');
    $d1=new DateTime();
    $d1->setTimezone($ESTTZ);
    $pubDate = $d1->format(DateTime::RSS);
    $sRSS .= "<pubDate>{$pubDate}</pubDate>";




    $sFetchSQL = RDP_WE_PPGALLERY::buildFetchSQL($termIDs, 0, $this->options['books-per-rss'],'post_date','DESC');
    global $wpdb;
    $rows = $wpdb->get_results($sFetchSQL);

    $description = <<<EOD
<div id="rdp-pp-rss-%%PostID%%" class="rdp-pp-rss-box">   
<div>
    <p style="float: left;margin: 0 3px 0 0" class="cover-image-container">
        <a id="ppe-cover-link-%%PostID%%" href="%%PostLink%%" class="ppe-cover-link" postid="%%PostID%%">
            <img class="coverImage" src="%%Image%%" alt="%%Title%%" border="0" width="118" height="174" onerror="this.style.display='none'" />
        </a>
    </p>
<div class="rdp-pp-rss-metadata-container" style="min-height: 180px;">
    <p class="title-container meta" style="font-size: 12px;line-height: normal;margin: 0px 3px 3px 0px;padding: 0px;"><span class="title">%%FullTitle%%</span></p>
    <p class="editor-container meta" style="font-size: 12px;line-height: normal;margin: 0px 3px 3px 0px;padding: 0px;"><b>Editor:</b><br><span class="editor">%%Editor%%</span></p>
    <p class="language-container meta" style="font-size: 12px;line-height: normal;margin: 0px 3px 3px 0px;padding: 0px;"><b>Language:</b><br><span class="editor">%%Language%%</span></p>    
    <p class="book-size-container meta" style="font-size: 12px;line-height: normal;margin: 0px 3px 3px 0px;padding: 0px;"><b>Book Size:</b><br><span class="book-size">%%BookSize%%</span></p>
</div>
</div>   
</div><!-- .weppgallery-box -->
<div class="clear "rdp-pp-rss-row-sep" style="height: 2px;background: none;"></div>
EOD;

    foreach($rows as $row):
        $sRSS .= '<item>';
        $contentPieces = unserialize($row->option_value);
        $sDownloadLink = get_post_meta( $row->ID, 'wiki_press_download_url', true );            
        $sImgSrc = (!empty($contentPieces['cover_img_src']))? $contentPieces['cover_img_src'] : '';
        $sTitle = (!empty($contentPieces['title']))? $contentPieces['title'] : '';
        $sSubtitle = (!empty($contentPieces['subtitle']))? $contentPieces['subtitle'] : '';
        $FullTitle = (!empty($contentPieces['subtitle']))? $sTitle . ': ' . $sSubtitle : $sTitle;
        $sEditor = (!empty($contentPieces['editor']))? $contentPieces['editor'] : '';
        $sLanguage = (!empty($contentPieces['language']))? $contentPieces['language'] : '';
        $sPriceCurrency = (!empty($contentPieces['price_currency']))? $contentPieces['price_currency'] : '';
        $sPriceAmount = (!empty($contentPieces['price_amount']))? $contentPieces['price_amount'] : '';
        $sBookSize = (!empty($contentPieces['book_size']))? $contentPieces['book_size'] : '';

        $sPostLink = get_permalink($row->ID);
        $sExcerpt = wp_trim_words( $row->post_excerpt, 40, '&hellip; <a href="'. $sPostLink .'">Read More</a>' );
        $title = self::entitiesPlain($row->post_title);

        $sRSS .= "<title><![CDATA[{$title}]]></title>";
        $sRSS .= "<link><![CDATA[{$sPostLink}]]></link>";
        $sRSS .= "<guid isPermaLink='true'><![CDATA[{$sPostLink}]]></guid>"; 

        $sGalleryItem = str_replace (array ( 
            '%%Image%%', 
            '%%Title%%' , 
            '%%Subtitle%%' , 
            '%%Editor%%',
            '%%Language%%',
            '%%PriceCurrency%%',
            '%%PriceAmount%%',
            '%%PostID%%',
            '%%Excerpt%%',
            '%%FullTitle%%',
            '%%PostLink%%',
            '%%BookSize%%') , 
            array ( 
            $sImgSrc, 
            $sTitle, 
            $sSubtitle, 
            $sEditor,
            $sLanguage,
            $sPriceCurrency,
            $sPriceAmount,
            $row->ID,
            $sExcerpt,
            $FullTitle,
            $sPostLink,
            $sBookSize), 
            $description );
        $sGalleryItem = self::entitiesPlain($sGalleryItem);
        $sRSS .= "<description><![CDATA[{$sGalleryItem}]]></description>";

        $d1=new DateTime($row->post_date);
        $d1->setTimezone($ESTTZ);
        $pubDate = $d1->format(DateTime::RSS);
        $sRSS .= "<pubDate>{$pubDate}</pubDate>";
        $sRSS .= '</item>';        
    endforeach;
    $sRSS .= '</channel>';
    $sRSS .= '</rss>';
    echo $sRSS;
    exit;
}//customRSSFunc

    static function entitiesPlain($string){
        return str_replace ( array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&quest;',  '&#39;' ), array ( '&', '"', "'", '<', '>', '?', "'" ), $string ); 
    }
    
    function save_meta( $post_id, $post, $update ) {
        if ( !has_shortcode( $post->post_content, 'wiki-embed' ) ) return; 
        $sKey  = get_post_meta($post_id, RDP_WE_PPE::$postMetaKey, true);
        delete_transient($sKey);
        delete_post_meta($post_id, RDP_WE_PPE::$postMetaKey);
        $var = do_shortcode( $post->post_content );
    }//save_meta

    function content( $content ) {
        if(!isset($_GET['wikiembed-override-url']))return $content;

        $code = "[wiki-embed url={$_GET['wikiembed-override-url']}]";
        $content = do_shortcode($code);

        /* Return the content. */
        return $content;
    }//content


    function page_template( $template ) {
        if(!isset($_GET['wikiembed-override-url']))return $template;
        $sRequestedTemplate = isset($this->options['default']['global-content-replace-template'])? $this->options['default']['global-content-replace-template'] : '';
        if(empty($sRequestedTemplate) || $sRequestedTemplate == 'same')return $template;
        $new_template = locate_template( array( $sRequestedTemplate ) );
        if ( '' != $new_template ) {
               $template = $new_template ;
        }

        return $template;
    }

    /**
     * settings function.
     * default settings
     * @access public
     * @return void
     */
    function default_settings() {
            return array(
                    'tabs'            => 1,
                    'accordians'      => 1,
                    'unreferenced-boxes-style'   => 1,
                    'style'           => 1,
                    'tabs-style'      => 0,
                    'accordion-style' => 0,
                    'wiki-update'     => "30", /* minutes */
                    'wiki-links'      => "default",
                    'wiki-links-new-page-email' => "",
                    'toc-links'      => "default",
                    'toc-show'      => 1,
                    'books-per-rss' => "10",
                    'ppe-beneath-cover-content' => '',
                    'ppe-cta-button-content' => '',
                    'ppe-cta-button-text' => PPE_CTA_BUTTON_TEXT,
                    'ppe-cta-button-width' => PPE_CTA_BUTTON_WIDTH,                
                    'ppe-cta-button-top-color' => PPE_CTA_BUTTON_TOP_COLOR,
                    'ppe-cta-button-bottom-color' => PPE_CTA_BUTTON_BOTTOM_COLOR,
                    'ppe-cta-button-font-color' => PPE_CTA_BUTTON_FONT_COLOR,
                    'ppe-cta-button-font-hover-color' => PPE_CTA_BUTTON_FONT_HOVER_COLOR,
                    'ppe-cta-button-border-color' => PPE_CTA_BUTTON_BORDER_COLOR,
                    'ppe-cta-button-box-shadow-color' => PPE_CTA_BUTTON_BOX_SHADOW_COLOR,
                    'ppe-cta-button-text-shadow-color' => PPE_CTA_BUTTON_TEXT_SHADOW_COLOR,
                    'default' => array(
                        'global-content-replace' => 0,
                        'global-content-replace-template' => 'default',
                        'source'      => 1,
                        'pre-source'  => "source: ",
                        'no-contents' => 1,
                        'no-edit'     => 1,
                        'no-infobox'  => 0,
                        'tabs'        => 1,
                        'accordion' => 0,
                        'links-open-new' => 0,
                    ),
                    'security' => array(
                        'whitelist' => null,
                    ),
            );
    }

    /**
     * shortcode function.
     * 
     * @access public
     * @return void
     */
    function shortcode( $atts,$content = null ) {
            // url is the unique identifier
        $sX = self::globalRequest('wikiembed-override-url');
            $atts = apply_filters( 'wikiembed_override_atts', $atts );
            $sHTML = '';
            $url = isset($atts['url'])? $atts['url'] : '';
            if($sX && $sX !== $url)return '';
            if(empty($url))$sHTML = 'WikiEmbed - ERROR: No URL specified.';
            if(empty($sHTML) && strpos($url, 'pediapress.com') !== false){
                // handle pediapress css
                 wp_register_style( 'rdp-we-pp-style-common', plugins_url( 'resources/css/pediapress.common.css' , __FILE__ ) );
                 wp_enqueue_style( 'rdp-we-pp-style-common' );
                 $filename = get_stylesheet_directory() .  '/pediapress.custom.css';
                 if (file_exists($filename)) {
                     wp_register_style( 'rdp-we-pp-style-custom',get_stylesheet_directory_uri() . '/pediapress.custom.css',array('rdp-we-pp-style-common' ) );
                     wp_enqueue_style( 'rdp-we-pp-style-custom' );
                 }                    
                
                $sHTML = RDP_WE_PPE::shortcode_handler($url,$atts,$content);
                $sHTML = apply_filters( 'rdp_wpe_after_pediapress_content_grab', $sHTML );
            }
            if(empty ($sHTML)){
                $sHTML = $this->shortcode_handler($atts);
                if(!has_action('wp_footer', array(&$this,'renderTOCMenu'))){
                    add_action('wp_footer', array(&$this,'renderTOCMenu'));
                }                
                $sHTML = apply_filters( 'rdp_wpe_after_wiki_content_grab', $sHTML );
            }

            $sHTML = apply_filters( 'rdp_wpe_shortcode', $sHTML );
            return $sHTML;
    }//shortcode
    
    public function renderTOCMenu(){
        $sKey  = get_post_meta(get_the_ID(), RDP_WE_PPE::$postMetaKey, true);
        $sInlineHTML = '';
        $contentPieces = array();
        if(!empty($sKey))$contentPieces = get_transient( $sKey ); 
        if(empty($contentPieces)){
            $sKey  = get_post_meta(get_the_ID(), '_rdp-ppe-cache-key', true);
            $contentPieces = get_option( $sKey );
        }
        
        if(empty($contentPieces))return;
        if(!wp_script_is('jquery-colorbox'))wp_enqueue_script( 'jquery-colorbox', plugins_url( '/resources/js/jquery.colorbox.min.js',RDP_WE_PLUGIN_BASENAME),array("jquery"), "1.3.20.2", true );   
        wp_enqueue_script(
                'rdp_wpe_toc_popup', 
                plugins_url( '/resources/js/script.toc-popup.js',RDP_WE_PLUGIN_BASENAME),
                array("jquery"), 
                $this->version, 
                true ); 
        if(!wp_style_is('jquery-colorbox'))wp_enqueue_style( 'jquery-colorbox', plugins_url( '/resources/css/colorbox.css',RDP_WE_PLUGIN_BASENAME),false, "1.3.20.2", 'screen');        
        
        $sInlineHTML .= "<div id='rdp_wpe_toc_inline_content_wrapper' style='display:none'><div id='rdp_wpe_toc_inline_content'>";
        $sInlineHTML .= '<h2>Table of Contents:</h2>';
        $sInlineHTML .= $contentPieces['toc'];
        $sInlineHTML .= "</div><!-- #rdp_wpe_inline_content --></div>";
        echo $sInlineHTML;
    }//renderTOCMenu


    private function shortcode_handler($atts){
            global $post;

            $this->content_count++; 

            extract( shortcode_atts( array(
                    'url'         => NULL,
                    'update'      => NULL, // 30 minutes
                    'remove'      => NULL,
                    'get'	      => NULL,
                    'default_get' => NULL,
                    'has_source'  => NULL,
            ), $atts ) );

            if ( ! $url && current_user_can( 'manage_options' ) ) { // checks to see if url is defined 
                    ob_start();
                    ?>
                    <hr />
                    <div class="wiki-embed-warning">
                            <div style="color: darkred;">
                                    You need to specify a url for your Wiki-Embed Shortcode
                            </div>
                            <small>
                                    This message is only displayed to administrators.
                                    <br />
                                    Please <a href=" <?php echo get_edit_post_link( $post->ID ); ?> ">edit this page</a>, and remove the [wiki-embed] shortcode, or specify a url parameter.
                            </small>
                    </div>
                    <hr />
                    <?php
                    return ob_get_clean();
            }

            $url = $this->get_page_url( $url ); // escape the url 

            // other possible attributes
            $has_no_edit 	 = in_array( "no-edit",     $atts );	
            $has_no_contents = in_array( "no-contents", $atts );
            $has_no_infobox  = in_array( "no-infobox",  $atts );
            $has_tabs       = in_array( "tabs",        $atts );
            $has_accordion 	 = in_array( "accordion",   $atts );

            if ( ! isset( $has_source ) ) { // this can be overwritten on per page basis
                    $has_source = $this->options['default']['source'];
            }

            if ( ! is_numeric( $update )) {
                    $update = $this->options['wiki-update'];  // this can be overwritten on per page basis
            }

            /**
             * code here lets you add the get and default_get parameter to your wiki-emebed
             */
            if ( $get ) {
                    $gets = explode( ",", $get );

                    $default_gets = explode( ",", $default_get );
                    $count_get = 0;
                    foreach ( $gets as $get_parameter ) {
                            $gets_replace[] = ( isset( $_GET[trim( $get_parameter )] ) && esc_html( $_GET[trim( $get_parameter )] ) != "" ? esc_html( $_GET[trim( $get_parameter )] ) : $default_gets[$count_get] );
                            $gets_search[]	= "%".trim( $get_parameter )."%";
                            $count_get++;
                    }

                    $url = str_replace( $gets_search, $gets_replace, $url );
            }

            $wiki_page_id = $this->get_page_id( $url, $has_accordion, $has_tabs, $has_no_contents, $has_no_edit, $has_no_infobox, $remove );

            // check to see if we need a refresh or was forced 
            if ($this->options['wiki-update'] != 0 &&  current_user_can( 'publish_pages' ) && isset( $_GET['refresh'] ) && wp_verify_nonce( $_GET['refresh'], $wiki_page_id ) ) {
                // we store stuff 
                foreach ( $this->wikiembeds as $wikiembeds_id => $wikiembeds_item ) {
                        $bits = explode( ",", $wikiembeds_id );

                        if ( esc_attr( $bits[0] ) == esc_attr( $url ) ) {
                                // Rather than deleting the data, set it to expire a long time ago so if the refresh fails it can be ignored.
                                $this->wikiembeds[$wikiembeds_id]['expires_on'] = 1;
                                update_option( 'wikiembeds', $this->wikiembeds );
                        }
                }

                unset( $wikiembeds_id ); 
            }

            // this function retuns the wiki content the way it is suppoed to come 
            $content = $this->get_wiki_content($wiki_page_id, $url, $has_accordion, $has_tabs, $has_no_contents, $has_no_edit, $has_no_infobox,  $update, $has_source, $remove );
            $this->register_scripts($url);
            $this->load_scripts( $has_tabs, $has_accordion );
            $this->print_scripts();
            $this->update_wikiembed_postmeta($wiki_page_id, $post->ID, $url, $content,$update );

            // if the user is admin 
            if ($this->options['wiki-update'] != 0 && current_user_can( 'publish_pages' ) ) {
                $expiresOn = (isset($this->wikiembeds[$wiki_page_id]["expires_on"]))?$this->wikiembeds[$wiki_page_id]["expires_on"]:0;
                if ( time() > $expiresOn ) {
                        $admin = "<div class='wiki-admin' style='position:relative; border:1px solid #CCC; margin-top:20px;padding:10px;'> <span style='background:#EEE; padding:0 5px; position:absolute; top:-1em; left:10px;'>Only visible to admins</span> Wiki content is expired and will be refreshed as soon as the source page can be reached. <a href='?refresh=".wp_create_nonce($wiki_page_id)."'>Retry now</a> | <a href='".admin_url('admin.php')."?page=wiki-embed&url=".urlencode($url)."'>in Wiki Embed List</a>";
                } else {
                        $admin = "<div class='wiki-admin' style='position:relative; border:1px solid #CCC; margin-top:20px;padding:10px;'> <span style='background:#EEE; padding:0 5px; position:absolute; top:-1em; left:10px;'>Only visible to admins</span> Wiki content expires in: ".human_time_diff( date('U', $this->wikiembeds[$wiki_page_id]["expires_on"] ) ). " <a href='".esc_url('?refresh='.wp_create_nonce($wiki_page_id))."'>Refresh Wiki Content</a> | <a href='".admin_url('admin.php')."?page=wiki-embed&url=".urlencode($url)."'>in Wiki Embed List</a>";
                }

                if ( $this->options['wiki-links'] == "new-page" ) {
                        if ( ! isset( $this->wikiembeds[$url]['url'] ) ) {
                                $admin .= " <br /> <a href='' alt='".urlencode( $url )."' title='Set this {$post->post_type} as Target URL' class='wiki-embed-set-target-url' rel='".get_permalink( $post->ID )."'>Set this {$post->post_type} as Target URL</a>";
                        } else {
                                $admin .= " <br /> <span>Target URL set: ".esc_url( $this->wikiembeds[$url]['url'] )."</span>";
                        }
                }

                $admin .= "</div>";
                return $content.$admin; 
            }

            return $content;            

    }//wiki_embed_shortcode_handler

    /**
     * load_page function.
     * 
     * @access public
     * @return void
     */
    function load_page() {
            if ( ! isset( $_GET['wikiembed-url'] ) && ! isset( $_GET['wikiembed-title'] ) ) {
                    return true; // do nothing 
            }

            // call global variables 
            global $wp_query;

            // do we need to redirect the page ? 
            $wiki_page_url = esc_url( $_GET['wikiembed-url'] ); 

            // we could try to load it 
            if ( isset( $this->wikiembeds[$wiki_page_url]['url'] ) ):
                    wp_redirect( esc_url( $this->wikiembeds[$wiki_page_url]['url'] ) );
                    die();
            endif;

            $tabs      = ( $this->options['default']['tabs'] == 1 ? true : false); 
            $accordion = ( $this->options['default']['tabs'] == 2 ? true : false); 
            $wiki_page_id = $this->get_page_id( $wiki_page_url, $accordion, $tabs, $this->options['default']['no-contents'], $this->options['default']['no-edit'], $this->options['default']['no-infobox'] );

            // make sure to load scripts
            $this->load_scripts( $tabs, $accordion );

            /* Generate the shortcode ? */
            $wiki_embed_shortcode = $this->get_page_shortcode( $wiki_page_url, $accordion, $tabs, $this->options['default']['no-contents'], $this->options['default']['no-edit'], $this->options['default']['no-infobox'] );

        // no we have no where to redirect the page to just stay here 
            if ( ! isset( $has_source ) ) {
                    $has_source = $this->options['default']['source'];
            }

            if ( ! isset( $remove ) ) {
                    $remove = ""; // nothing to remove 
            }

            $url   = $this->get_page_url( $_GET['wikiembed-url'] );
            $title = $this->get_page_title( $_GET['wikiembed-title'] );

            $content = $this->get_wiki_content(	
                    $url,
                    $accordion,
                    $tabs,
                    $this->options['default']['no-contents'],
                    $this->options['default']['no-edit'],
                    $this->options['default']['no-infobox'],
                    $this->options['wiki-update'],
                    $this->options['default']['source'],
                    $remove
            );

            if ( current_user_can( 'publish_pages' ) ) {
                    $content.= '<div class="wiki-admin" style="position:relative; border:1px solid #CCC; margin-top:20px;padding:10px;"> <span style="background:#EEE; padding:0 5px; position:absolute; top:-1em; left:10px;">Only visible to admins</span> <a href="'.admin_url('admin.php').'?page=wiki-embed&url='.urlencode($url).'">in Wiki Embed List</a> | <a href="'.admin_url('post-new.php?post_type=page&content=').urlencode($wiki_embed_shortcode).'&post_title='.esc_attr($title).'">Create a New Page</a></div>';
            }

            $admin_email = get_bloginfo( 'admin_email' );
            $user = get_user_by( 'email', $admin_email );

            $wp_query->is_home = false;
            $wp_query->is_page = true;

            $wp_query->post_count = 1;
            $post = (object) null;
            $post->ID = 0; // wiki-embed is set to 0
            $post->post_title = $title;
            $post->post_name = sanitize_title($title);
            $post->guid = get_site_url()."?wikiembed-url=".urlencode($url)."&wikiembed-title=".urlencode( $title );
            $post->post_content = $content;
            $post->post_status = "published";
            $post->comment_status = "closed";
            $post->post_modified = date( 'Y-m-d H:i:s' );
            $post->post_excerpt = "excerpt nothing goes here";
            $post->post_parent = 0;
            $post->post_type = "page";
            $post->post_date = date( 'Y-m-d H:i:s' );
            $post->post_author = $user->ID; // newly created posts are set as if they are created by the admin user

            $wp_query->posts = array( $post );
            $wp_query->queried_object = $post; // this helps remove some errors 
            $flat_url = str_replace( ".", "_", $url);

            // email the telling the admin to do something about the newly visited link. 
            if ( is_email( $this->options['wiki-links-new-page-email'] ) && ! isset( $_COOKIE["wiki_embed_urls_emailed:".$flat_url] ) && ! current_user_can( 'publish_pages' ) ) {
                    $current_url  =	get_site_url()."?wikiembed-url=".urlencode($url)."&wikiembed-title=".urlencode($title);
                    $settings_url = get_site_url()."/wp-admin/admin.php?page=wikiembed_settings_page";
                    $list_url     = get_site_url()."/wp-admin/admin.php?page=wiki-embed";
                    $new_page     = get_site_url()."/wp-admin/post-new.php?post_type=page&post_title=".$title."&content=".$wiki_embed_shortcode;

                    $list_url_item = get_site_url()."/wp-admin/admin.php?page=wiki-embed&url={$url}";

                    $subject = "Wiki Embed Action Required!";

                    $message = "
                    A User stumbled apon a page that is currently not a part of the site.
                    This is the url that they visited - {$current_url}

                    You have a few options:

                    Fix the problem by:
                    Creating a new page - and adding the shortcode 
                    Go to {$new_page} 

                    Here is the shorcode that you might find useful:
                    {$wiki_embed_shortcode}

                    Then go to the Wiki-Embed list and add a Target URL to point to the site
                    {$list_url_item}

                    and place the link that is suppoed to take you to the page that you just created.



                    Or you should:
                    Do Nothing - remove your email from the wiki embed settings page - {$settings_url}
                    ";

                    $sent = wp_mail( $this->options['wiki-links-new-page-email'], $subject, $message ); 

                    // set the cookie do we don't send the email again
                    $expire = time() + 60*60*24*30;
                    $set_the_cookie = setcookie( "wiki_embed_urls_emailed:".$flat_url, "set", $expire );
            }
    }

    /**
     * load_scripts function.
     * 
     * @access public
     * @param mixed $has_tabs
     * @param mixed $has_accordion
     * @return void
     */
    function load_scripts( $has_tabs, $has_accordion ) {
        if ( ! empty( $this->pre_load_scripts ) ) {
                $this->load_scripts = $this->pre_load_scripts;
        }

        if ( is_array( $this->tabs_support ) ) {
                switch( $this->tabs_support[0] ) {
                        case 'twitter-bootstrap';
                                $this->load_scripts[] = 'twitter-tab-shortcode';
                                break; 
                        // add support for something else here 
                        default:
                                $this->load_scripts[] = 'wiki-embed-tabs';
                                break;
                }
        } elseif ( $has_tabs ) {
                $this->load_scripts[] = 'wiki-embed-tabs';
        }

        if ( is_array( $this->accordion_support ) ) {
                switch( $this->accordion_support[0] ) {
                        case 'twitter-bootstrap';
                                // Do Nothing
                                break; 
                        // add support for something else here 
                        default:
                                $this->load_scripts[] = 'wiki-embed-accordion';	
                        break;
                }
        } elseif ( $has_accordion ) {
                $this->load_scripts[] = 'wiki-embed-accordion';	
        }

    }

    /**
     * get_page_id function.
     * 
     * @access public
     * @param mixed $url
     * @param mixed $has_accordion
     * @param mixed $has_tabs
     * @param mixed $has_no_contents
     * @param mixed $has_no_edit
     * @param mixed $has_no_infobox
     * @param bool $remove (default: false)
     * @return string $page_id;
     */
    function get_page_id( $url, $has_accordion, $has_tabs, $has_no_contents, $has_no_edit, $has_no_infobox, $remove = false ) {
            $wiki_page_id = esc_url( $url ).",";

            if ( $has_tabs ) {
                    $wiki_page_id .= "tabs,";
            }

            if ( $has_accordion ) {
                    $wiki_page_id .= "accordion,";
            }

            if ( $has_no_contents ) {
                    $wiki_page_id .= "no-contents,";
            }

            if ( $has_no_edit ) {
                    $wiki_page_id .= "no-edit,";
            }

            if ( $has_no_infobox ) {
                    $wiki_page_id .= "no-infobox,";
            }

            if ( $remove ) {
                    $wiki_page_id .= $remove.",";
            }

            $wiki_page_id =	substr( $wiki_page_id, 0, -1 );

            return $wiki_page_id;
    }

    /**
     * get_page_shortcode function.
     * 
     * @access public
     * @param mixed $url
     * @param mixed $has_accordion
     * @param mixed $has_tabs
     * @param mixed $has_no_contents
     * @param mixed $has_no_edit
     * @param mixed $has_no_infobox
     * @return string $wiki_embed_shortcode
     */
    function get_page_shortcode( $url, $has_accordion, $has_tabs, $has_no_contents, $has_no_edit, $has_no_infobox ) {
            $atts = "";
            $atts .= " url=" . $url;
            if ( $has_tabs )        $atts .= " tabs";
            if ( $has_accordion )   $atts .= " accordion";
            if ( $has_no_contents ) $atts .= " no-contents";
            if ( $has_no_edit )     $atts .= " no-edit";
            if ( $has_no_infobox )  $atts .= " no-infobox";

            return "[wiki-embed".$atts."]";
    }

    /**
     * get_page_url function.
     * 
     * @access public
     * @param mixed $get_url
     * @return void
     */
    function get_page_url( $get_url ) {
            // Remove unwanted parts
            $url = $this->remove_action_render( $get_url );
            $url = str_replace( "&#038;","&", $url );
            $url = str_replace( "&amp;","&", $url );	
            $url_array = explode( "#", $url );

            return $url_array[0];
    }

    /* TODO: his function is identical to the one above it. Remove one of them. */
    function esc_url( $url ) {
            // remove unwanted parts
            $url = $this->remove_action_render( $url );
            $url = str_replace( "&#038;", "&", $url );
            $url = str_replace( "&amp;", "&", $url );	
            $url_array = explode( "#", $url );

            return $url_array[0];
    }

    /**
     * remove_action_render function.
     * removed any add action from the end of the url 
     * @access public
     * @param mixed $url
     * @return void
     */
    function remove_action_render( $url ) {
            if ( substr( $url, -14 ) == "?action=render" ) {
                    return substr( $url, 0, -14 );
            } else {
                    return $url;	
            }
    }

    /**
     * get_page_title function.
     * 
     * @access public
     * @param mixed $title
     * @return void
     */
    function get_page_title( $title ) {
            $title =  esc_html( $title );

            // explode url - so that the title doesn't hash marks contain into 
            $title_array = explode( '#', $title );

            $title = ( isset( $title_array[1] )  ? $title_array[0] : $title );
            return $title ;
    }

    /**
     * get_wiki_content function.
     * 
     * @access public
     * @param mixed $url
     * @param mixed $has_tabs
     * @param mixed $has_no_contents
     * @param mixed $has_no_edit
     * @param mixed $update
     * @param bool $has_source. (default: false)
     * @param mixed $remove. (default: null)
     * @return void
     */
    function get_wiki_content($wiki_page_id, $url, $has_accordion, $has_tabs, $has_no_contents, $has_no_edit, $has_no_infobox, $update, $has_source, $remove = null ) {

        $wiki_page_body  = $this->remote_request_wikipage($wiki_page_id, $url, $update );

            $isExpired = true;
            if(is_array($this->wikiembeds)){
                if(array_key_exists($wiki_page_id,$this->wikiembeds)){
                    if(is_array($this->wikiembeds[$wiki_page_id])){
                        try{
                            $isExpired = ($this->wikiembeds[$wiki_page_id]['expires_on'] < time());
                        }catch (Exception $e) {
                            //ignore error
                        }
                    }                 
                }                
            }
            

            if ( $wiki_page_body && $isExpired && ! ( isset( $_GET['refresh'] ) && wp_verify_nonce( $_GET['refresh'], $wiki_page_id ) ) ) {
                    //If the cache exists but is expired (and an immediate refresh has not been forced:
                    // Refresh it at the end!
                    register_shutdown_function( array( $this, 'refresh_after_load'), $url, $has_accordion, $has_tabs, $has_no_contents, $has_no_edit, $has_no_infobox, $update, $remove );
            } elseif ( ! $wiki_page_body || ( current_user_can( 'publish_pages' ) && isset( $_GET['refresh'] ) && wp_verify_nonce( $_GET['refresh'], $wiki_page_id ) ) ) {	


                    if ( $wiki_page_body ) { // Successfully grabbed remote contnet
                  
                            //render page content
                        require_once( "resources/simple_html_dom.php" );
                        $html = new rdp_simple_html_dom();
                        $html->load('<html><body>'.$wiki_page_body.'</body></html>');
                        $body = $html->find('body',0);
                        $oURLPieces = parse_url($url);
                        if(empty($oURLPieces['scheme']))$oURLPieces['scheme'] = 'http';
                        $sSourceDomain = $oURLPieces['scheme'].'://'.$oURLPieces['host'];
                        
                        if($body){
                            foreach($body->find('script') as $script){
                                $script->outertext = '';
                            }
                            foreach($body->find('style') as $script){
                                $script->outertext = '';
                            } 
                            foreach($body->find('img') as $img){
                                 $oImgPieces = parse_url($img->src);
                                 if(!isset($oImgPieces['host'])):
                                     $sPath = $oImgPieces['path'];
                                     if(substr($sPath, 0, 2) == '..')$sPath = substr($sPath, 3);
                                     if(substr($sPath, 0, 1) != '/')$sPath = '/'.$sPath;
                                     $img->src = $sSourceDomain . $sPath;
                                 endif;
                                 if(substr(strtolower($img->src), 0, 4) != 'http'){
                                      $img->src = $oURLPieces['scheme'] . ':' . $img->src;
                                 }
                                 $class = 'data-file-width';
                                 $img->$class = null;
                                 $class = 'data-file-height';
                                 $img->$class = null;
                                 $img->srcset = null;
                                 if($img->width >= 400 || $img->height >= 400){
                                    $img->width = null;
                                    $img->height = null;
                                    $img->style = 'width: 100%;max-width: 400px;height: auto;';
                                 }
                            }
                            
                            if($this->options['wiki-links'] == 'default' && !empty($this->options['default']['links-open-new'])){
                                foreach($body->find('a') as $link){
                                    $pos = strpos($link->href, '#');
                                    if ($pos === false)$link->target = '_new';
                                }
                                
                            }
                            $wiki_page_body = $html->find('body',0)->innertext;                            
                        }
                        $html->clear();
                       
                        $wiki_page_body = $this->render( $wiki_page_id, $wiki_page_body, $has_no_edit, $has_no_contents , $has_no_infobox, $has_accordion, $has_tabs, $remove );
                    
                        
                        
                        
                    } else { //Failed, (and there's no cache available) so show an error
                            $update = 0;	//Set the expiry offset to 0 (now) to try again next time the page is loaded
                            return '<span class="alert">
                                            We were not able to Retrieve the content of this page, at this time.<br />
                                            You can: <br />
                                            1. Try refreshing the page. Press Ctrl + R (windows) or ⌘ Cmd + R (mac)<br />
                                    2. Go to the <a href="'.esc_url($url).'" >source</a><br />
                                    </span>';
                    }
            }

            // display the source 
            $wiki_embed_end = '';
            if ( $has_source ) {
                    $source_text = ( isset( $this->options['default']['pre-source'] ) ? $this->options['default']['pre-source'] : "source:" ); 
                    $wiki_embed_end .= '<span class="wiki-embed-source">'.$source_text.' <a href="'.esc_url( urldecode($url)) .'">'.urldecode($url).'</a></span>';
            }

            // add special wiki embed classed depending on what should be happening
            $wiki_embed_class = '';

            switch ( $this->options['wiki-links'] ) {
                    case "overlay":
                            $wiki_embed_class .= " wiki-embed-overlay ";
                            break;
                    case "overwrite":
                            $wiki_embed_class .= " wiki-embed-overwrite ";
                            break;                            
                    case "new-page":
                    default:
                            $wiki_embed_class .= " wiki-embed-new-page ";
                            break;

            }

            $wiki_target_url = ' wiki-target-url-not-set';

            if ( isset( $this->wikiembeds[$wiki_page_id]['url'] ) && $this->wikiembeds[$wiki_page_id]['url'] ) {
                    $wiki_target_url = " wiki-target-url-set";
            }

            $wiki_embed_class .= $wiki_target_url; 
            
            if(strpos($wiki_page_body, $wiki_embed_class))return $wiki_page_body;
            
            return "<div class='wiki-embed ".$wiki_embed_class."' rel='{$url}'>".$wiki_page_body."</div>".$wiki_embed_end;
    }

    /**
     * remote_request_wikipage function.
     * This function get the content from the url and stores in an transient. 
     * @access public
     * @param mixed $url
     * @param mixed $update
     * @return void
     */
    function remote_request_wikipage($wiki_page_id, $url, $update ) {

            if ( ! $this->pass_url_check( $url ) ) {
                    return "This url does not meet the site security guidelines.";
            }

            $wiki_page_body = (empty($update) || !is_numeric($update))? false : $this->get_cache( $wiki_page_id );
            // grab the content from the cache
            
            $isExpired = true;
            if(is_array($this->wikiembeds)){
                if(array_key_exists($wiki_page_id,$this->wikiembeds)){
                    if(is_array($this->wikiembeds[$wiki_page_id])){
                        try{
                            $isExpired = ($this->wikiembeds[$wiki_page_id]['expires_on'] < time());
                        }catch (Exception $e) {
                            //ignore error
                        }
                    }                
                }                
            }

            if ( false === $wiki_page_body || $isExpired ) {
                    // else return the 
                    $wiki_page = wp_remote_request( $this->action_url( $url ) );

                    if ( ! is_wp_error( $wiki_page ) ) {
                            $wiki_page_body = $this->rudermentory_check( $wiki_page );

                            if ( ! $wiki_page_body ) {
                                    return false;
                            }
                    } else {
                    // an error occured try getting the content again
                    $args = array(
                        'timeout'     => 20,
                    );                        
                    $wiki_page = wp_remote_request( $this->action_url($url),$args );
                    if ( ! is_wp_error( $wiki_page ) ) {
                            $wiki_page_body = $this->rudermentory_check( $wiki_page );

                            if ( ! $wiki_page_body ) {
                                return false;
                            }
                        } else {
                            return false;// error occured while fetching content 
                        }
                    }

                // make sure that we are UTF-8
                if ( function_exists('mb_convert_encoding') ) {
                    $wiki_page_body = mb_convert_encoding( $wiki_page_body, 'HTML-ENTITIES', "UTF-8" ); 
                }


                $wiki_page_body = $this->make_safe( $wiki_page_body );

            }

        return $wiki_page_body;
    }

    /**
     * rudermentory_check function.
     * 
     * @access public
     * @param mixed $wiki_page
     * @return void
     */
    function rudermentory_check( $wiki_page ) {
            //rudimentary error check - if the wiki content contains one of the error strings below
            //or the http status code is an error then it should not be saved.
            $error_strings = array( "Can't contact the database server" );
            $errors = false;
            $RV = false;
            foreach ( $error_strings as $error ) {
                    if ( strpos( $wiki_page['body'], $error ) !== false ) {
                            $errors = true;
                            break;
                    }
            }

            if ( ! $errors && $wiki_page['response']['code'] == 200 ): 
                    $RV = $wiki_page['body'];
            else:
                    $RV = false;
            endif;
            
            return $RV;
    }

    /**
     * pass_url_check function.
     * 
     * @access public
     * @param mixed $url
     * @return void
     */
    function pass_url_check( $url ) {
            $white_list = trim( $this->options['security']['whitelist'] );
            $white_list_pass = false;
            if ( ! empty( $white_list ) ) {

                    $white_list_urls = preg_split( '/\r\n|\r|\n/', $this->options['security']['whitelist'] ); 
                    // http://blog.motane.lu/2009/02/16/exploding-new-lines-in-php/

                    foreach ( $white_list_urls as $check_url ) {
                        if(strpos($url, $check_url) !== false) {
                            $white_list_pass = true;
                            break;
                        }
                    }

            }

            return $white_list_pass;
    }

    /**
     * action_url function.
     * 
     * @access public
     * @param mixed $url
     * @return void
     */
    function action_url( $url ) {
            if ( ! function_exists( 'http_build_url' ) ) {
                    require( 'http_build_url.php' );
            }

            return http_build_url( $url, array( "query" => "action=render" ), HTTP_URL_JOIN_QUERY );
    }

    /**
     * wiki_embed_make_safe function.
     * strip out any unwanted tags - the same way wordpress does
     * @access public
     * @param mixed $body
     * @return void
     */
    function make_safe( $body ) {
        
            global $allowedposttags;
            $new_tags = $allowedposttags;

//            foreach ( $allowedposttags as $tag => $array ) {
//               $new_tags[$tag]['id'] = array();
//               $new_tags[$tag]['class'] = array();
//               $new_tags[$tag]['style'] = array();
//            }
 
            // param
            $new_tags['param']['name'] = array();
            $new_tags['param']['value'] = array();

            // object
            $new_tags['object']['type'] = array();
            $new_tags['object']['allowscriptaccess'] = array();
            $new_tags['object']['allownetworking'] = array();
            $new_tags['object']['allowfullscreen'] = array();
            $new_tags['object']['width'] = array();
            $new_tags['object']['height'] = array();
            $new_tags['object']['data'] = array();

            // embed
            $new_tags['embed']['width'] = array();
            $new_tags['embed']['height'] = array();
            $new_tags['embed']['type'] = array();
            $new_tags['embed']['wmode'] = array();
            $new_tags['embed']['src'] = array();
            $new_tags['embed']['type'] = array();

            // <iframe width="480" height="360" src="http://www.youtube.com/embed/CoAv6yIVkSQ" frameborder="0" allowfullscreen></iframe>
            // is there a better way of allowing trusted sources like youtube? 
            $new_tags['iframe']['allowfullscreen'] = array();
            $new_tags['iframe']['width'] = array();
            $new_tags['iframe']['height'] = array();
            $new_tags['iframe']['src'] = array();
            $new_tags['iframe']['frameborder'] = array();
   
            // lets sanitize this 
	$body = wp_kses_no_null($body);
	$body = wp_kses_js_entities($body);
	$body = wp_kses_normalize_entities($body); 
        $allowed_protocols = wp_allowed_protocols();
        $body = wp_kses_hook($body, $new_tags, $allowed_protocols);
//echo $body;
//exit;
//        $body = wp_kses( $body, $new_tags );
     
            return $body;
    }

    /**
     * render function.
     * 
     * @access public
     * @param mixed $wiki_page_body
     * @param mixed $has_no_edit
     * @param mixed $has_no_contents
     * @param mixed $has_no_infobox
     * @param mixed $has_accordion
     * @param mixed $has_tabs
     * @return void
     */
    function render( $wiki_page_id, $wiki_page_body, $has_no_edit, $has_no_contents, $has_no_infobox, $has_accordion, $has_tabs, $remove ) {
        $has_no_unreferenced_box = $this->options['unreferenced-boxes-style'];
            if ( $has_no_unreferenced_box || $has_no_edit || $has_no_contents || $has_no_infobox || $has_accordion || $has_tabs || $remove ) {
                    require_once( "resources/css_selector.php" );	//for using CSS selectors to query the DOM (instead of xpath)

                    $wiki_page_id = md5( $wiki_page_id );	
                    //Prevent the parser from throwing PHP warnings if it receives malformed HTML
                    libxml_use_internal_errors(true);

                    //For some reason any other method of specifying the encoding doesn't seem to work and special characters get broken
                    $oDOM = new DOMDocument("1.0", "UTF-8");
                    $html = $oDOM->loadHTML( '<?xml version="1.0" encoding="UTF-8"?>' . $wiki_page_body );	

                    //Remove specified elements
                    $remove_elements = explode( ",", $remove );

                    // remove edit links 
                    if ( $has_no_edit ):
                            $remove_elements[] = '.editsection';
                    endif; // end of removing links

                    // remove table of contents 
                    if ( $has_no_contents ):
                            $remove_elements[] = '#toc';
                    endif;

                    // remove infobox 
                    if ( $has_no_infobox ):
                            $remove_elements[] = '.infobox';
                    endif;
                    
                    // remove Unreferenced 
                    if ( !empty($has_no_unreferenced_box) ):
                        $xpath = new DomXpath($oDOM);
                        $allClassUnreferenced = $xpath->query("//table");
                        foreach($allClassUnreferenced as $e){
                            $attributes = $e->attributes;
                            if(!is_null($attributes)) 
                            { 
                                foreach ($attributes as $index=>$attr) 
                                { 
                                    if (strpos($attr->value,'ambox-Unreferenced') !== false) {
                                        $e->parentNode->removeChild($e);
                                        break;
                                    }
                                 } 
                            }
                        }
                    endif;                     

                    $finder = new DomCSS($oDOM);
                    
                   

                    // bonus you can remove any element by passing in a css selector and seperating them by commas
                    if ( ! empty( $remove_elements ) ) {
                            foreach ( $remove_elements as $element ) {
                                    if ( $element ) {
                                            foreach ( $finder->query( $element ) as $e ) {
                                                    $e->parentNode->removeChild($e);
                                            }

                                            $removed_elements[] = $element;
                                    }
                            }
                    } // end of removing of the elements 

                    //Strip out undesired tags that DOMDocument automaticaly adds
                    $wiki_page_body = preg_replace( array( '/^<!DOCTYPE.+?>/u','/<\?.+?\?>/' ), array( '', '' ), str_replace( array( '<html>', '</html>', '<body>', '</body>' ), array( '', '', '', '' ), $oDOM->saveHTML() ) );

                    //Seperate article content into an array of headers and an array of content (for tabs/accordions/styling)
                    $start_offset = 0;
                    $headlines = array();
                    $content = array();
                    $first_header_position = strpos( $wiki_page_body, '<h2>' );

                    //Check if the first header is from a table of contents. if so, need to move up and find the next header.
                    if ( ! $this->extract_headline_text( substr( $wiki_page_body, $first_header_position, strpos( $wiki_page_body, '</h2>' ) + 5 - $first_header_position ) ) ) {
                            $first_header_position = strpos( $wiki_page_body, '<h2>', $first_header_position + 1 );
                    }

                    $article_intro = substr( $wiki_page_body, 0, $first_header_position ); //contains everything up to (but excluding) the first subsection of the article
                    $article_content = substr( $wiki_page_body, $first_header_position ); //contains the rest of the article 

                    //Go through the wiki body, find all the h2s and content between h2s and put them into arrays.
                    while ( true ) {
                            $start_header = strpos( $article_content, '<h2>', $start_offset );

                            if ( $start_header === false ) { //The article doesn't have any headers
                                    $article_intro = $article_content;
                                    break;
                            }

                            //find out where the end of this header and the end of the corresponding section are
                            $end_header  = strpos( $article_content, '</h2>', $start_offset );
                            $end_section = strpos( $article_content, '<h2>', $end_header );
                            $headlines[] = substr( $article_content, $start_header + 4, $end_header - $start_header - 4 );

                            if ( $end_section !== false ) { //success, we've hit another header
                                    $content[] = substr( $article_content, $end_header + 5, $end_section-$end_header - 5 );
                                    $start_offset = $end_section;
                            } else { //we've hit the end of the article without finding anything else
                                    $content[] = substr( $article_content, $end_header + 5 );
                                    break;
                            }
                    }
                    //Now $content[] and $headers[] each are populated for the purposes of tabs/accordions etc

                    //Build the main page content, with tabs & accordion if necessary
                    $article_sections = array();
                    $tab_list = "";
                    $index = 0;
                    $count = count( $headlines ) - 1 ;

                    foreach ( $headlines as $headline ) {
                            //add headline to the tabs list if we're using tabs
                            if ( $has_tabs ) {
                                    $tab_list .= '<li><a href="#fragment-'.$wiki_page_id.'-'.$index.'" >'.$this->extract_headline_text( $headline ).'</a></li>';				
                            }

                            $headline_class = "wikiembed-fragment wikiembed-fragment-counter-".$index;

                            if ( $count == $index ) {
                                    $headline_class .= " wikiembed-fragment-last";
                            }

                            if ( $has_accordion ) { //jquery UI's accordions use <h2> and <div> pairs to organize accordion content
                                    $headline_class .=" wikiembed-fragment-accordion ";
                                    $headline_class = apply_filters( 'wiki-embed-article-content-class', $headline_class, $index, 'accordion' );

                                    $article_content_raw = '
                                            <h2><!-- start of headline wiki-embed --><a href="#">' . $this->extract_headline_text( $headline )  . '</a><!--end of headline wiki-embed --></h2>
                                            <!-- start of content headline --><div class="' . $headline_class . '">
                                                    <!-- start of content wiki-embed -->' . $content[$index] . '<!-- end of content wiki-embed -->
                                            </div>
                                    ';

                                    $article_sections[] = apply_filters( 'wiki-embed-article-content', $article_content_raw, $index, 'accordion', $wiki_page_id );
                            } else { //And this alternative structure for tabs. (or if there's neither tabs nor accordion)
                                    $headline_class = apply_filters('wiki-embed-article-content-class', $headline_class, $index, 'tabs' );
                                    $article_content_raw = '
                                            <div id="fragment-'.$wiki_page_id.'-'.$index.'" class="'.$headline_class.'">
                                                    <h2>'.$headline.'</h2>
                                                    <!-- start of content wiki-embed -->' . $content[$index] . '<!-- end of content wiki-embed -->
                                            </div>
                                    ';
                                    if ( $has_tabs ) {
                                            $article_sections[] = apply_filters( 'wiki-embed-article-content', $article_content_raw, $index, 'tabs', $wiki_page_id );
                                    } else {
                                            $article_sections[] = apply_filters( 'wiki-embed-article-content', $article_content_raw, $index, 'none', $wiki_page_id );
                                    }
                            }

                            $index++;
                    }

                    if ( $has_tabs ) { // Accordians
                            $tab_list = apply_filters( 'wiki-embed-tab_list', $tab_list );
                            $start = '<div class="wiki-embed-tabs wiki-embed-fragment-count-'.$count.'">'; // shell div

                            $tabs_shell_class = apply_filters( 'wiki-embed-tabs-shell-class', 'wiki-embed-tabs-nav');

                            if ( ! empty( $tab_list ) ) {
                                    $start .= '<ul class="'.$tabs_shell_class.'">'.$tab_list.'</ul>';
                            }

                            $articles_content = apply_filters( 'wiki-embed-articles', implode( " ", $article_sections ), 'tabs' );
                    } elseif ( $has_accordion ) { // Tabs
                            $start = '<div id="accordion-wiki-'.$this->content_count.'" class="wiki-embed-shell wiki-embed-accordion wiki-embed-fragment-count-'.$count.'">'; // shell div
                            $articles_content = apply_filters( 'wiki-embed-articles', implode( " ", $article_sections ), 'accordion' );
                    } else { // None
                            $start = '<div class="wiki-embed-shell wiki-embed-fragment-count-'.$count.'">'; // shell div
                            $articles_content = apply_filters( 'wiki-embed-articles', implode( " ", $article_sections ), 'none' );
                    }

                    $wiki_page_body = $article_intro . $start . $articles_content . '</div>';
            } // end of content modifications 

            //clear the error buffer since we're not interested in handling minor HTML errors here
            libxml_clear_errors();

            return $wiki_page_body;
    }

    /**
     * extract_headline_text function.
     * given an <h2> tag, returns the content of the inner mw-headline span, or return false on failure.
     * @access public
     * @param mixed $element
     * @return string
     */
    function extract_headline_text($element){
            $match = preg_match( '/id=".+?">(.+?)<\/span>/', $element, $headline );

            if ( $match ) {
                    return $headline[1];
            } else {
                    return false;
            }
    }

    /* FILTERS */
    /**
     * page_link function.
     * filter for the page link … 
     * @access public
     * @param mixed $url
     * @return void
     */
    function page_link( $url ) {
            global $post;
            if(empty($post))return $url;
            if ( $post->ID === 0 ) {
                    return $post->guid;
            }

            return $url;
    }
    /* END OF FILTERS */

    /* AJAX STUFF HAPPENED HERE */
    /**
     * wikiembed_overlay_ajax function.
     * 
     * This function is what gets dislayed in the overlay
     * @access public
     * @return void
     */
    function overlay_ajax() {
        $sURL = (isset($_GET['url'] ))? $_GET['url'] : '';
        $url = $this->action_url( $sURL );
        $urlIsFile = strpos($sURL, '/File:');
        //$source_url = esc_url( urldecode( $sURL ) );
        
        $sRemove = (isset($_GET['remove'] ))? $_GET['remove'] : '';
        $remove = esc_attr( urldecode( $sRemove ) );
        
        $sTitle = (isset($_GET['title'] ))? $_GET['title'] : '';
        $title = '';
        if(FALSE !== $urlIsFile){
            $sTitle = stripslashes ($sTitle);
            $sTitle = self::entitiesPlain($sTitle);
        }else{
            $title = esc_html( urldecode( $sTitle ) );            
        }


        $plain_html = (isset($_GET['plain_html'] ))? $_GET['plain_html'] : '';
        //$source_url = $this->remove_action_render( $source_url );

            // constuct 
        $sWikiEmbedURL = (isset($_GET['wikiembed-url'] ))? $_GET['wikiembed-url'] : '';
        $wiki_page_id = esc_url( $sWikiEmbedURL ).",";
        
        if(empty($this->options['default']['accordion']))$this->options['default'] += array('accordion' => '2');
        if(empty($this->options['default']['tabs']))$this->options['default'] += array('tabs' => '1');
        if(empty($this->options['default']['no-contents']))$this->options['default'] += array('no-contents' => '1');
        if(empty($this->options['default']['no-edit']))$this->options['default'] += array('no-edit' => '1');
        if(empty($this->options['default']['no-infobox']))$this->options['default'] += array('no-infobox' => '0');
        if(empty($this->options['default']['source']))$this->options['default'] += array('source' => '1');
        if ( $this->options['default']['tabs'] == 2   ) $wiki_page_id .= "accordion,";
        if ( $this->options['default']['tabs'] == 1   ) $wiki_page_id .= "tabs,";
        if ( $this->options['default']['no-contents'] ) $wiki_page_id .= "no-contents,";
        if ( $this->options['default']['no-infobox']  ) $wiki_page_id .= "no-infobox,";
        if ( $this->options['default']['no-edit']     ) $wiki_page_id .= "no-edit,";

        $wiki_page_id = substr( $wiki_page_id, 0, -1 );

        $content = $this->get_wiki_content(
                $url,
                $this->options['default']['accordion']=='2',
                $this->options['default']['tabs']=='1',
                $this->options['default']['no-contents'],
                $this->options['default']['no-edit'],
                $this->options['default']['no-infobox'],
                $this->options['wiki-links'],
                $this->options['default']['source'],
                $remove
        );

        if ( $plain_html ):
                echo $content;
        else:
                ?>
                <!doctype html>

                <!--[if lt IE 7 ]> <html class="ie6" <?php language_attributes(); ?>> <![endif]-->
                <!--[if IE 7 ]>    <html class="ie7" <?php language_attributes(); ?>> <![endif]-->
                <!--[if IE 8 ]>    <html class="ie8" <?php language_attributes(); ?>> <![endif]-->
                <!--[if (gte IE 9)|!(IE)]><!--> <html <?php language_attributes(); ?>> <!--<![endif]-->
                        <head>
                                <meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
                                <title><?php echo urldecode(esc_attr($_GET['title'])); ?></title>

                                <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.1/jquery.min.js"></script>
                                <link media="screen" href="<?php echo bloginfo('stylesheet_url')?>" type="text/css" rel="stylesheet" >
                                <link media="screen" href="<?php echo plugins_url('/resources/css/wiki-embed.css', __FILE__) ; ?>" type="text/css" rel="stylesheet" >
                                <link media="screen" href="<?php echo plugins_url('/resources/css/wiki-overlay.css', __FILE__) ; ?>" type="text/css" rel="stylesheet" >
                                <script src="<?php echo plugins_url('/resources/js/wiki-embed-overlay.js', __FILE__) ; ?>" ></script>
                        </head>
                        <body>
                                <div id="wiki-embed-iframe">
                                        <div class="wiki-embed-content">
                                                <h1 class="wiki-embed-title" ><?php  echo $title;  ?>
                                                </h1>
                                                <?php
                                                    if(FALSE !== $urlIsFile){
                                                        echo $sTitle;
                                                    }else{
                                                        echo $content;            
                                                    }
                                                ?>
                                        </div>
                                </div>
                        </body>
                </html>
                <?php
        endif;
        die(); // don't need any more help 
    }


    function search_metadata_join( $join ) {
        global $wpdb, $wp_query;
        if ( ! is_admin() && $wp_query->is_search ) {
            $classes = get_body_class();
            if ( !in_array( 'woocommerce', $classes ) ) {
                $join .= " LEFT JOIN ".$wpdb->postmeta." ON ".$wpdb->posts.".ID = ".$wpdb->postmeta.".post_id AND ( ".$wpdb->postmeta.".meta_key = 'wikiembed_content' ) ";
            }
        }

        return $join;
    }

    function search_metadata_where( $where ) {
        global $wpdb, $wp, $wp_query;
        if ( ! is_admin() && $wp_query->is_search ) {
            $classes = get_body_class();
            if ( !in_array( 'woocommerce', $classes ) ) {
                $where .=  " OR ( ".$wpdb->postmeta.".meta_value LIKE '%".$wp->query_vars['s']."%' ) ";;
            }  
        }

        return $where;
    }


    /**
     * Makes the plugin searchable by Ajaxy Live Search.
     * http://wordpress.org/plugins/ajaxy-search-form/
     *
     * This is a specific fix for integration with Ajaxy, and only for Ajaxy.
     * It hooks into a custom filter created by the Ajaxy plugin,
     * and makes assumptions about how the query is formatted.
     * If Ajaxy changes how they query, this function will very easily break.
     */
    function search_metadata_ajaxy( $query ) {
            global $wpdb;

            $result = true;
            if ( preg_match( '/%(.*?)%/', $query, $result ) ) {
                    $search = $result[1];

                    $query = explode( "where", $query, 2 );
                    $where = $query[1];
                    $query = $query[0];

                    $where = explode( "limit", $where, 2 );
                    $limit = $where[1];
                    $where = $where[0];

                    $where = explode( ")", $where, 2 );
                    $where = $where[0] . " OR ".$wpdb->postmeta.".meta_value LIKE '%".$search."%' ) " . $where[1];

                    $join = " LEFT JOIN ".$wpdb->postmeta." ON ".$wpdb->posts.".ID = ".$wpdb->postmeta.".post_id AND ( ".$wpdb->postmeta.".meta_key = 'wikiembed_content' ) ";
                    $query = $query . $join . "WHERE" . $where . "LIMIT" . $limit;
            }

            return $query;
    }


    /* CACHING */
    /**
     * get_cache function.
     * 
     * @access public
     * @param mixed $wiki_page_id
     * @return void
     */
    function get_cache( $wiki_page_id ) {
        return get_option( $this->get_hash( $wiki_page_id ) );
    }

    /**
     * update_cache function.
     * 
     * @access public
     * @param mixed $wiki_page_id
     * @param mixed $body
     * @param mixed $update
     * @return void
     */
    function update_cache( $wiki_page_id, $body, $update ) {
        if(!is_numeric($update) || empty($update))return;
            /**
             * check to see if we have a site already 
             **/
            $hash = $this->get_hash( $wiki_page_id);

            if ( false === get_option( $hash ) ) {
                    $worked = add_option( $hash, $body, '', 'no' ); // this make sure that we don't have autoload turned on
            } else {
                    $worked = update_option( $hash, $body );
            }

            // save it under the wikiembed
            // keep a track of what how long it is going to be in there
            if ( is_array( $this->wikiembeds ) ) {
                    $this->wikiembeds[$wiki_page_id]['expires_on'] = time() + ($update * 60);
                    update_option( 'wikiembeds', $this->wikiembeds );
            } else {
                    $this->wikiembeds[$wiki_page_id]['expires_on'] = time() + ($update * 60);
                    add_option( 'wikiembeds', $this->wikiembeds, '', 'no' );
            }

            return $worked;
    }

    /**
     * delete_cache function.
     * 
     * @access public
     * @param mixed $wiki_page_id
     * @return void
     */
    function delete_cache( $wiki_page_id ) {
            $hash = $this->get_hash( $wiki_page_id );

            delete_option( $hash );

            if ( is_array( $this->wikiembeds ) ) {
                    unset( $this->wikiembeds[$wiki_page_id] );
                    update_option( 'wikiembeds', $this->wikiembeds );
            }
    }

    /**
     * clear_cache function.
     * 
     * @access public
     * @param mixed $wiki_page_id
     * @return void
     */
    function clear_cache( $wiki_page_id ) {
            $hash = $this->get_hash( $wiki_page_id );

            delete_option( $hash );

            if ( is_array( $this->wikiembeds ) ) {
                    $this->wikiembeds[$wiki_page_id]['expires_on'] = 1;
                    update_option( 'wikiembeds', $this->wikiembeds );
            }
    }

    /**
     * get_hash function.
     * 
     * @access public
     * @param mixed $wiki_page_id
     * @return void
     */
    function get_hash( $wiki_page_id ) {
            return "wikiemebed_".md5( $wiki_page_id );
    }

    /**
     * refresh_after_load function.
     * Refresh the content after the page has loaded
     * @access public
     * @param mixed $url
     * @param mixed $has_accordion
     * @param mixed $has_tabs
     * @param mixed $has_no_contents
     * @param mixed $has_no_edit
     * @param mixed $has_no_infobox
     * @param mixed $update
     * @param mixed $has_source
     * @param mixed $remove (default: null)
     * @return void
     */
    function refresh_after_load($url, $has_accordion, $has_tabs, $has_no_contents, $has_no_edit, $has_no_infobox, $update, $remove = null ) {
            //Get page from remote site
            global $wikiembeds,$wikiembed_options;
            $wiki_page_id = $this->get_page_id( $url, $has_accordion, $has_tabs, $has_no_contents, $has_no_edit, $has_no_infobox,  $remove );
            $wiki_page_body = $this->remote_request_wikipage($wiki_page_id, $url, $update );

            if ( $wiki_page_body ) { // Successfully grabbed remote content
                    //render page content
                    $wiki_page_body = $this->render( $wiki_page_id, $wiki_page_body, $has_no_edit, $has_no_contents , $has_no_infobox, $has_accordion, $has_tabs, $remove );
                    $this->update_cache( $wiki_page_id,  $wiki_page_body, $update );
            }
    }
    /* for backwards compatibility */

    /**
     * wikiembed_save_post function.
     * 
     * @access public
     * @param mixed $post_id
     * @return void
     */
    function save_post( $post_id ) {	
            if ( wp_is_post_revision( $post_id ) ) {
                    $post = get_post( wp_is_post_revision( $post_id ) );

                    // start fresh each time you save the post or page
                    delete_post_meta( $post->ID, "wiki_embed" );
            }

            return $post_id;
    }

    function update_wikiembed_postmeta($wiki_page_id, $post_id, $url, $content,$update ) {
        if($this->options['wiki-update'] == 0 )return;

        $this->update_cache($wiki_page_id, $content, $update);

        if ( $this->wikiembeds[$url]['expires_on'] != get_post_meta( $post_id, "wikiembed_expiration" ) ) {
            $content = strip_tags( $content );

            // If this is not the first piece of content to be embeded, then include the content that we got from previous shortcodes.
            if ( $this->content_count > 1 ) {
                    $old_content = get_post_meta( $post_id, "wikiembed_content" );
                    $old_content = $old_content[0];
                    $content = $old_content . $content;
            }

            update_post_meta( $post_id, "wikiembed_content", $content );
            update_post_meta( $post_id, "wikiembed_expiration", $this->wikiembeds[$url] );
        }
    }
    /* END OF CACHING */
    
    public static function globalRequest( $name, $default = '' ) {
        $RV = '';
        $array = $_GET;

        if ( isset( $array[ $name ] ) ) {
                $RV = $array[ $name ];
        }else{
            $array = $_POST;
            if ( isset( $array[ $name ] ) ) {
                    $RV = $array[ $name ];
            }                
        }
        
        if(empty($RV) && !empty($default)) return $default;
        return $RV;
    }      
}//Wiki_Embed

$wikiembed_object = new Wiki_Embed();