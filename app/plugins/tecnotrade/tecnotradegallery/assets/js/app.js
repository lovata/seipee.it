
document.addEventListener("DOMContentLoaded", function() {
    console.log("Il contenuto del documento è stato caricato.");
    
    lightGallery(document.getElementById('lightgallery'),{
        licenseKey: '0000-0000-000-0000',
        speed: 500
    });
    
    
    if($('#lightgallery').hasClass('justifiedGallery')){
        $(".justifiedGallery").justifiedGallery({
            rowHeight : 200,
            lastRow : 'nojustify',
            margins : 3
        })
    }
});

