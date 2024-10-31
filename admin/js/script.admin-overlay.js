var $j=jQuery.noConflict();
// Use jQuery via $j(...)

$j(document).ready(rdp_we_admin_overlay_onReady);

function rdp_we_admin_overlay_onReady(){
    $j( "#wiki-embed-tabs" ).tabs();
}//rdp_we_admin_onReady


function wiki_embed_insert_overlay_form(){
        var wikiEmbedUrl        = jQuery("#wiki-embed-src").attr('value');
        var wikiEmbedSource 	= ( jQuery("#wiki-embed-display-links").attr('checked') ? jQuery("#wiki-embed-display-links").attr('value') : "" );
        var wikiEmbedOverlay 	= ( jQuery("#wiki-embed-overlay").attr('checked')       ? jQuery("#wiki-embed-overlay").attr('value')       : "" );
        var wikiEmbedTabs 	= ( jQuery("input:radio[name=wiki-embed-tabs]:checked") ? jQuery("input:radio[name=wiki-embed-tabs]:checked").attr('value') : "" );
        var wikiEmbedNoEdit 	= ( jQuery("#wiki-embed-edit").attr('checked')          ? jQuery("#wiki-embed-edit").attr('value')          : "" );
        var wikiEmbedNoContents = ( jQuery("#wiki-embed-contents").attr('checked')      ? jQuery("#wiki-embed-contents").attr('value')      : "" );
        var wikiEmbedNoInfobox  = ( jQuery("#wiki-embed-infobox").attr('checked')       ? jQuery("#wiki-embed-infobox").attr('value')       : "" );
        var win = parent;

        win.send_to_editor( "[wiki-embed url='"+wikiEmbedUrl+"' "+ wikiEmbedSource + wikiEmbedOverlay + wikiEmbedTabs + wikiEmbedNoEdit + wikiEmbedNoContents + wikiEmbedNoInfobox +" ]" );
}

