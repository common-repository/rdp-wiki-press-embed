 $j=jQuery.noConflict();
// Use jQuery via $j(...)

$j(document).ready(rdp_wpe_toc_popup_onReady);

function rdp_wpe_toc_popup_onReady(){
    oRDPWPEContainer = $j('div.wiki-embed');
    if(!oRDPWPEContainer.length)return;  
    oTOC = $j('div.wpe-toc-link-container');
    if(!oTOC.length){
       oRDPWPEContainer.first().before('<div class="wpe-toc-link-container"><a href="#rdp_wpe_toc_inline_content" class="wpe-toc-link">Show Book Table of Contents</a></div>' );
    }
    $j(".wpe-toc-link").colorbox(
                                {returnFocus:false,
                                inline:true, 
                                innerWidth: 960, 
                                innerHeight:"80%",
                                transition:"none"
                                });  
                                
    $j(document).bind('cbox_cleanup', function(){
      $j("#rdp_wpe_toc_inline_content_wrapper").html($j("#cboxLoadedContent").html());
    });                                
}//rdp_wpe_toc_popup_onReady


