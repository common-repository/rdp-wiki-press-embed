<?php if ( ! defined('WP_CONTENT_DIR')) exit('No direct script access allowed'); ?>
<?php
if ( ! class_exists('RDP_WE_PPE') ) :
class RDP_WE_PPE {
    public static $postMetaKey = 'ppebook-cache-key';
    
    public static function shortcode_handler($url,$atts,$content = null){
        $sKey  = get_post_meta(get_the_ID(), self::$postMetaKey, true);
        $sHTML = '';
        $contentPieces = array();
        if(!empty($sKey))$contentPieces = get_transient( $sKey );
        if(empty($contentPieces))$contentPieces = self::grabContentFromPediaPress($url,$atts,$contentPieces,$content);            

        $sHTML .= self::renderContent($url, $atts, $contentPieces, $content);
        self::handleScripts($atts,$content);
        return $sHTML;
    } //shortcode
    
    private static function handleScripts($atts,$content = null){
        wp_enqueue_script( 'jquery-colorbox', plugins_url( '/resources/js/jquery.colorbox.min.js',RDP_WE_PLUGIN_BASENAME),array("jquery"), "1.3.20.2", true );        
        wp_enqueue_script( 'rdp-we-ppe-overlay', plugins_url( '/resources/js/pediapress-overlay.js',RDP_WE_PLUGIN_BASENAME),array("jquery"), "1.0", true );        
        $params = array(
            'has_content' => 0,
            'links_active' => 1
            );
        
        switch ($atts['toc_links']) {
            case 'logged-in':
                if(!is_user_logged_in())$params['links_active'] = 0;
                break;
            case 'disabled':
                $params['links_active'] = 0;
                break;
            default:
                break;
        }        
        
        if(!empty($content)){
            $params['has_content'] = 1;
            wp_enqueue_script("jquery-ui-tabs");
            wp_enqueue_style( 'wiki-embed-admin-core-style', plugins_url( '/admin/css/jquery-ui.css',RDP_WE_PLUGIN_BASENAME ), null,'1.11.2' );            
            wp_enqueue_style( 'wiki-embed-admin-theme-style', plugins_url( '/admin/css/jquery-ui.theme.min.css',RDP_WE_PLUGIN_BASENAME ), array('wiki-embed-admin-core-style'),'1.11.2' );             
        } 
        wp_localize_script( 'rdp-we-ppe-overlay', 'rdp_we_ppe', $params );
        wp_enqueue_style( 'colorbox-style', plugins_url( '/resources/css/colorbox.css',RDP_WE_PLUGIN_BASENAME),false, "1.3.20.2", 'screen');        
        do_action('rdp_pp_book_scripts_enqueued',$atts, $content);        
    }//handleScripts
    
