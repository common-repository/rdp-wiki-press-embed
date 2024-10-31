<?php if ( ! defined('WP_CONTENT_DIR')) exit('No direct script access allowed'); ?>
<?php
if ( ! class_exists('RDP_WE_PPGALLERY') ) :
class RDP_WE_PPGALLERY {
    public $_options; // GLOBAL Options 
    public $_version;  
    private $_atts;
    private $_content;
    
    public function __construct($version,$options){
        $this->_version = $version;
        $this->_options = $options;        
        add_shortcode( 'wiki-embed-ppgallery', array(&$this, 'shortcode') );        
    }//__construct    
    
    public function shortcode($atts,$content = null){
        $nGUID = uniqid();
        $sHTML = '';
        global $wikiembed_object;     
        $wikiembed_options = $wikiembed_object->options;          
        $atts = shortcode_atts(array(
            'col' => '2',
            'num' => '10',
            'cat' => '',
            'tag' => '',
            'size' => 'small',
            'excerpt_length' => 55,
            'sort_col' => 'post_title',
            'sort_dir' => 'ASC',
            'cta_button_content' => empty($content)? empty($wikiembed_options['ppe-cta-button-content'])? '' : $wikiembed_options['ppe-cta-button-content'] : $content,
            'cta_button_text' => empty($wikiembed_options['ppe-cta-button-text'])? PPE_DOWNLOAD_BUTTON_TEXT : $wikiembed_options['ppe-cta-button-text'],
            'cta_button_width' => empty($wikiembed_options['ppe-cta-button-width'])? PPE_DOWNLOAD_BUTTON_WIDTH : $wikiembed_options['ppe-cta-button-width'],
            'cta_button_top_color' => empty($wikiembed_options['ppe-cta-button-top-color'])? PPE_DOWNLOAD_BUTTON_TOP_COLOR : $wikiembed_options['ppe-cta-button-top-color'],
            'cta_button_font_color' => empty($wikiembed_options['ppe-cta-button-font-color'])? PPE_DOWNLOAD_BUTTON_FONT_COLOR : $wikiembed_options['ppe-cta-button-font-color'],
            'cta_button_font_hover_color' => empty($wikiembed_options['ppe-cta-button-font-hover-color'])? PPE_DOWNLOAD_BUTTON_FONT_HOVER_COLOR : $wikiembed_options['ppe-cta-button-font-hover-color'],
            'cta_button_border_color' => empty($wikiembed_options['ppe-cta-button-border-color'])? PPE_DOWNLOAD_BUTTON_BORDER_COLOR : $wikiembed_options['ppe-cta-button-border-color'],
            'cta_button_bottom_color' => empty($wikiembed_options['ppe-cta-button-bottom-color'])? PPE_DOWNLOAD_BUTTON_BOTTOM_COLOR : $wikiembed_options['ppe-cta-button-bottom-color'],
            'cta_button_box_shadow_color' => empty($wikiembed_options['ppe-cta-button-box-shadow-color'])? PPE_DOWNLOAD_BUTTON_BOX_SHADOW_COLOR : $wikiembed_options['ppe-cta-button-box-shadow-color'],
            'cta_button_text_shadow_color' => empty($wikiembed_options['ppe-cta-button-text-shadow-color'])? PPE_DOWNLOAD_BUTTON_TEXT_SHADOW_COLOR : $wikiembed_options['ppe-cta-button-text-shadow-color'],            
            ), $atts);
        
        if(!is_numeric($atts['col']))return $sHTML;
        if(!is_numeric($atts['num']))return $sHTML; 
        
        $termIDs = '';
        if(!empty($atts['cat'])){
            $termIDs = $atts['cat'];
        }
        if(!empty($atts['tag'])){
            if(!empty($termIDs))$termIDs .= ',';
            $termIDs .= $atts['tag'];
        }        
        
        global $wpdb;
        $sCountSQL = self::buildCountSQL($termIDs);
        $nRecordCount = $wpdb->get_var( $sCountSQL );
        if(empty($nRecordCount))return $sHTML;
        $totalPages = ceil((int)$nRecordCount/(int)$atts['num']);        
        $currentPage = (isset($_POST['txtWEPPGalleryCurrentPage']))? $_POST['txtWEPPGalleryCurrentPage'] : 1;
        $paged = $currentPage;
        
        if(isset($_POST['ddWEPPGallerySelect'])) $paged = (int)$_POST['ddWEPPGallerySelect'];
        if(isset($_POST['btnWEPPGalleryFirst'])) $paged = 1;
        if(isset($_POST['btnWEPPGalleryLast'])) $paged = (int)$totalPages;   
        if(isset($_POST['btnWEPPGalleryPrevious'])) $paged = (int)$currentPage - 1;
        if(isset($_POST['btnWEPPGalleryNext'])) $paged = (int)$currentPage + 1;
        if($paged < 1)$paged = 1;
        if($paged > $totalPages)$paged = $totalPages;
        
        $start = ($paged - 1)*(int)$atts['num'];
        $sFetchSQL = self::buildFetchSQL($termIDs, $start, $atts['num'],$atts['sort_col'],$atts['sort_dir']);
        $rows = $wpdb->get_results($sFetchSQL);
        $sHTML .= '<div class="rdp_pp_book_gallery '. strtolower($atts['size']) .' rdp_pp_book_gallery-'. $nGUID .'">';
        $sHTML .= self::renderGallery($rows, (int)$atts['col'], $atts,$nGUID);
        $sHTML .= '<div id="rdp_pp_gallery_footer">';
        $sHTML .= self::renderPaging($paged, $totalPages);
        $sRSSLink = self::buildRSSLink($atts);
        $sHTML .= '<a class="rdp-pp-gallery-rss" target="_new" href="' . $sRSSLink . '">';
        $sHTML .= '<img class="rdp-pp-gallery-rss" src="' . plugins_url( '/css/images/rss-icon.png',__FILE__)  . '" />';
        $sHTML .= '</a>';
        $sHTML .= '</div><!-- #rdp_pp_gallery_footer -->';
        $sHTML .= '</div><!-- .rdp_pp_book_gallery -->';
        $sInlineHTML = '';
        if(!empty($atts['cta_button_content'])){
            $sInlineHTML .= "<div id='rdp_pp_gallery_inline_content_wrapper' style='display:none'><div id='rdp_pp_gallery_inline_content'>";
            $sInlineHTML .= '<div id="wiki-embed-tabs" style="position: static;">';
            $sInlineHTML .= "<ul>";
            $sInlineHTML .= "<li><a href='#tab-1'>{$atts['cta_button_text']}</a></li>";
            $sInlineHTML .= "<li><a id='tab-2-link' href='#tab-2'></a></li>";
            $sInlineHTML .= "</ul>";
            $sInlineHTML .= '<div id="tab-1" class="ppe-tab">';
            $sInlineHTML .= do_shortcode($atts['cta_button_content']);
            $sInlineHTML .= "</div><!-- #tab-1 -->";
            $sInlineHTML .= '<div id="tab-2" class="ppe-tab">';                    
            $sInlineHTML .= "<div id='rdp_pp_gallery_frame_loading'>Page is loading...</div>​<iframe style='display: none;' src=''></iframe>";
            $sInlineHTML .= "</div><!-- #tab-2 -->"; 
            $sInlineHTML .= "</div><!-- #ppe-tabs -->";                    
            $sInlineHTML .= "</div><!-- #rdp_pp_gallery_inline_content --></div>";
        }         
        $sHTML .= $sInlineHTML;
        $this->_atts = $atts;
        $this->_content = $content;
        add_action('wp_footer', array(&$this,'scriptsEnqueue'));

        return $sHTML;
    }//shortcode_handler
    
