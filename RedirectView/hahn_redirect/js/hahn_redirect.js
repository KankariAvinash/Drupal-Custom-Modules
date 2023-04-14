(function ($, Drupal, drupalSettings) {
    'use strict';
    Drupal.behaviors.hahn_redirect = {
        attach: function (context, settings) {
            $(document, context).once('hahn_redirect').each(function () {
                // Attach a click listener to the clear button.
                $(function () {
                    $("#dialog").dialog({
                        autoOpen: false,
                        maxWidth: 600,
                        maxHeight: 500,
                        width: 600,
                        height: 500,
                        open: function() {
                            var self = this;
                            $(this).parent('.ui-dialog').on('click', false);
                            $(document).one('click', function() {
                                $(self).dialog('close');
                            });
                        }
                    });
                    var navDoms = document.getElementsByClassName("edit_clear");
                    Array.from(navDoms).forEach(function (navDom) {
                        if (navDom) {
                            navDom.addEventListener('click', function () {
                                let nid = navDom.getAttribute('value');
                                $.ajax({
                                    url: Drupal.url('hello-ajax-response'),
                                    type: 'POST',
                                    dataType: 'json',
                                    data: { 'data': nid },
                                    success: function (response) {
                                        if (response.hasOwnProperty('nid')) {
                                            $("#dialog").dialog("open");
                                            let title = response['first_name'] + ' ' + response['second_name'];
                                            $("#dialog").dialog('option', 'title', title);
                                            $("#tabs").tabs();
                                            $("#tabs").find("#tabs1").find("#fName").html('First Name : ' + response['first_name']);
                                            $("#tabs").find("#tabs1").find("#SName").html('Second Name: ' + response['second_name']);
                                            $("#tabs").find("#tabs2").find("#eMail").html('Email: ' + response['Email']);
                                            $("#tabs").find("#tabs2").find("#phonenumber").html('Phonenumber: ' + response['Phone_number']);
                                            console.log(response);
                                        }
                                    }
                                });

                            });
                        }
                    });
                });

            })
        }
    };
})(jQuery, Drupal, drupalSettings);