    public static function grabContentFromPediaPress($url){
        $sKEY = 'ppebook-' . md5(esc_url( $url ));
        update_post_meta(get_the_ID(), self::$postMetaKey, $sKEY);
        if (false !== ( $special_query_results = get_transient( $sKEY ) ) ) return $special_query_results;
        
        require_once 'simple_html_dom.php'; 
        $html = rdp_file_get_html($url);
        $contentPieces = array();        
        if(!$html)return $contentPieces; 
 
        $bodyID = $html->find('body',0)->id;        
        $contentPieces['body_id'] = $bodyID;
        if($contentPieces['body_id'] == 'errorpage')return $contentPieces;
        $contentPieces = array(
            'body_id' => $bodyID,
            'link' => '',
            'cover_img_src' => '',
            'price_currency' => '',
            'price_amount' => '',
            'title' => '',
            'subtitle' => '',
            'editor' => '',
            'language' => '',
            'toc' => '',
            'book_size' => ''
        );
        
        $mainContent = $html->find('div#mainContent',0);
        
        $baseURL = 'https://pediapress.com';        
        if('book_show' == $bodyID):
            $contentPieces['link'] = $url;
        
            $priceCurrency = $mainContent->find('#price-currency',0);
            $contentPieces['price_currency'] = ($priceCurrency)? $priceCurrency->innertext : '';
            
            $priceAmount = $mainContent->find('#price-amount',0);
            $contentPieces['price_amount'] = ($priceAmount)? $priceAmount->innertext : '';
            
            $title = $mainContent->find('#title',0);
            $contentPieces['title'] = ($title)? $title->innertext : '';        
        
        
            $coverImage = $mainContent->find('#coverImage',0);
            if($coverImage){
                $imgSrc = $baseURL . $coverImage->src;
                $imgName = '' ;
                $sExt = '';
                
                $rawImage = wp_remote_get($imgSrc);
                if( !is_wp_error( $rawImage ) ) {
                    $sContentType = (isset($rawImage['headers']['content-type']))? $rawImage['headers']['content-type'] : '' ;
                    $mimeType = explode(';',$sContentType);
                    switch ($mimeType[0]) {
                        case 'image/jpeg':
                            $sExt = '.jpg';
                            break;
                        case 'image/gif':
                            $sExt = '.gif';
                            break;
                        case 'image/png':
                            $sExt = '.png';
                            break;
                        case 'image/tiff':
                            $sExt = '.tif';
                            break;
                        default:
                            break;
                    }
                }
                
                if(!empty($sExt)){
                    $upload_dir = wp_upload_dir();
                    $imgName = sanitize_title($contentPieces['title']) . $sExt;
                    $imgCacheDir = $upload_dir['basedir'] . '/rdp-wiki-press-embed';
                    if(!file_exists($imgCacheDir))mkdir($imgCacheDir, 0755);
                    $imgCacheSrc = $imgCacheDir . '/' . $imgName;
                    if(!file_exists($imgCacheSrc)){
                        $fp = fopen($imgCacheSrc, 'x');
                        fwrite($fp, $rawImage['body']); // save the full image
                        fclose($fp);                    
                    }   
                    $contentPieces['cover_img_src'] =  $upload_dir['baseurl'] . '/rdp-wiki-press-embed/' . $imgName;                    
                }else{
                    $contentPieces['cover_img_src'] = $imgSrc;
                }
            }

            $metaData = $mainContent->find('#metadata',0);
            if($metaData){
                $metaParas = $metaData->find('p');
                for ($i = 0; $i<=count($metaParas)-1;$i++ ){
                    $innerText = $metaParas[$i]->innertext;
                    if(strpos($innerText, 'Subtitle:') !== false){
                        $contentPieces['subtitle'] = trim(substr($innerText, strpos($innerText, '>')+1)); 
                        continue;
                    }

                    if(strpos($innerText, 'Editor:') !== false){
                        $contentPieces['editor'] = trim(substr($innerText, strpos($innerText, '>')+1)); 
                        continue;
                    }

                    if(strpos($innerText, 'Language:') !== false){
                        $contentPieces['language'] = trim(substr($innerText, strpos($innerText, '>')+1)); 
                        continue;
                    }                

                }                
            }
            
            $form = $mainContent->find('#bookopts-form',0);
            if($form){
                $formParas = $form->find('p');
                for ($i = 0; $i<=count($metaParas)-1;$i++ ){
                    $innerText = $formParas[$i]->innertext;
                    if(strpos($innerText, 'Book size:') !== false){
                        $contentPieces['book_size'] = trim(substr($innerText, strrpos($innerText, '>')+1)); 
                        continue;
                    }                    
                }
            }
            
            $toc = $mainContent->find('ul.outline',0);
            if($toc){
                foreach($toc->find('a') as $link){
                    $link->title = $link->innertext;
                }                
            }
            $contentPieces['toc'] = ($toc)? $toc->outertext : '';            

        endif;
        
        set_transient( $sKEY, $contentPieces, 0 );        
        $html->clear();        
        return $contentPieces;
        
    }//grabContentFromPediaPress
    