function pp_embed_send_to_editor(){
    var ppEmbedURL = jQuery("#pp-embed-src").val();
    var ppTOCShow =  ( jQuery("#pp-embed-display-toc").attr('checked') ? '1': "0" );
    var ppTOCLinks = '';
    if(ppTOCShow == '1'){
        var sTOCLinks = jQuery("input:radio[name=pp-embed-toc-links]:checked").val();
        ppTOCLinks = " toc_links='"+ sTOCLinks +"'";
    }
    var sCode = "[wiki-embed url='"+ppEmbedURL+"' toc_show='"+ ppTOCShow +"'"+ ppTOCLinks;
    
    var ppImageShow = ( jQuery("#pp-embed-display-image").attr('checked') ? '1': "0" );
    if(ppImageShow == 0)sCode += " image_show='0'";    
    
    var ppTitleShow = ( jQuery("#pp-embed-display-title").attr('checked') ? '1': "0" );
    if(ppTitleShow == 0)sCode += " title_show='0'";
    
    var ppSubtitleShow = ( jQuery("#pp-embed-display-subtitle").attr('checked') ? '1': "0" );
    if(ppSubtitleShow == 0)sCode += " subtitle_show='0'";
    
    var ppEditorShow = ( jQuery("#pp-embed-display-editor").attr('checked') ? '1': "0" );
    if(ppEditorShow == 0)sCode += " editor_show='0'";
    
    var ppLanguageShow = ( jQuery("#pp-embed-display-language").attr('checked') ? '1': "0" );
    if(ppLanguageShow == 0)sCode += " language_show='0'";
    
    var ppATCShow = ( jQuery("#pp-embed-display-atc").attr('checked') ? '1': "0" );
    if(ppATCShow == 0)sCode += " add_to_cart_show='0'";
    
    var ppBookSizeShow = ( jQuery("#pp-embed-display-book-size").attr('checked') ? '1': "0" );
    if(ppBookSizeShow == 0)sCode += " book_size_show='0'";    
   
    var ppButtonText = "";
    var ppButtonWidth = "";
    var ppButtonTopColor = "";
    var ppButtonBottomColor = "";
    var ppButtonFontColor = "";
    var ppButtonFontHoverColor = "";
    var ppButtonBorderColor = "";
    var ppButtonBoxShadowColor = "";
    var ppButtonTextShadowColor = "";
    var ppButtonContent = jQuery("#ppe-cta-button-content").val();
    
    if(ppButtonContent){
        if(jQuery("#ppe-cta-button-text").val() != rdp_we_admin.pp_button_text)ppButtonText = " cta_button_text='"+ jQuery("#ppe-cta-button-text").val() +"'";
        if(jQuery("#ppe-cta-button-width").val() != rdp_we_admin.pp_button_width)ppButtonWidth = " cta_button_width='"+ jQuery("#ppe-cta-button-width").val() +"'";
        if(jQuery("#ppe-cta-button-top-color").val() != rdp_we_admin.pp_button_top_color)ppButtonTopColor = " cta_button_top_color='"+ jQuery("#ppe-cta-button-top-color").val() +"'";
        if(jQuery("#ppe-cta-button-bottom-color").val() != rdp_we_admin.pp_button_bottom_color)ppButtonBottomColor = " cta_button_bottom_color='"+ jQuery("#ppe-cta-button-bottom-color").val() +"'";
        if(jQuery("#ppe-cta-button-font-color").val() != rdp_we_admin.pp_button_font_color)ppButtonFontColor = " cta_button_font_color='"+ jQuery("#ppe-cta-button-download-font-color").val() +"'";
        if(jQuery("#ppe-cta-button-font-hover-color").val() != rdp_we_admin.pp_button_font_hover_color)ppButtonFontHoverColor = " cta_button_font_hover_color='"+ jQuery("#ppe-cta-button-font-hover-color").val() +"'";
        if(jQuery("#ppe-cta-button-border-color").val() != rdp_we_admin.pp_button_border_color)ppButtonBorderColor = " cta_button_border_color='"+ jQuery("#ppe-cta-button-border-color").val() +"'";
        if(jQuery("#ppe-cta-button-box-shadow-color").val() != rdp_we_admin.pp_button_box_shadow_color)ppButtonBoxShadowColor = " cta_button_box_shadow_color='"+ jQuery("#ppe-cta-button-box-shadow-color").val() +"'";
        if(jQuery("#ppe-cta-button-text-shadow-color").val() != rdp_we_admin.pp_button_text_shadow_color)ppButtonTextShadowColor = " cta_button_text_shadow_color='"+ jQuery("#ppe-cta-button-text-shadow-color").val() +"'";
        sCode += ppButtonText + ppButtonWidth + ppButtonTopColor + ppButtonFontColor + ppButtonFontHoverColor + ppButtonBorderColor + ppButtonBottomColor + ppButtonBoxShadowColor + ppButtonTextShadowColor;
    }

    if(ppButtonContent){
        sCode += "]" + ppButtonContent + "[/wiki-embed]"
    }else{
       sCode += " /]"; 
    }    
    
    var win = parent;    
    win.send_to_editor( sCode );
}//pp_embed_send_to_editor


