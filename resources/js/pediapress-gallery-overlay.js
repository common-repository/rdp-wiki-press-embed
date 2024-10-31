 $j=jQuery.noConflict();
// Use jQuery via $j(...)

$j(document).ready(rdp_pp_gallery_overlay_onReady);

function rdp_pp_gallery_overlay_onReady(){
    $j('div.rdp_pp_book_gallery .ready').removeClass('invisible');

    if(typeof rdp_pp_gallery == 'undefined'){
        $j(".rdp-pp-gallery-add-to-cart").colorbox({iframe:true, innerWidth: 960, innerHeight:"80%",transition:"none"})
    }else{
        $j(".rdp-pp-gallery-add-to-cart").colorbox(
                                    {returnFocus:false,
                                    inline:true, 
                                    innerWidth: 960, 
                                    innerHeight:"80%",
                                    transition:"none",
                                    onOpen:function(){ 
                                        rdp_pp_gallery_update_tab_2(this);
                                    }
                                    }) ;                    


        $j(".rdp-pp-gallery-cta-button").colorbox(
                                    {returnFocus:false,
                                    inline:true, 
                                    innerWidth: 960, 
                                    innerHeight:"80%",
                                    transition:"none",
                                    onOpen:function(){ 
                                        $j( "#wiki-embed-tabs" ).tabs( "option", "active", 0 );
                                        rdp_pp_gallery_update_tab_2(this);
                                    }
                                    }) ;
        $j( "#wiki-embed-tabs" ).tabs(); 
        
        $j(document).bind('cbox_complete', function(){
            setTimeout(function(){
                $j( "#wiki-embed-tabs .ppe-tab" ).height($j( "#cboxLoadedContent" ).height() - ($j( "#wiki-embed-tabs .ui-tabs-nav" ).height()*3));
            },300);  
        }); 
        
        $j(document).bind('cbox_cleanup', function(){
          $j("#rdp_pp_gallery_inline_content_wrapper").html($j("#cboxLoadedContent").html());
          
        }); 
        
        $j(document).bind('cbox_closed', function(){
            setTimeout(function(){$j( "#wiki-embed-tabs" ).tabs();},300);
        });
        
        var txtSrc = $j( "input[id*='pp-src-']:first" );
        if(txtSrc){
            $j('#rdp_pp_gallery_inline_content #tab-2 iframe').attr('src',txtSrc.val());
            $j('#rdp_pp_gallery_inline_content #tab-2 iframe').load(function() {
                $j(this).show()
                $j('#rdp_pp_gallery_inline_content #rdp_pp_gallery_frame_loading').hide();
            });
        }
        
    }
}//rdp_pp_gallery_overlay_onReady


function rdp_pp_gallery_update_tab_2(ctl){
    var btnATC = $j('#rdp-pp-gallery-add-to-cart-'+$j(ctl).attr('postid')) ;
    if(btnATC)$j('#rdp_pp_gallery_inline_content #tab-2-link').text(btnATC.text());     
    var txtSrc = $j('#pp-src-'+$j(ctl).attr('postid')) ;
    if(txtSrc)$j('#rdp_pp_gallery_inline_content #tab-2 iframe').attr('src',txtSrc.val());
    jQuery(document).trigger('rdp_pp_gallery_colorbox_onOpen',[ctl]);
}//rdp_pp_gallery_update_tab_2