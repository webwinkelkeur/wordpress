jQuery(function ($) {
    $(document).on('click', `.${notice_params.class} .notice-dismiss`, function () {
        $.ajax(ajaxurl,
            {
                type: 'POST',
                data: {
                    action: notice_params.hook,
                }
            });
    });
});