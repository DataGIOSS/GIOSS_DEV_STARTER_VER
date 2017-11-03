$(document).ready(function () {
    var id_registro = 0;

    function put_id(id){
        id_registro = id;
        localStorage.setItem("id_registro", id);
    }

    function get_url(id){

        console.log('Entra a get URL '+ document.getElementById('disable_url' + id).value + '   ' + id);

        id_registro = id;
        localStorage.setItem("id_registro", id);
        console.log("El id de registro es: " + id_registro);

        var url = document.URL;

        $( document ).ready(function() {
            document.getElementById('edit_url'+ id).value = url;
            document.getElementById('disable_url' + id).value = url;
        });

        
    }

    function open_modal(){
        
        var id = localStorage.getItem("id_registro");
        
        get_url(id);

        $('#edit_' + id).modal('show');
        $('#edit_' + id).addClass('in');
        $('#edit_' + id).css('background', 'linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4))');
    }


    if ($( "#error_alert" ).hasClass( "in" )) {
        open_modal();
    }

    function disable_user(id){

        if ($('#edit_status' + id).val() == 1) {
            $('#disable_user_btn' + id).removeClass('btn btn-danger');
            $('#disable_user_spn' + id).removeClass('glyphicon glyphicon-remove-sign');
            
            $('#disable_user_btn' + id).addClass('btn btn-success');
            $('#disable_user_spn' + id).addClass('glyphicon glyphicon-ok');

        } else {
            
            $('#disable_user_btn' + id).removeClass('btn btn-success');
            $('#disable_user_spn' + id).removeClass('glyphicon glyphicon-ok');

            $('#disable_user_btn' + id).addClass('btn btn-danger');
            $('#disable_user_spn' + id).addClass('glyphicon glyphicon-remove-sign');

        }
    }

    $('#myTab a').click(function(e) {
        e.preventDefault();
        $(this).tab('show');
    });

    function clean_tab(){
        $('#home_us').removeClass('show');
    }

    // store the currently selected tab in the hash value
    $("ul.nav-tabs > li > a").on("shown.bs.tab", function(e) {
        var id = $(e.target).attr("href").substr(1);
        window.location.hash = id;
    });

    $('#alert_1').fadeIn('fast').delay(5000).fadeOut('slow');
    $('#alert_2').fadeIn('fast').delay(5000).fadeOut('slow');
    $('#alert_3').fadeIn('fast').delay(5000).fadeOut('slow');
    // on load of the page: switch to the currently selected tab
    var hash = window.location.hash;
    $('#myTab a[href="' + hash + '"]').tab('show');
});