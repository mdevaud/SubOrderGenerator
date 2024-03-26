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


$( "a.suborder-send-mail" ).click( function(ev) {
    ev.preventDefault();
    var $this = $(this);
    var $url = $this.attr("href");
    $.ajax({
        url: $url,
        type: 'POST',
    }).done(function(data, textStatus, jqXHR){
        $(".suborder-toaster").html("<div class='alert alert-info'> Mail sent</div>").show().fadeOut(1200);
    }).fail(function(jqXHR, textStatus, errorThrown){
        $(".suborder-toaster").html("<div class='alert alert-danger'> Mail failed </div>").show().fadeOut(1200);
    });
});