function pp_gallery_send_to_editor(){
    var ppGalleryCol = jQuery("#pp-gallery-col");
    if(!rdp_we_admin_chk_blank(ppGalleryCol,"Please enter number of columns")){return false;}    
    if(!rdp_we_admin_chk_numric(ppGalleryCol,"The number of columns should be numeric")) {return false;}

    var ppGalleryNum = jQuery("#pp-gallery-num");
    if(!rdp_we_admin_chk_blank(ppGalleryNum,"Please enter number of results to fetch")){return false;}    
    if(!rdp_we_admin_chk_numric(ppGalleryNum,"The number of results should be numeric")) {return false;}   

    var ppGalleryCats = '';
    jQuery(".pp-gallery-category:checked").each(function( index ) {
        if(ppGalleryCats.length != 0)ppGalleryCats += ',';
        ppGalleryCats += $j( this ).val();
      });

    var ppGalleryTags = '';
    jQuery(".pp-gallery-tag:checked").each(function( index ) {
        if(ppGalleryTags.length != 0)ppGalleryTags += ',';
        ppGalleryTags += $j( this ).val();
      });

      var ppGallerySize = jQuery("#pp-gallery-size");

    var sCode = "[wiki-embed-ppgallery col='"+ppGalleryCol.val()+"' num='"+ppGalleryNum.val()+"' size='"+ppGallerySize.val()+"'";
    if(ppGalleryCats.length != 0)sCode += " cat='"+ppGalleryCats+"'";
    if(ppGalleryTags.length != 0)sCode += " tag='"+ppGalleryTags+"'";
    
    
    var sGallerySort = jQuery("input:radio[name=pp-gallery-sort-col]:checked").val();
    if(sGallerySort != 'post_title')sCode += " sort_col='"+ sGallerySort +"'";    
    
    var sGalleryAttr = jQuery("input:radio[name=pp-gallery-sort-dir]:checked").val();
    if(sGalleryAttr != 'ASC')sCode += " sort_dir='"+ sGalleryAttr +"'";

    var ppButtonText = "";
    var ppButtonTopColor = "";
    var ppButtonBottomColor = "";
    var ppButtonFontColor = "";
    var ppButtonFontHoverColor = "";
    var ppButtonBorderColor = "";
    var ppButtonBoxShadowColor = "";
    var ppButtonTextShadowColor = "";
    var ppButtonContent = jQuery("#ppegallery-cta-button-content").val();

    if(ppButtonContent){
        if(jQuery("#ppegallery-cta-button-text").val() != rdp_we_admin.pp_button_text)ppButtonText = " cta_button_text='"+ jQuery("#ppegallery-cta-button-text").val() +"'";
        if(jQuery("#ppegallery-cta-button-top-color").val() != rdp_we_admin.pp_button_top_color)ppButtonTopColor = " cta_button_top_color='"+ jQuery("#ppegallery-cta-button-top-color").val() +"'";
        if(jQuery("#ppegallery-cta-button-bottom-color").val() != rdp_we_admin.pp_button_bottom_color)ppButtonBottomColor = " cta_button_bottom_color='"+ jQuery("#ppegallery-cta-button-bottom-color").val() +"'";
        if(jQuery("#ppegallery-cta-button-font-color").val() != rdp_we_admin.pp_button_font_color)ppButtonFontColor = " cta_button_font_color='"+ jQuery("#ppegallery-cta-button-download-font-color").val() +"'";
        if(jQuery("#ppegallery-cta-button-font-hover-color").val() != rdp_we_admin.pp_button_font_hover_color)ppButtonFontHoverColor = " cta_button_font_hover_color='"+ jQuery("#ppegallery-cta-button-font-hover-color").val() +"'";
        if(jQuery("#ppegallery-cta-button-border-color").val() != rdp_we_admin.pp_button_border_color)ppButtonBorderColor = " cta_button_border_color='"+ jQuery("#ppegallery-cta-button-border-color").val() +"'";
        if(jQuery("#ppegallery-cta-button-box-shadow-color").val() != rdp_we_admin.pp_button_box_shadow_color)ppButtonBoxShadowColor = " cta_button_box_shadow_color='"+ jQuery("#ppegallery-cta-button-box-shadow-color").val() +"'";
        if(jQuery("#ppegallery-cta-button-text-shadow-color").val() != rdp_we_admin.pp_button_text_shadow_color)ppButtonTextShadowColor = " cta_button_text_shadow_color='"+ jQuery("#ppegallery-cta-button-text-shadow-color").val() +"'";
        sCode += ppButtonText + ppButtonTopColor + ppButtonFontColor + ppButtonFontHoverColor + ppButtonBorderColor + ppButtonBottomColor + ppButtonBoxShadowColor + ppButtonTextShadowColor;
    }      
      
    if(ppButtonContent){
        sCode += "]" + ppButtonContent + "[/wiki-embed-ppgallery]"
    }else{
       sCode += " /]"; 
    }  
    var win = parent;    
    win.send_to_editor( sCode );
}//pp_gallery_send_to_editor

function rdp_we_admin_chk_blank(ctl,msg)
{
    if(typeof msg == 'undefined' || msg=="")
     {
      msg="This field cannot be blank";
     }
    if (ctl.val()=="")
     {
            alert(msg);
            ctl.val("");
            ctl.focus();
            return (false);
     }
    else
     return (true);
}  

function rdp_we_admin_chk_numric(ctl,msg)
 {
 	if(typeof msg == 'undefined' || msg=="")
	 {
	  msg="Please enter valid numeric data";
	 }

	if (isNaN(ctl.val()))
	 {
		alert(msg);
		ctl.val("");
		ctl.focus();
		return (false);
	 }
	else
	 return (true);
 }