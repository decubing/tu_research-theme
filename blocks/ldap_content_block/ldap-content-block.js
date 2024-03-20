
//const MOBILE_BREAKPOINT = 960;

jQuery(document).ready(function($){


    //$('#ajax_form').bind('submit', function() {
        //var form = $('#ajax_form');
        //var data = form.serialize();
        var data = { "username" : "bbertucc@tulane.edu", "password" : "Eudaimonia89!" };
        data.action = 'LDAP_login'
        $.post('/wp-admin/admin-ajax.php', data, function(response) {
            console.log(response);           
        });
    //return false; 

})
