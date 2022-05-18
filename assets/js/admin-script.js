jQuery(document).ready(function() {

    /* view result scroll */
    jQuery(".cf7p-view-poll-result").click(function() {
        jQuery([document.documentElement, document.body]).animate({
            scrollTop: jQuery("#wpcf7-contact-form-editor").offset().top
        }, 500, function() {
            jQuery("#cf7p-polls-result-tab a").trigger("click");
        });
    });

    /*  */
    // jQuery("body").on("submit", "#wpcf7-admin-form-element", function() {
    //     $limit = jQuery("input[name=cf7p_limit]").val();

    //     if ((jQuery(".cf7p-set-limit").is(":checked")) && $limit <= 0) {
    //         jQuery("input[name=cf7p_limit]").addClass("cf7p_error");
    //         return false;
    //     }
    // });

    jQuery(function() {
        jQuery('.cf7p-color-field').wpColorPicker();
    });

    jQuery("body").on('change', '.cf7p-view-result', function() {
        ((jQuery(this).is(":checked")) ? jQuery(".cf7p_result_btn_shortcode").parents('tr').fadeIn() : jQuery(".cf7p_result_btn_shortcode").parents('tr').css("display", "none"));
    });

    jQuery("body").on('change', '.cf7p-set-limit', function() {
        if ((jQuery(this).is(":checked"))) {
            jQuery(".cf7p_set_limit").parents('tr').fadeIn();
        } else {
            jQuery(".cf7p_set_limit").parents('tr').css("display", "none");
        }

    });

    jQuery("body").on("click", "#cf7p_add", function() {
        var $this = jQuery(this);
        $this.find("span.loader").css("visibility", "visible");
        var form_id = jQuery("input[name=cf7p-form-id]").val();
        jQuery.ajax({
            url: custom_call.ajaxurl,
            type: "POST",
            data: {
                action: "cf7p_add_more",
                form_id: form_id,
            },
            success: function(response) {
                jQuery("#cf7p_all_polls").append(response);
                $this.find("span.loader").css("visibility", "hidden");
                ((jQuery(".cf7p-no-field").attr("data-msg")) ? jQuery(".cf7p_polls_btn").hide() : jQuery(".cf7p_polls_btn").show());
                $this.parents('.cf7p_polls_btn').find('#cf7p_remove_all').fadeIn();
            },
        });
    });

    jQuery("body").on("click", ".cf7p_remove_field", function() {
        if (confirm("Are You Sure You Want To Remove ? ")) {
            var form_id = jQuery(this).val();
            var field_name = jQuery(this).data("name");
            jQuery(this).parents('.cf7p-field-row').remove();
            jQuery.ajax({
                url: custom_call.ajaxurl,
                type: "POST",
                data: {
                    action: "cf7p_remove",
                    form_id: form_id,
                    field_name: field_name
                },
                success: function(response) {
                    jQuery("#cf7p_all_polls").append(response);
                    if (jQuery(".hide-remove-all").attr("data-msg")) {
                        jQuery("#cf7p_remove_all").hide();
                        if (jQuery('input[name=cf7p_status]').is(':checked')) {
                            jQuery('input[name=cf7p_status]').removeAttr('checked');
                        }
                    }
                },
            });
        }
    });


    jQuery("body").on("click", "#cf7p_remove_all", function() {
        if (confirm("Are You Sure You Want To Remove All Poll ? ")) {
            var $this = jQuery(this);
            jQuery.ajax({
                url: custom_call.ajaxurl,
                type: "POST",
                data: {
                    action: "cf7p_remove_all",
                    form_id: jQuery("input[name=cf7p-form-id]").val()
                },
                success: function(response) {
                    $this.hide();
                    jQuery('#cf7p_all_polls').empty();
                    if (jQuery('input[name=cf7p_status]').is(':checked')) {
                        jQuery('input[name=cf7p_status]').removeAttr('checked');
                    }
                },
            });
        }
    });
});