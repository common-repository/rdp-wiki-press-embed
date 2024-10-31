var $j=jQuery.noConflict();
// Use jQuery via $j(...)

$j(document).ready(rdp_wcr_main_onReady);
function rdp_wcr_main_onReady(){
    $j('#mainContent .ready').removeClass('invisible');
    rdp_wcr_handle_links();
}//rdp_wcr_main_onReady

function rdp_wcr_handle_links(){
    var baseURL = window.location.protocol + "//" + window.location.host + window.location.pathname;
    var baseTarget = (typeof WikiEmbedSettings != 'undefined')? url('protocol', WikiEmbedSettings.target_url) + "//" + url('hostname', WikiEmbedSettings.target_url): '';
    var oDomains = rdp_wcr.domains.split(',');
    
    $j("a").each(function(i){
        var sHREF = $j(this).attr('href');        
         if(typeof sHREF == 'undefined')return true;        
        if(sHREF.substring(0, 1) == '#')return true;
        if(url('?wikiembed-override-url',sHREF))return true;
        if($j(this).hasClass('ppe-add-to-cart'))return true;
        if($j(this).hasClass('ppe-cover-link'))return true;  
        if($j(this).hasClass('image'))return true;  
        if($j(this).hasClass('rdp-wbb-go-to-wiki-page'))return true;         

        var n = -1;
        for (i = 0; i < oDomains.length; i++) { 
            n = sHREF.indexOf(oDomains[i]);
            if(n >= 0)break;
        }
        var p = 'pediapress.com'.indexOf(url('domain', sHREF));
        if(n <= 0 && p <= 0)return true;
        if(sHREF.substring(0, 2) == '//') sHREF = 'http:' + sHREF;
        if(baseTarget != '' && sHREF.substring(0, 1) == '/') sHREF = baseTarget + sHREF;        
        urls = sHREF.match(/(https?:\/\/[^\s]+)/g);
        if(urls == null)return true;
        $j(this).attr('href',baseURL+jQuery.query.set("wikiembed-override-url", sHREF) ).removeAttr('target');    
        $j(this).data("href",sHREF).addClass('wiki-link');
    });     
}//rdp_wcr_handle_links