    static function unXMLEntities($string) { 
       return str_replace (array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;' ) , array ( '&', '"', "'", '<', '>' ), $string ); 
    } 


    public static function renderContent($URL,&$atts,$contentPieces,$content = null){
        if($contentPieces['body_id'] == 'errorpage')return 'Error: Unable to retieve content from PediaPress';
        $html = null;
        $sHTML = '';
        global $wikiembed_object;     
        $wikiembed_options = $wikiembed_object->options;         
        $atts = shortcode_atts( array(
        'url' => '',
        'image_show' => 1,
        'title_show' => 1,
        'subtitle_show' => 1,
        'editor_show' => 1,
        'language_show' => 1,
        'add_to_cart_show' => 1,
        'book_size_show' => 1,
        'beneath_cover_content' => empty($wikiembed_options['ppe-beneath-cover-content'])? '' : $wikiembed_options['ppe-beneath-cover-content'],
        'toc_show' => empty($wikiembed_options['toc-show'])? '0' : $wikiembed_options['toc-show'],
        'toc_links' => empty($wikiembed_options['toc-links'])? 'default' : $wikiembed_options['toc-links'],
        'cta_button_content' => empty($content)? empty($wikiembed_options['ppe-cta-button-content'])? '' : $wikiembed_options['ppe-cta-button-content'] : $content,
        'cta_button_text' => empty($wikiembed_options['ppe-cta-button-text'])? PPE_CTA_BUTTON_TEXT : $wikiembed_options['ppe-cta-button-text'],
        'cta_button_width' => empty($wikiembed_options['ppe-cta-button-width'])? PPE_CTA_BUTTON_WIDTH : $wikiembed_options['ppe-cta-button-width'],
        'cta_button_top_color' => empty($wikiembed_options['ppe-cta-button-top-color'])? PPE_CTA_BUTTON_TOP_COLOR : $wikiembed_options['ppe-cta-button-top-color'],
        'cta_button_font_color' => empty($wikiembed_options['ppe-cta-button-font-color'])? PPE_CTA_BUTTON_FONT_COLOR : $wikiembed_options['ppe-cta-button-font-color'],
        'cta_button_font_hover_color' => empty($wikiembed_options['ppe-cta-button-font-hover-color'])? PPE_CTA_BUTTON_FONT_HOVER_COLOR : $wikiembed_options['ppe-cta-button-font-hover-color'],
        'cta_button_border_color' => empty($wikiembed_options['ppe-cta-button-border-color'])? PPE_CTA_BUTTON_BORDER_COLOR : $wikiembed_options['ppe-cta-button-border-color'],
        'cta_button_bottom_color' => empty($wikiembed_options['ppe-cta-button-bottom-color'])? PPE_CTA_BUTTON_BOTTOM_COLOR : $wikiembed_options['ppe-cta-button-bottom-color'],
        'cta_button_box_shadow_color' => empty($wikiembed_options['ppe-cta-button-box-shadow-color'])? PPE_CTA_BUTTON_BOX_SHADOW_COLOR : $wikiembed_options['ppe-cta-button-box-shadow-color'],
        'cta_button_text_shadow_color' => empty($wikiembed_options['ppe-cta-button-text-shadow-color'])? PPE_CTA_BUTTON_TEXT_SHADOW_COLOR : $wikiembed_options['ppe-cta-button-text-shadow-color'],
        ), $atts );
        
        if(!is_numeric($atts['toc_show']))$atts['toc_show'] = 1;
        $arrTOCLinksDefaults = array('default','disabled','logged-in');
        if(!in_array( $atts['toc_links'], $arrTOCLinksDefaults ))$atts['toc_links'] = 'default';
        
        require_once 'simple_html_dom.php'; 
               
        $sDownloadButton = '';
        $sInlineHTML = '';        
        $bodyID = $contentPieces['body_id'];

        $baseURL = 'https://pediapress.com';
        switch ($bodyID) {
            case 'book_category':
                $html = rdp_str_get_html($contentPieces['dom']);
                if(!$html)break;
                $mainContent = $html->find('div#mainContent',0);
                if(!$mainContent)break;
                foreach($mainContent->find('#category-tree li a') as $categoryLink){
                    $categoryLink->href = $baseURL . $categoryLink->href;
                }
                foreach($mainContent->find('div.s1 a') as $link){
                    if(substr($link->href, 0, 1)== '?'){
                        $link->href = $URL . $link->href;                        
                    }else{
                        $link->href = $baseURL . $link->href;                       
                    }

                }
                foreach ($mainContent->find('#bookList li img') as $coverImage) {
                    $coverImage->src = $baseURL . $coverImage->src;
                }
                $sHTML = $mainContent->outertext;
                $html->clear();                
                break;
            case 'book_show':
                $sClasses = '';
                if($atts['image_show'] == 0)$sClasses .= ' no-cover';
                if($atts['title_show'] == 0)$sClasses .= ' no-title';
                if($atts['subtitle_show'] == 0)$sClasses .= ' no-subtitle';
                if($atts['editor_show'] == 0)$sClasses .= ' no-editor';
                if($atts['language_show'] == 0)$sClasses .= ' no-language';
                if($atts['add_to_cart_show'] == 0)$sClasses .= ' no-add-to-cart';
                
                $sMainContentClasses = apply_filters('rdp_pp_book_main_content_classes', $sClasses ) ;
                $sHTML = '<div id="mainContent" class="book_show' . $sMainContentClasses . '">';
                $sHTML .= '<div class="wrap" style="clear: right;"><div class="s1 w4"><div id="coverPreviewArea" class="nico_18">';
                if($atts['image_show'] == 1 && !empty($contentPieces['cover_img_src'])):
                    $sHTML .= '<div class="ready">';
                    //$sHTML .= "<a target='_new' href='";
                    //$sHTML .= (!empty($atts['cta_button_content']))? "#rdp_ppe_inline_content" : $URL ;
                    $sHTML .= '<img id="coverImage" src="' . $contentPieces['cover_img_src'] . '" alt="" style="max-width: 201px;height: auto"/>';
                    //$sHTML .= "' class='ppe-cover-link'>" . $coverImage . "</a>";                
                    $sHTML .= '</div><!-- .ready -->';
                    
                endif;
                if(!empty($atts['beneath_cover_content']))$sHTML .= '<div id="contentBeneathCover">' . $atts['beneath_cover_content'] . '</div>';
                $sHTML .= '</div><!-- #coverPreviewArea --></div><!-- .s1 .w4 -->';                
                
                $fIncludeMeta = $atts['title_show'] == 1 || $atts['subtitle_show'] == 1 || $atts['editor_show'] == 1 || $atts['language_show'] == 1 ;
                if($fIncludeMeta):
                    $sHTML .= '<div id="metadata" class="s0l w4">';
                    if($atts['title_show'] == 1) $sHTML .= '<p><label id="title-label">Title:</label><span id="title">' . $contentPieces['title'] . '</span></p>';
                    if($atts['subtitle_show'] == 1 && !empty($contentPieces['subtitle'])) $sHTML .= '<p><label id="subtitle-label">Subtitle:</label><span id="subtitle">' . $contentPieces['subtitle'] . '</span></p>';
                    if($atts['editor_show'] == 1 && !empty($contentPieces['editor'])) $sHTML .= '<p><label id="editor-label">Editor:</label><span id="editor">' . $contentPieces['editor'] . '</span></p>';
                    if($atts['language_show'] == 1 && !empty($contentPieces['language'])) $sHTML .= '<p><label id="language-label">Language:</label><span id="language">' . $contentPieces['language'] . '</span></p>';
                    if($atts['book_size_show'] == 1 && !empty($contentPieces['book_size'])) $sHTML .= '<p><label id="book-size-label">Book size:</label><span id="book-size">' . $contentPieces['book_size'] . '</span></p>';
                    
                    $sPriceCurrency = (!empty($contentPieces['price_currency']))? $contentPieces['price_currency'] : '';
                    $sPriceAmount = (!empty($contentPieces['price_amount']))? $contentPieces['price_amount'] : '';
                    $sAddToCartHREF = (!empty($atts['cta_button_content']))? "#rdp_ppe_inline_content" : $URL ;
                    $sAddToCartHREF = apply_filters('rdp_pp_book_atc_href', $sAddToCartHREF, $URL ) ;
                    $sAddToCartText = "Print Edition - {$sPriceCurrency} {$sPriceAmount}";

                    if($atts['add_to_cart_show'] == 1 ):
                        $sATC = '<div id="add-to-cart-box">';
                        $sATC .= '<a href="' . $sAddToCartHREF . '" class="ppe-add-to-cart cboxElement" target="_new">' . $sAddToCartText . '</a>';
                        $sATC .= '</div><!-- #add-to-cart-box -->'; 
                        $sHTML .= apply_filters('rdp_pp_atc_button', $sATC ) ;
                    endif; 
                    
                    if(!empty($atts['cta_button_content'])){
                        if($atts['add_to_cart_show'] == 1 )$sDownloadButton .= "<div class='rdp-ppe-inline-content-sep'>OR</div>";
                        $sDownloadButton .= "<div id='rdp-pp-cta-button-box'><a class='rdp-ppe-cta-button' href='#rdp_ppe_inline_content'>{$atts['cta_button_text']}</a></div>";
                        $sHTML .= apply_filters('rdp_pp_book_cta_button', $sDownloadButton) ;
                        $sInlineHTML .= "<div id='rdp_ppe_inline_content_wrapper' style='display:none'><div id='rdp_ppe_inline_content' class='$sClasses'>";
                        $sInlineHTML .= '<div id="wiki-embed-tabs" style="position: static;">';
                        $sInlineHTML .= "<ul>";
                        $sInlineHTML .= "<li><a href='#tab-1'>{$atts['cta_button_text']}</a></li>";
                        $sInlineHTML .= "<li><a href='#tab-2'>{$sAddToCartText}</a></li>";
                        $sInlineHTML .= "</ul>";
                        $sInlineHTML .= '<div id="tab-1" class="ppe-tab">';
                        $sInlineHTML .= do_shortcode($atts['cta_button_content']);
                        $sInlineHTML .= "</div><!-- #tab-1 -->";
                        $sInlineHTML .= '<div id="tab-2" class="ppe-tab">';                    
                        $sInlineHTML .= "<iframe src='{$URL}'></iframe>";
                        $sInlineHTML .= "</div><!-- #tab-2 -->"; 
                        $sInlineHTML .= "</div><!-- #ppe-tabs -->";                    
                        $sInlineHTML .= "</div><!-- #rdp_ppe_inline_content --></div>";
                    }                       

                    $sHTML .= '</div><!-- #metadata -->';
                endif;
                $sHTML .= '</div>';
                if(!empty($atts['toc_show'])):
                    $mainContent = rdp_str_get_html('<html><body>'.$contentPieces['toc'].'</body></html>');
                    if($mainContent){
                        $sHTML .= '<div></div><h2>Table of Contents:</h2>';
                        switch ($atts['toc_links']) {
                            case 'logged-in':
                                if(!is_user_logged_in()):
                                    foreach($mainContent->find('ul.outline li a') as $link){
                                        $link->outertext = $link->innertext;
                                    }
                                endif;
                                break;
                            case 'disabled':
                                foreach($mainContent->find('ul.outline li a') as $link){
                                    $link->outertext = $link->innertext;
                                }
                                break;
                            default:
                                break;
                        }
                        $sHTML .= $mainContent->find('ul.outline',0)->outertext;
                    }
                endif;
                
                $sHTML .= '</div><!-- #mainContent -->';
                $sHTML .= $sInlineHTML;

               break;

            default:
                break;
        }//switch ($bodyID)


        $sCTAButtonWidth = (is_numeric($atts['cta_button_width']))? $atts['cta_button_width'] . 'px' : 'auto' ;
        
        $style = <<<EOS
<style type="text/css">
#mainContent div#rdp-pp-cta-button-box a {
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
	font-size:15px;
	font-weight:bold;
	font-style:normal;
	height:30px;
	line-height:30px;
	width:{$sCTAButtonWidth};
	padding: 0 15px;            
	text-decoration:none;
	text-align:center;
	text-shadow:1px 1px 0px {$atts['cta_button_text_shadow_color']};
}
#mainContent div#rdp-pp-cta-button-box a:hover{
    color: {$atts['cta_button_font_hover_color']};
}
#mainContent div#rdp-pp-cta-button-box a:active {
	position:relative;
	top:1px;
}</style>   
   
   
EOS;
       
        $sHTML .= $style;

        return $sHTML;
    }//renderContent
    
    
}//RDP_WE_PPE
endif;

/* EOF */
