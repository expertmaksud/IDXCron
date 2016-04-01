

var adminUtil = {
    importData: function (data)
    {

        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: iiacAjax.ajaxurl,
            data: data,
            success: function (response) {
                if (response.type === "success") {
                    //jQuery("#vote_counter").html(response.vote_count);
                    alert("Data Imported Successfully.");
                }
                else {
                    alert("Please try again later.");
                }
            }
        });
    }
};

jQuery(document).ready(function ($) {
    $(document).ajaxStop($.unblockUI);


    $("#btn_iiac_listing_import").click(function () {
        var data = {
            'action': 'iiac_import_data',
            'postNonce': iiacAjax.postNonce
        };

        $.blockUI({css: {
                border: 'none',
                padding: '15px',
                backgroundColor: '#000',
                '-webkit-border-radius': '10px',
                '-moz-border-radius': '10px',
                opacity: .5,
                color: '#fff'
            }});
        adminUtil.importData(data);

    });
});


