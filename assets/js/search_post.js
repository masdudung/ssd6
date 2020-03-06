jQuery(document).ready(function($) {

    console.log('ase')

    var data = {
        'action': 'search_post_handler',
        'whatever': 1234
    }

    $( "#post_title" ).autocomplete({
        source: function(request, response) {
            $.ajax({
                type: "POST",
                url: test.ajaxurl,
                data: {
                    action: 'search_post_handler',
                    keyword: $('#post_title').val()
                },
                success: function (data) {
                    console.log(data)

                    if (data != null) {
                        response(data);
                    }
                },
                error: function(result) {
                    alert("Error");
                }
            });
        }
    });

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    // jQuery.post(test.ajaxurl, data, function(response) {
    //     alert('Got this from the server: ' + response);
    // });
});