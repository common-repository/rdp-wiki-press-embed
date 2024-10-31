<?php
add_action( 'admin_footer', 'wikiembed_overlay_popup_form' );
add_action( 'media_buttons', 'wikiembed_overlay_buttons' );

/**
 * wikiembed_overlay_buttons function.
 * 
 * @access public
 * @param mixed $context
 * @return void
 */
function wikiembed_overlay_buttons( $page = null, $target = null ) {
    global $pagenow;
    if ( !in_array( $pagenow, array( "post.php", "post-new.php" ) ))return;
    $wiki_embed_overlay_image_button = plugins_url('/rdp-wiki-press-embed/resources/img/icon.png');
    $output_link = '<a href="#TB_inline?height=500&width=690&inlineId=wiki_embed_form" class="thickbox button" title="' .__("RDP Wiki-Press Embed", 'wiki-embed') . '" id="wiki-embed-overlay-button">';
    $output_link .= '<span class="wp-media-buttons-icon" style="background: url('. $wiki_embed_overlay_image_button.'); background-repeat: no-repeat; background-position: left bottom;"/></span>';
    $output_link .= '</a><style>#wiki_embed_form{ display:none;}</style>';
    echo $output_link;
}

/**
 * wikiembed_overlay_popup_form function.
 * 
 * @access public
 * @return void
 */