    private static function buildRSSLink($atts){
        $sURL = trailingslashit( get_bloginfo('url') );
        $params = array();
        if(!empty($atts['cat']))$params['cat'] = $atts['cat'];
        if(!empty($atts['tag'])){
            $tagIDs = explode(',', $atts['tag']);
            $params['tag'] = '';
            foreach($tagIDs as $termID){
                $oTerm = get_term_by('id', $termID, 'post_tag');
                if(!empty($oTerm)){
                    if(strlen($params['tag']) > 0) $params['tag'] .= ',';
                    $params['tag'] .= $oTerm->slug;
                }
            }            
        }
        $params['feed'] = 'pediapress';
        $sURL = add_query_arg($params,$sURL);
        return $sURL;
    }//buildRSSLink
    
    public function scriptsEnqueue(){
        self::handleScripts($this->_atts, $this->_content);
    }
    
    private static function handleScripts($atts,$content = null){
        wp_register_style( 'rdp-we-pp-style-common', plugins_url( 'css/pediapress.common.css' , __FILE__ ) );
        wp_enqueue_style( 'rdp-we-pp-style-common' );

        $filename = get_stylesheet_directory() .  '/pediapress.custom.css';
        if (file_exists($filename)) {
            wp_register_style( 'rdp-pp-style-custom',get_stylesheet_directory_uri() . '/pediapress.custom.css',array('rdp-we-pp-style-common' ) );
            wp_enqueue_style( 'rdp-pp-style-custom' );
        } 

        wp_enqueue_script( 'jquery-colorbox', plugins_url( '/resources/js/jquery.colorbox.min.js',RDP_WE_PLUGIN_BASENAME),array("jquery"), "1.3.20.2", true );        
        wp_enqueue_script( 'pp-gallery-overlay', plugins_url( '/resources/js/pediapress-gallery-overlay.js',RDP_WE_PLUGIN_BASENAME),array("jquery",'jquery-colorbox'), "1.0", true );        
        if(!empty($content)){
            $params = array('fcontent' => 1);
            wp_localize_script( 'pp-gallery-overlay', 'rdp_pp_gallery', $params );
            wp_enqueue_script("jquery-ui-tabs");
            wp_enqueue_style( 'wiki-embed-admin-core-style', plugins_url( '/admin/css/jquery-ui.css',RDP_WE_PLUGIN_BASENAME ), null,'1.11.2' );            
            wp_enqueue_style( 'wiki-embed-admin-theme-style', plugins_url( '/admin/css/jquery-ui.theme.min.css',RDP_WE_PLUGIN_BASENAME ), array('wiki-embed-admin-core-style'),'1.11.2' );             
        }  
        if(!wp_style_is('jquery-colorbox'))wp_enqueue_style( 'jquery-colorbox', plugins_url( '/resources/css/colorbox.css',RDP_WE_PLUGIN_BASENAME),false, "1.3.20.2", 'screen');        
        
        do_action('rdp_pp_gallery_scripts_enqueued',$atts, $content);        
    }//handleScripts

