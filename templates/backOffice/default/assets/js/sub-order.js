$( "a.suborder-delete" ).click( function(ev) {
    ev.preventDefault();
    var $this = $(this);
    var $url = $this.attr("href");
    $.ajax({
        url: $url,
        type: 'DELETE',
    }).done(function(data, textStatus, jqXHR){
        location.reload()
    }).fail(function(jqXHR, textStatus, errorThrown){
        console.log('FAILED');
    });
});

$( "a.suborder-generate-link" ).click( function(ev) {
    ev.preventDefault();
    var $this = $(this);
    var $url = $this.attr("href");
    navigator.clipboard.writeText($url)
        .then(() => {
            $(".suborder-toaster").html("<div class='alert alert-info'> Copied</div>").show().fadeOut(1200);
        })
});

$( "a.suborder-send-mail" ).click( function(ev) {
console.log('TODO')
});