function wikiembed_overlay_popup_form() {
    global $wikiembed_object, $pagenow, $post;
    if ( !in_array( $pagenow, array( "post.php", "post-new.php" ) ))return;        
	$wikiembed_options = $wikiembed_object->options;
	
            wp_enqueue_script('wiki-embed-admin-overlay', plugins_url( '/admin/js/script.admin-overlay.js',RDP_WE_PLUGIN_BASENAME), array("jquery","jquery-ui-tabs"), '1.0', true);
            $params = array(
                            'pp_button_text' => (PPE_CTA_BUTTON_TEXT != $wikiembed_options['ppe-cta-button-text'])? $wikiembed_options['ppe-cta-button-text'] : PPE_CTA_BUTTON_TEXT,
                            'pp_button_width' => (PPE_CTA_BUTTON_WIDTH != $wikiembed_options['ppe-cta-button-width'])? $wikiembed_options['ppe-cta-button-width'] : PPE_CTA_BUTTON_WIDTH,
                            'pp_button_top_color' => (PPE_CTA_BUTTON_TOP_COLOR != $wikiembed_options['ppe-cta-button-top-color'])? $wikiembed_options['ppe-cta-button-top-color'] : PPE_CTA_BUTTON_TOP_COLOR,
                            'pp_button_bottom_color' => (PPE_CTA_BUTTON_BOTTOM_COLOR != $wikiembed_options['ppe-cta-button-bottom-color'])? $wikiembed_options['ppe-cta-button-bottom-color'] : PPE_CTA_BUTTON_BOTTOM_COLOR,
                            'pp_button_font_color' => (PPE_CTA_BUTTON_FONT_COLOR != $wikiembed_options['ppe-cta-button-font-color'])? $wikiembed_options['ppe-cta-button-font-color'] : PPE_CTA_BUTTON_FONT_COLOR,
                            'pp_button_font_hover_color' => (PPE_CTA_BUTTON_FONT_HOVER_COLOR != $wikiembed_options['ppe-cta-button-font-hover-color'])? $wikiembed_options['ppe-cta-button-font-hover-color'] : PPE_CTA_BUTTON_FONT_HOVER_COLOR,
                            'pp_button_border_color' => (PPE_CTA_BUTTON_BORDER_COLOR != $wikiembed_options['ppe-cta-button-border-color'])? $wikiembed_options['ppe-cta-button-border-color'] : PPE_CTA_BUTTON_BORDER_COLOR,
                            'pp_button_box_shadow_color' => (PPE_CTA_BUTTON_BOX_SHADOW_COLOR != $wikiembed_options['ppe-cta-button-box-shadow-color'])? $wikiembed_options['ppe-cta-button-box-shadow-color'] : PPE_CTA_BUTTON_BOX_SHADOW_COLOR,
                            'pp_button_text_shadow_color' => (PPE_CTA_BUTTON_TEXT_SHADOW_COLOR != $wikiembed_options['ppe-cta-button-text-shadow-color'])? $wikiembed_options['ppe-cta-button-text-shadow-color'] : PPE_CTA_BUTTON_TEXT_SHADOW_COLOR
                        );
            wp_localize_script('wiki-embed-admin-overlay', 'rdp_we_admin', $params);
            wp_enqueue_style( 'wiki-embed-admin-core-style', plugins_url( '/admin/css/jquery-ui.css',RDP_WE_PLUGIN_BASENAME ), null,'1.11.2' );            
            wp_enqueue_style( 'wiki-embed-admin-theme-style', plugins_url( '/admin/css/jquery-ui.theme.min.css',RDP_WE_PLUGIN_BASENAME ), array('wiki-embed-admin-core-style'),'1.11.2' );            
            
            ?>
	
		<div id="wiki_embed_form" style="display:none;">
                    <div id="wiki-embed-tabs" style="position: static;">
                      <ul>
                        <li><a href="#tabs-1"><?php _e('Wiki Page', 'wiki-embed'); ?></a></li>
                        <li><a href="#tabs-2"><?php _e('PediaPress Book', 'wiki-embed'); ?></a></li>
                        <li><a href="#tabs-3"><?php _e('PediaPress Gallery', 'wiki-embed'); ?></a></li>
                      </ul>
			<div id="tabs-1" class="wiki_embed_form_wrap">
                            <div class="media-item media-blank">
					<table class="describe">
						<tbody>
							<tr>
								<th valign="top" style="width: 130px;" class="label" scope="row">
									<span class="alignleft"><label for="wiki-embed-src"><?php _e('Source URL', 'wiki-embed'); ?></label></span>
									<span class="alignright"><abbr class="required" title="required" id="status_img">*</abbr></span>
								</th>
								<td class="field"><input type="text" aria-required="true" value="http://" name="wiki-embed-src" id="wiki-embed-src" size="60"><br /><br /></td>
							</tr>
							
							<?php if ( $wikiembed_options['tabs'] ): ?>
								<tr>
									<th valign="top" class="label" scope="row">
									</th>
									<td class="field"><input type="radio" name="wiki-embed-tabs" value=" tabs" class="wiki-embed-tabs" id="wiki-embed-tabs" <?php checked( $wikiembed_options['default']['tabs'],1 ); ?> />
									<span><label for="wiki-embed-tabs">Convert section headings to tabs</label></span>
									</td>
								</tr>
								<tr>
									<th valign="top" class="label" scope="row">
									</th>
									<td class="field"><input type="radio" name="wiki-embed-tabs" value=" accordion" class="wiki-embed-accordion" id="wiki-embed-accordion" <?php checked( $wikiembed_options['default']['tabs'],2 ); ?> />
									<span><label for="wiki-embed-accordion">Convert section headings to accordion</label></span>
									</td>
								</tr>
								<tr>
									<th valign="top" class="label" scope="row">
									</th>
									<td class="field"><input type="radio" name="wiki-embed-tabs" value=" " id="wiki-embed-normal-headers" id="wiki-embed-normal-headers" <?php checked( $wikiembed_options['default']['tabs'],0 ); ?> />
									<span><label for="wiki-embed-normal-headers">Don't convert section headings</label></span>
									</td>
								</tr>
							<?php else: ?>
								<tr>
									<th valign="top" class="label" scope="row">
									</th>
									<td class="field"><input type="checkbox" disabled="disabled" /><span><label for="wiki-embed-tabs"> <del>Top section converted into tabs</del></label></span>
									&mdash; to enable see the <a href="">Settings page</a>
									</td>
								</tr>
							<?php endif; ?>
							
							<tr>
								<th valign="top" class="label" scope="row">
								</th>
								<td class="field"><input type="checkbox" aria-required="true" value=" no-edit" name="wiki-embed-edit" id="wiki-embed-edit"<?php checked($wikiembed_options['default']['no-edit'] ); ?> /> <span ><label for="wiki-embed-edit"> Remove edit links</label></span></td>
							</tr>
							<tr>
								<th valign="top" class="label" scope="row">
								</th>
								<td class="field"><input type="checkbox" aria-required="true" value=" no-contents" name="wiki-embed-contents" id="wiki-embed-contents" <?php checked($wikiembed_options['default']['no-contents'] ); ?> /> <span ><label for="wiki-embed-contents"> Remove contents index</label></span></td>
							</tr>
							<tr>
								<th valign="top" class="label" scope="row">
								</th>
								<td class="field"><input type="checkbox" aria-required="true" value=" no-infobox" name="wiki-embed-infobox" id="wiki-embed-infobox" <?php checked($wikiembed_options['default']['no-infobox'] ); ?> /> <span ><label for="wiki-embed-infobox"> Remove info box</label></span></td>
							</tr>
							<tr>
								<td></td>
								<td><br />
									<input type="button" value="Insert into Post/ Page" onclick="wiki_embed_insert_overlay_form();" id="btnWikiEmbed" class="button">
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
                    
                    <div id="tabs-2" class="pp_embed_form_wrap">
                        <div class="media-item media-blank">
                            <table class="describe">
                                <tbody>
                                    <tr>
                                        <th valign="top" style="width: 130px;" class="label" scope="row">
                                                <span class="alignleft"><label for="src"><?php _e('Source URL', 'wiki-embed'); ?></label></span>
                                                <span class="alignright"><abbr class="required" title="required" id="status_img">*</abbr></span>
                                        </th>
                                        <td class="field"><input type="text" aria-required="true" value="http://" name="pp-embed-src" id="pp-embed-src" size="60"><br /><br /></td>
                                    </tr> 
                                    <tr>
                                        <th valign="top" class="label" scope="row"></th>
                                        <td class="field"><input type="checkbox" aria-required="true" value="1" name="pp-embed-display-toc" id="pp-embed-display-toc" <?php checked($wikiembed_options['toc-show'] ); ?> /> <span ><label for="pp-embed-display-toc"> Display Table of Contents for PediaPress book</label></span></td>
                                    </tr>  
                                    <tr>
                                        <th valign="top" class="label" scope="row"></th>
                                        <td class="field">
                                        <?php
                                            $sTOCLinks = empty($wikiembed_options['toc-links'])? 'default' : $wikiembed_options['toc-links'];
                                        ?>                                           
                                        <label><input name="pp-embed-toc-links" id="pp-embed_toc-links-default" type="radio" value="default"  <?php checked($sTOCLinks,"default"); ?> /> Default &mdash; TOC links are enabled</label>
                                        <br />
                                        <label><input name="pp-embed-toc-links" id="pp-embed_toc-links-logged-in" type="radio" value="logged-in" <?php checked($sTOCLinks,"logged-in"); ?> /> Logged-in &mdash; TOC links are active only when a user is logged in</label>
                                        <br />
                                        <label><input name="pp-embed-toc-links" id="pp-embed_toc-links-disabled" type="radio" value="disabled" <?php checked($sTOCLinks,"disabled"); ?>  /> Disabled &mdash; TOC links are completely disabled, all the time</label>                                               
                                        </td>
                                    </tr>
                                    <tr>
                                        <th valign="top" class="label" scope="row"></th>
                                        <td class="field">
                                            <input type="checkbox" aria-required="true" value="1" name="pp-embed-display-image" id="pp-embed-display-image" checked="checked" /> <span style="margin-right: 8px;"><label for="pp-embed-display-image"> <?php _e('Display cover image', 'wiki-embed'); ?></label></span>
                                            <input type="checkbox" aria-required="true" value="1" name="pp-embed-display-title" id="pp-embed-display-title" checked="checked" /> <span style="margin-right: 8px;"><label for="pp-embed-display-title"> <?php _e('Display title', 'wiki-embed'); ?></label></span>                                            
                                            <input type="checkbox" aria-required="true" value="1" name="pp-embed-display-subtitle" id="pp-embed-display-subtitle" checked="checked" /> <span><label for="pp-embed-display-subtitle"> <?php _e('Display subtitle', 'wiki-embed'); ?></label></span>
                                            <div></div>
                                            <input type="checkbox" aria-required="true" value="1" name="pp-embed-display-editor" id="pp-embed-display-editor" checked="checked" /> <span  style="margin-right: 8px;"><label for="pp-embed-display-editor"> <?php _e('Display editor', 'wiki-embed'); ?></label></span>
                                            <input type="checkbox" aria-required="true" value="1" name="pp-embed-display-language" id="pp-embed-display-language" checked="checked" /> <span style="margin-right: 8px;"><label for="pp-embed-display-language"> <?php _e('Display language', 'wiki-embed'); ?></label></span>
                                            <input type="checkbox" aria-required="true" value="1" name="pp-embed-display-atc" id="pp-embed-display-atc" checked="checked" /> <span ><label for="pp-embed-display-atc"> <?php _e('Display Add-to-Cart button', 'wiki-embed'); ?></label></span>                                        
                                            <div></div>
                                            <input type="checkbox" aria-required="true" value="1" name="pp-embed-display-book-size" id="pp-embed-display-book-size" checked="checked" /> <span  style="margin-right: 8px;"><label for="pp-embed-display-book-size"> <?php _e('Display book size', 'wiki-embed'); ?></label></span>
                                        </td>                                        
                                    </tr>
                                    <tr>
                                        <th valign="top" class="label" scope="row"></th>
                                        <td class="field">
                                            <h3><?php _e('CTA Button Settings', 'wiki-embed'); ?></h3>
                                            <label for="ppe-cta-button-content">Popup Content (shortcode/text/HTML)</label><br />
                                            <?php
                                                $sPPDownloadButtonContent = empty($wikiembed_options['ppe-cta-button-content'])? '' : $wikiembed_options['ppe-cta-button-content'];
                                                $sPPDownloadButtonContent = esc_textarea($sPPDownloadButtonContent);
                                            ?>                                              
                                            <textarea id="ppe-cta-button-content"><?php echo $sPPDownloadButtonContent ?></textarea>
                                        </td>
                                    </tr>                                    
                                    
                                    <tr>
                                        <th valign="top" class="label" scope="row"></th>
                                        <td class="field">
                                            <label for="ppe-cta-button-text"><?php _e('Button Text', 'wiki-embed'); ?></label><br />
                                            <?php
                                                $sPPDownloadButtonText = empty($wikiembed_options['ppe-cta-button-text'])? PPE_CTA_BUTTON_TEXT : $wikiembed_options['ppe-cta-button-text'];
                                                $sPPDownloadButtonText = esc_attr($sPPDownloadButtonText);
                                            ?>                                              
                                            <input type="text" id="ppe-cta-button-text" value="<?php echo $sPPDownloadButtonText ?>" />
                                        </td>
                                    </tr>
                                <tr style="line-height: normal;">
                                    <th valign="top" style="padding: 0;" class="label" scope="row"></th>
                                    <td class="field" style="padding: 0 10px;">
                                        <?php 
                                            $sPPDownloadButtonWidth = ( isset( $wikiembed_options['ppe-cta-button-width'] ) ) ? $wikiembed_options['ppe-cta-button-width'] : PPE_CTA_BUTTON_WIDTH;
                                        ?>
                                        <span style="width: 150px;display: inline-block;"><?php _e('Width', 'wiki-embed'); ?>:</span> <input type="text" id="ppe-cta-button-width" value="<?php echo $sPPDownloadButtonWidth ?>"  style="width: 50px;"/> pixels
                                        <p style="margin-top: 0;text-align: center;">(max: 500) or <em>auto</em> for normal auto sizing</p>                                  
                                    </td>
                                </tr>                                
                                <tr>
                                    <th valign="top" style="padding: 0;" class="label" scope="row"></th>
                                    <td class="field" style="padding: 0 10px;">
                                        <?php 
                                            $bgTopColor = ( isset( $wikiembed_options['ppe-cta-button-top-color'] ) ) ? $wikiembed_options['ppe-cta-button-top-color'] : PPE_CTA_BUTTON_TOP_COLOR;
                                        ?>
                                        <span style="width: 150px;display: inline-block;"><?php _e('Top Color', 'wiki-embed'); ?>:</span> <input type="text" id="ppe-cta-button-top-color" class="bgTopColor rdp-we-color-picker" data-default-color="<?php echo PPE_CTA_BUTTON_TOP_COLOR  ?>" value="<?php echo $bgTopColor ?>" />                                        
                                    </td>
                                </tr>
                                <tr>
                                    <th valign="top" style="padding: 0;" class="label" scope="row"></th>
                                    <td class="field" style="padding: 0 10px;">
                                        <?php 
                                            $bgBottomColor = ( isset( $wikiembed_options['ppe-cta-button-bottom-color'] ) ) ? $wikiembed_options['ppe-cta-button-bottom-color'] : PPE_CTA_BUTTON_BOTTOM_COLOR;
                                        ?>
                                        <span style="width: 150px;display: inline-block;"><?php _e('Bottom Color', 'wiki-embed'); ?>:</span> <input type="text" id="ppe-cta-button-bottom-color" class="bgBottomColor rdp-we-color-picker" data-default-color="<?php echo PPE_CTA_BUTTON_BOTTOM_COLOR  ?>" value="<?php echo $bgBottomColor ?>" />                                        
                                    </td>
                                </tr>
                                <tr>
                                    <th valign="top" style="padding: 0;" class="label" scope="row"></th>
                                    <td class="field" style="padding: 0 10px;">
                                        <?php 
                                            $fontColor = ( isset( $wikiembed_options['ppe-cta-button-font-color'] ) ) ? $wikiembed_options['ppe-cta-button-font-color'] : PPE_CTA_BUTTON_FONT_COLOR;
                                        ?>
                                        <span style="width: 150px;display: inline-block;"><?php _e('Font Color', 'wiki-embed'); ?>:</span> <input type="text" id="ppe-cta-button-font-color" class="fontColor rdp-we-color-picker" data-default-color="<?php echo PPE_CTA_BUTTON_FONT_COLOR  ?>" value="<?php echo $fontColor ?>" />                                        
                                    </td>
                                </tr> 
                                <tr>
                                    <th valign="top" style="padding: 0;" class="label" scope="row"></th>
                                    <td class="field" style="padding: 0 10px;">
                                        <?php 
                                            $fontHoverColor = ( isset( $wikiembed_options['ppe-cta-button-font-hover-color'] ) ) ? $wikiembed_options['ppe-cta-button-font-hover-color'] : PPE_CTA_BUTTON_FONT_HOVER_COLOR;
                                        ?>
                                        <span style="width: 150px;display: inline-block;"><?php _e('Font Hover Color', 'wiki-embed'); ?>:</span> <input type="text" id="ppe-cta-button-font-hover-color" class="fontHoverColor rdp-we-color-picker" data-default-color="<?php echo PPE_CTA_BUTTON_FONT_HOVER_COLOR  ?>" value="<?php echo $fontHoverColor ?>" />                                        
                                    </td>
                                </tr>  
                                <tr>
                                    <th valign="top" style="padding: 0;" class="label" scope="row"></th>
                                    <td class="field" style="padding: 0 10px;">
                                        <?php 
                                            $borderColor = ( isset( $wikiembed_options['ppe-cta-button-border-color'] ) ) ? $wikiembed_options['ppe-cta-button-border-color'] : PPE_CTA_BUTTON_BORDER_COLOR;
                                        ?>
                                        <span style="width: 150px;display: inline-block;"><?php _e('Border Color', 'wiki-embed'); ?>:</span> <input type="text" id="ppe-cta-button-border-color" class="borderColor rdp-we-color-picker" data-default-color="<?php echo PPE_CTA_BUTTON_BORDER_COLOR  ?>" value="<?php echo $borderColor ?>" />                                        
                                    </td>
                                </tr> 
                                <tr>
                                    <th valign="top" style="padding: 0;" class="label" scope="row"></th>
                                    <td class="field" style="padding: 0 10px;">
                                        <?php 
                                            $boxShadowColor = ( isset( $wikiembed_options['ppe-cta-button-box-shadow-color'] ) ) ? $wikiembed_options['ppe-cta-button-box-shadow-color'] : PPE_CTA_BUTTON_BOX_SHADOW_COLOR;
                                        ?>
                                        <span style="width: 150px;display: inline-block;"><?php _e('Button Shadow Color', 'wiki-embed'); ?>:</span> <input type="text" id="ppe-cta-button-box-shadow-color" class="boxShadowColor rdp-we-color-picker" data-default-color="<?php echo PPE_CTA_BUTTON_BOX_SHADOW_COLOR  ?>" value="<?php echo $boxShadowColor ?>" />                                        
                                    </td>
                                </tr> 
                                <tr>
                                    <th valign="top" style="padding: 0;" class="label" scope="row"></th>
                                    <td class="field" style="padding: 0 10px;">
                                        <?php 
                                            $textShadowColor = ( isset( $wikiembed_options['ppe-cta-button-text-shadow-color'] ) ) ? $wikiembed_options['ppe-cta-button-text-shadow-color'] : PPE_CTA_BUTTON_TEXT_SHADOW_COLOR;
                                        ?>
                                        <span style="width: 150px;display: inline-block;"><?php _e('Text Shadow Color', 'wiki-embed'); ?>:</span> <input type="text" id="ppe-cta-button-text-shadow-color" class="textShadowColor rdp-we-color-picker" data-default-color="<?php echo PPE_CTA_BUTTON_TEXT_SHADOW_COLOR  ?>" value="<?php echo $textShadowColor ?>" />                                        
                                    </td>
                                </tr>                                     
                                    <tr>
                                        <td></td>
                                        <td><br />
                                                <input type="button" value="Insert into Post/ Page" onclick="pp_embed_send_to_editor();" id="btnPPEmbed" class="button">
                                        </td>
                                    </tr>                                    
                                </tbody>
                            </table>
                        </div>
                    </div> 
                        
                        
                    <div id="tabs-3" class="pp_embed_form_wrap">
                        <div class="media-item media-blank">
                            <table class="describe">
                                <tbody> 
                                    <tr>
                                        <th valign="top" class="label" scope="row" style="width: 200px;">
                                                <span class="alignleft"><label for="pp-gallery-col"><?php _e('Number of Columns', 'wiki-embed'); ?></label></span>
                                                <span class="alignright"><abbr class="required" title="required" id="status_img">*</abbr></span>
                                        </th>
                                        <td class="field"><input type="text" aria-required="true" value="2" name="pp-gallery-col" id="pp-gallery-col" style="width: 20px;"></td>
                                    </tr>                                    
                                    <tr>
                                        <th valign="top" class="label" scope="row" style="width: 200px;">
                                                <span class="alignleft"><label for="pp-gallery-num"><?php _e('Number of Results per Page', 'wiki-embed'); ?></label></span>
                                                <span class="alignright"><abbr class="required" title="required" id="status_img">*</abbr></span>
                                        </th>
                                        <td class="field"><input type="text" aria-required="true" value="10" name="pp-gallery-num" id="pp-gallery-num" style="width: 30px;"></td>
                                    </tr>
                                    <tr>
                                        <th valign="top" class="label" scope="row" style="width: 200px;">
                                                <span class="alignleft"><label for="pp-gallery-size"><?php _e('Gallery Style', 'wiki-embed'); ?></label></span>
                                        </th>
                                        <td class="field">
                                            <select id="pp-gallery-size">
                                                <option selected="selected" value="small">Small</option>
                                                <option value="medium">Medium</option>
                                                <option value="large">Large</option>
                                            </select>
                                        </td>
                                    </tr>                                    
                                    <tr>
                                        <th valign="top" class="label" scope="row" style="width: 200px;">
                                                <span class="alignleft"><label for="pp-gallery-categories"><?php _e('Target Categories', 'wiki-embed'); ?></label></span>
                                                <span class="alignright"><abbr class="required" title="required" id="status_img"></abbr></span>
                                        </th>
                                        <td class="field">
                                            <div id="pp-gallery-categories" class="container">
                                                <?php 
                                                $args = array(
                                                  'orderby' => 'name',
                                                  'hide_empty' => 0
                                                  );
                                                $categories = get_categories( $args );  
                                                    foreach ( $categories as $category ) {
                                                            echo '<input class="pp-gallery-category" name="pp-gallery-category" value="'. $category->term_id . '" type="checkbox" />' . $category->name . '<br/>';
                                                    }
                                                ?>
                                            </div>
                                        </td>
                                    </tr> 
                                    <tr>
                                        <th valign="top" class="label" scope="row" style="width: 200px;">
                                                <span class="alignleft"><label for="pp-gallery-tags"><?php _e('Target Tags', 'wiki-embed'); ?></label></span>
                                                <span class="alignright"><abbr class="required" title="required" id="status_img"></abbr></span>
                                        </th>
                                        <td class="field">
                                            <div id="pp-gallery-tags" class="container">
                                                <?php 
                                                $args = array(
                                                  'orderby' => 'name',
                                                  'hide_empty' => 0
                                                  );
                                                $tags = get_tags( $args );  
                                                    foreach ( $tags as $tag ) {
                                                            echo '<input class="pp-gallery-tag" name="pp-gallery-tag" value="'. $tag->term_id . '" type="checkbox" />' . $tag->name . '<br/>';
                                                    }
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th valign="top" class="label" scope="row" style="width: 200px;">
                                                <span class="alignleft"><label for="pp-gallery-sort-col"><?php _e('Sort Field', 'wiki-embed'); ?></label></span>
                                        </th>
                                        <td class="field">
                                            <input type="radio" name="pp-gallery-sort-col" checked="checked" value="post_title">Post Title &nbsp;&nbsp;&nbsp;<input type="radio" name="pp-gallery-sort-col" value="post_date">Post Date
                                        </td>
                                    </tr> 
                                    <tr>
                                        <th valign="top" class="label" scope="row" style="width: 200px;">
                                                <span class="alignleft"><label for="pp-gallery-sort-dir"><?php _e('Sort Order', 'wiki-embed'); ?></label></span>
                                        </th>
                                        <td class="field">
                                            <input type="radio" name="pp-gallery-sort-dir" checked="checked" value="ASC">Ascending &nbsp;&nbsp;&nbsp;<input type="radio" name="pp-gallery-sort-dir" value="DESC">Descending
                                        </td>
                                    </tr> 
                                    <tr>
                                        <th valign="top" class="label" scope="row"></th>
                                        <td class="field">
                                            <h3><?php _e('CTA Button Settings', 'wiki-embed'); ?></h3>
                                            <label for="ppegallery-cta-button-content"><?php _e('Popup Content (shortcode/text/HTML)', 'wiki-embed'); ?></label><br />
                                            <?php
                                                $sPPDownloadButtonContent = empty($wikiembed_options['ppe-cta-button-content'])? '' : $wikiembed_options['ppe-cta-button-content'];
                                                $sPPDownloadButtonContent = esc_textarea($sPPDownloadButtonContent);
                                            ?>                                              
                                            <textarea id="ppegallery-cta-button-content"><?php echo $sPPDownloadButtonContent ?></textarea>
                                        </td>
                                    </tr>                                    
                                    <tr>
                                        <th valign="top" class="label" scope="row" style="width: 200px;"></th>
                                        <td class="field">
                                            <label for="ppegallery-cta-button-text"><?php _e('CTA Button Text', 'wiki-embed'); ?></label>
                                            <?php
                                                $sPPDownloadButtonText = empty($wikiembed_options['ppe-cta-button-text'])? PPE_CTA_BUTTON_TEXT : $wikiembed_options['ppe-cta-button-text'];
                                                $sPPDownloadButtonText = esc_attr($sPPDownloadButtonText);
                                            ?>                                              
                                            <input type="text" id="ppegallery-cta-button-text" value="<?php echo $sPPDownloadButtonText ?>" />
                                        </td>
                                    </tr>
                                <tr>
                                    <th valign="top" style="padding: 0;" class="label" scope="row"></th>
                                    <td class="field" style="padding: 0 10px;">
                                        <?php 
                                            $bgTopColor = ( isset( $wikiembed_options['ppe-cta-button-top-color'] ) ) ? $wikiembed_options['ppe-cta-button-top-color'] : PPE_CTA_BUTTON_TOP_COLOR;
                                        ?>
                                        <span style="width: 150px;display: inline-block;"><?php _e('Top Color', 'wiki-embed'); ?>:</span> <input type="text" id="ppegallery-cta-button-top-color" class="bgTopColor rdp-we-color-picker" data-default-color="<?php echo PPE_CTA_BUTTON_TOP_COLOR  ?>" value="<?php echo $bgTopColor ?>" />                                        
                                    </td>
                                </tr>
                                <tr>
                                    <th valign="top" style="padding: 0;" class="label" scope="row"></th>
                                    <td class="field" style="padding: 0 10px;">
                                        <?php 
                                            $bgBottomColor = ( isset( $wikiembed_options['ppe-cta-button-bottom-color'] ) ) ? $wikiembed_options['ppe-cta-button-bottom-color'] : PPE_CTA_BUTTON_BOTTOM_COLOR;
                                        ?>
                                        <span style="width: 150px;display: inline-block;"><?php _e('Bottom Color', 'wiki-embed'); ?>:</span> <input type="text" id="ppegallery-cta-button-bottom-color" class="bgBottomColor rdp-we-color-picker" data-default-color="<?php echo PPE_CTA_BUTTON_BOTTOM_COLOR  ?>" value="<?php echo $bgBottomColor ?>" />                                        
                                    </td>
                                </tr>
                                <tr>
                                    <th valign="top" style="padding: 0;" class="label" scope="row"></th>
                                    <td class="field" style="padding: 0 10px;">
                                        <?php 
                                            $fontColor = ( isset( $wikiembed_options['ppe-cta-button-font-color'] ) ) ? $wikiembed_options['ppe-cta-button-font-color'] : PPE_CTA_BUTTON_FONT_COLOR;
                                        ?>
                                        <span style="width: 150px;display: inline-block;"><?php _e('Font Color', 'wiki-embed'); ?>:</span> <input type="text" id="ppegallery-cta-button-font-color" class="fontColor rdp-we-color-picker" data-default-color="<?php echo PPE_CTA_BUTTON_FONT_COLOR  ?>" value="<?php echo $fontColor ?>" />                                        
                                    </td>
                                </tr> 
                                <tr>
                                    <th valign="top" style="padding: 0;" class="label" scope="row"></th>
                                    <td class="field" style="padding: 0 10px;">
                                        <?php 
                                            $fontHoverColor = ( isset( $wikiembed_options['ppe-cta-button-font-hover-color'] ) ) ? $wikiembed_options['ppe-cta-button-font-hover-color'] : PPE_CTA_BUTTON_FONT_HOVER_COLOR;
                                        ?>
                                        <span style="width: 150px;display: inline-block;"><?php _e('Font Hover Color', 'wiki-embed'); ?>:</span> <input type="text" id="ppegallery-cta-button-font-hover-color" class="fontHoverColor rdp-we-color-picker" data-default-color="<?php echo PPE_CTA_BUTTON_FONT_HOVER_COLOR  ?>" value="<?php echo $fontHoverColor ?>" />                                        
                                    </td>
                                </tr>  
                                <tr>
                                    <th valign="top" style="padding: 0;" class="label" scope="row"></th>
                                    <td class="field" style="padding: 0 10px;">
                                        <?php 
                                            $borderColor = ( isset( $wikiembed_options['ppe-cta-button-border-color'] ) ) ? $wikiembed_options['ppe-cta-button-border-color'] : PPE_CTA_BUTTON_BORDER_COLOR;
                                        ?>
                                        <span style="width: 150px;display: inline-block;"><?php _e('Border Color', 'wiki-embed'); ?>:</span> <input type="text" id="ppegallery-cta-button-border-color" class="borderColor rdp-we-color-picker" data-default-color="<?php echo PPE_CTA_BUTTON_BORDER_COLOR  ?>" value="<?php echo $borderColor ?>" />                                        
                                    </td>
                                </tr> 
                                <tr>
                                    <th valign="top" style="padding: 0;" class="label" scope="row"></th>
                                    <td class="field" style="padding: 0 10px;">
                                        <?php 
                                            $boxShadowColor = ( isset( $wikiembed_options['ppe-cta-button-box-shadow-color'] ) ) ? $wikiembed_options['ppe-cta-button-box-shadow-color'] : PPE_CTA_BUTTON_BOX_SHADOW_COLOR;
                                        ?>
                                        <span style="width: 150px;display: inline-block;"><?php _e('Button Shadow Color', 'wiki-embed'); ?>:</span> <input type="text" id="ppegallery-cta-button-box-shadow-color" class="boxShadowColor rdp-we-color-picker" data-default-color="<?php echo PPE_CTA_BUTTON_BOX_SHADOW_COLOR  ?>" value="<?php echo $boxShadowColor ?>" />                                        
                                    </td>
                                </tr> 
                                <tr>
                                    <th valign="top" style="padding: 0;" class="label" scope="row"></th>
                                    <td class="field" style="padding: 0 10px;">
                                        <?php 
                                            $textShadowColor = ( isset( $wikiembed_options['ppe-cta-button-text-shadow-color'] ) ) ? $wikiembed_options['ppe-cta-button-text-shadow-color'] : PPE_CTA_BUTTON_TEXT_SHADOW_COLOR;
                                        ?>
                                        <span style="width: 150px;display: inline-block;"><?php _e('Text Shadow Color', 'wiki-embed'); ?>:</span> <input type="text" id="ppegallery-cta-button-text-shadow-color" class="textShadowColor rdp-we-color-picker" data-default-color="<?php echo PPE_CTA_BUTTON_TEXT_SHADOW_COLOR  ?>" value="<?php echo $textShadowColor ?>" />                                        
                                    </td>
                                </tr>                                     
                                    <tr>
                                        <td></td>
                                        <td><br />
                                                <input type="button" value="Insert into Post/ Page" onclick="pp_gallery_send_to_editor();" id="btnPPGallery" class="button">
                                        </td>
                                    </tr>
                                  
                                </tbody>
                            </table>
                        </div>
                    </div>                     
                    </div><!-- wiki-embed-tabs -->
		</div><!-- end #wiki_embed_form -->
		<?php

}