    private static function renderGallery($rows,$cols,$atts,$nGUID){
        $sHTML = '';
        $nCols = (count($rows) < $cols)? count($rows) : $cols ;
        $width = floor(100/$nCols)-1.5;
        $sClass = '';
        $nCounter = 0;
        $template = '';
        $upload_dir = wp_upload_dir();
        $imgCacheDir = $upload_dir['basedir'] . '/rdp-wiki-press-embed';
        $customTemplateSrc = $imgCacheDir . '/ppgallery.column.results.html';
        
        if(file_exists($customTemplateSrc)){
            $template = file_get_contents($customTemplateSrc);
        }else{
            $template = file_get_contents(RDP_WE_PLUGIN_BASEDIR . 'resources/ppgallery-template/ppgallery.column.results.html');
        }
        

        foreach($rows as $row):
            $contentPieces = unserialize($row->option_value);
            $sLink = '';
            if(!empty($atts['cta_button_content'])){
                $sLink = '#rdp_pp_gallery_inline_content';
            }else{
                $sLink = $contentPieces['link'];
            }
                    
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
            $sExcerpt = wp_trim_words( $row->post_excerpt, (int)$atts['excerpt_length'], '&hellip; <a href="'. $sPostLink .'">Read More</a>' );
            
            $sHTML .= '<div id="weppgallery-box-' . $row->ID . '" class="weppgallery-box weppgallery-col-'. $nCounter % $cols .'">';
            $sGalleryItem = str_replace (array ( 
                '%%Link%%' , 
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
                '%%CTAButtonText%%',
                '%%BookSize%%') , 
                array ( 
                $sLink, 
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
                $atts['cta_button_text'],
                $sBookSize), 
                $template );
            
            $sHTML .= apply_filters('rdp_pp_gallery_item', 
                    $sGalleryItem, 
                    array ( 
                    $sLink, 
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
                    $atts['cta_button_text'],
                    $sBookSize));
            $sHTML .= '<input type="hidden" id="pp-src-' . $row->ID . '" value="' . $contentPieces['link'] . '" />';
            $sHTML .= '</div>';
            $nCounter++;
            if($nCounter == count($rows))$sClass = ' last';
            if ($nCounter % $cols === 0) {
                $sHTML .= '<div class="clear weppgallery-row-sep'.$sClass.'" style="height: 2px;background: none;"></div>';
            }
        endforeach;
        if ($nCounter % $cols !== 0)$sHTML .= '<div class="clear weppgallery-row-sep last" style="height: 2px;background: none;"></div>';
        
        $sHTML .= '<style type="text/css">';
        $sHTML .= 'div.rdp_pp_book_gallery-'.$nGUID.' div.weppgallery-box{width: '. $width . '%;}';
        $sHTML .= '</style>';
        
        $style = <<<EOS
<style type="text/css">
div.rdp_pp_book_gallery-{$nGUID} p.rdp-pp-gallery-cta-button-container a {
	-o-box-shadow:inset 0px 1px 0px 0px {$atts['cta_button_box_shadow_color']};
	-moz-box-shadow:inset 0px 1px 0px 0px {$atts['cta_button_box_shadow_color']};
	-webkit-box-shadow:inset 0px 1px 0px 0px {$atts['cta_button_box_shadow_color']};
	box-shadow:inset 0px 1px 0px 0px {$atts['cta_button_box_shadow_color']};
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, {$atts['cta_button_top_color']}), color-stop(1, {$atts['cta_button_bottom_color']}) );
	background:-o-gradient( linear, left top, left bottom, color-stop(0.05, {$atts['cta_button_top_color']}), color-stop(1, {$atts['cta_button_bottom_color']}) );
	background:-moz-linear-gradient( center top, {$atts['cta_button_top_color']} 5%, {$atts['cta_button_bottom_color']} 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='{$atts['cta_button_top_color']}', endColorstr='{$atts['cta_button_bottom_color']}');
	background-color:{$atts['cta_button_top_color']};
        background-image:linear-gradient(
                to bottom,
                {$atts['cta_button_top_color']},
                {$atts['cta_button_bottom_color']}
        );            
	-webkit-border-top-left-radius:8px;
	-moz-border-radius-topleft:8px;
	border-top-left-radius:8px;
	-webkit-border-top-right-radius:8px;
	-moz-border-radius-topright:8px;
	border-top-right-radius:8px;
	-webkit-border-bottom-right-radius:8px;
	-moz-border-radius-bottomright:8px;
	border-bottom-right-radius:8px;
	-webkit-border-bottom-left-radius:8px;
	-moz-border-radius-bottomleft:8px;
	border-bottom-left-radius:8px;
	text-indent:0px;
	border:1px solid {$atts['cta_button_border_color']};
	display:inline-block;
	color:{$atts['cta_button_font_color']};
	font-family:Arial, Helvetica, sans-serif;
	font-size:12px;
	font-weight:normal;
	font-style:normal;
	height:20px;
	line-height:20px;
	width: auto;
	padding: 0 15px;            
	text-decoration:none;
	text-align:center;
	text-shadow:1px 1px 0px {$atts['cta_button_text_shadow_color']};
}
div.rdp_pp_book_gallery-{$nGUID} p.rdp-pp-gallery-cta-button-container a:hover{
    color: {$atts['cta_button_font_hover_color']};
}
div.rdp_pp_book_gallery-{$nGUID} p.rdp-pp-gallery-cta-button-container a:active {
	position:relative;
	top:1px;
}</style>   
   
   
EOS;
       
        $sHTML .= $style;        

        return $sHTML;
    }//renderGallery
    
    
    private static function renderPaging($paged,$totalPages){
        if($totalPages == 1)return '';
        
        $sHTML = '<form class="rdp-weppg-paging-form" method="post" action="">'; 
        $sHTML .= '<div id="rdp-weppg-paging-controls" class="rdp-weppg-paging-controls"><div class="wrap">';
        $sHTML .= '<input type="submit"';
        if($paged == 1)$sHTML .= ' disabled="disabled" ';
        $sHTML .= 'name="btnWEPPGalleryFirst" class="rdp-weppg-paging-link rdp-weppg-paging-first" pg="1" title="First Page" value="<<"/>';
        $sHTML .= '<input type="submit"';
        if($paged == 1)$sHTML .= ' disabled="disabled" ';        
        $sHTML .= 'name="btnWEPPGalleryPrevious" class="rdp-weppg-paging-link rdp-weppg-paging-previous" title="Previous Page" value="<" />';

        $sHTML .= ' <span class="rdp-weppg-paging-select-wrap rdp-weppg-paging-select-wrap"><span id="rdp-weppg-paging-select-label"></span><select name="ddWEPPGallerySelect" class="rdp-weppg-paging-select" onchange="this.form.submit()">';

        for ($x = 1; $x <= $totalPages; $x++) {
            $sHTML .= '<option value="'.$x.'" '. selected($x, $paged,FALSE) .'>'.$x.'</option>';
        } 

        $sHTML .= '</select> <span> of ' . $totalPages . '</span></span> ';
        $sHTML .= '<input type="submit"';
        if($paged == $totalPages)$sHTML .= ' disabled="disabled" ';
        $sHTML .= 'name="btnWEPPGalleryNext" class="rdp-weppg-paging-link rdp-weppg-paging-next" title="Next Page" value=">" />';
        $sHTML .= '<input type="submit"';
        if($paged == $totalPages)$sHTML .= ' disabled="disabled" ';
        $sHTML .= 'name="btnWEPPGalleryLast" class="rdp-weppg-paging-link rdp-weppg-paging-last" title="Last Page" value=">>">';
        
        $sHTML .= '</div><!-- wrap --></div><!-- .rdp-weppg-paging-controls -->';       
        $sHTML .= '<input type="hidden" name="txtWEPPGalleryCurrentPage" value="' . $paged . '" />';
        $sHTML .= '</form>';
        return $sHTML;      
    }//renderPaging


    private static function buildCountSQL($termIDs){
        global $wpdb;
        $sSQL = '';
        
        if(!empty($termIDs)):
        $sSQL = <<<EOS
SELECT COUNT(*) record_count
FROM (SELECT p.ID, pm.meta_value
FROM {$wpdb->posts} p, {$wpdb->postmeta} pm
WHERE p.ID = pm.post_id
AND pm.meta_key = 'ppebook-cache-key'
AND p.post_status = 'publish'
AND p.post_type = 'post'
AND p.ID IN
(SELECT DISTINCT object_id 
FROM {$wpdb->term_relationships}
WHERE term_taxonomy_id IN 
(SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE term_id IN ({$termIDs}))))p,
wp_options o 
WHERE o.option_name = concat('_transient_',p.meta_value);   
   
EOS;
    else:
        $sSQL = <<<EOS
SELECT COUNT(*) record_count
FROM (SELECT p.ID, pm.meta_value
FROM {$wpdb->posts} p, {$wpdb->postmeta} pm
WHERE p.ID = pm.post_id
AND pm.meta_key = 'ppebook-cache-key'
AND p.post_status = 'publish'
AND p.post_type = 'post')p,
wp_options o 
WHERE o.option_name = concat('_transient_',p.meta_value);   
   
EOS;

    endif;
        
        return $sSQL;
    }//buildCountSQL
    
    public static function buildFetchSQL($termIDs, $start, $rowCount, $orderCol = 'post_title', $orderAttr = 'ASC'){
        global $wpdb;
        $sSQL = '';
        
        if(!empty($termIDs)):        
        $sSQL = <<<EOS
SELECT p.*,o.*
FROM (SELECT p.ID, p.post_content, p.post_title, p.post_excerpt, p.post_date, pm.meta_value
FROM {$wpdb->posts} p, {$wpdb->postmeta} pm
WHERE p.ID = pm.post_id
AND pm.meta_key = 'ppebook-cache-key'
AND p.post_status = 'publish'
AND p.post_type = 'post'
AND p.ID IN
(SELECT DISTINCT object_id 
FROM {$wpdb->term_relationships}
WHERE term_taxonomy_id IN 
(SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE term_id IN ({$termIDs}))))p,
{$wpdb->options} o 
WHERE o.option_name = concat('_transient_',p.meta_value)   
ORDER BY p.{$orderCol} {$orderAttr} LIMIT {$start}, {$rowCount};   
EOS;
    else:
        $sSQL = <<<EOS
SELECT p.*,o.*
FROM (SELECT p.ID, p.post_content, p.post_title, p.post_excerpt, p.post_date, pm.meta_value
FROM {$wpdb->posts} p, {$wpdb->postmeta} pm
WHERE p.ID = pm.post_id
AND pm.meta_key = 'ppebook-cache-key'
AND p.post_status = 'publish'
AND p.post_type = 'post')p,
{$wpdb->options} o 
WHERE o.option_name = concat('_transient_',p.meta_value)   
ORDER BY p.{$orderCol} {$orderAttr} LIMIT {$start}, {$rowCount};   
EOS;

    endif;
        
        return $sSQL;        
    }//buildFetchSQL    
    
    
}//RDP_WE_PPGALLERY
endif;
/* EOF */
