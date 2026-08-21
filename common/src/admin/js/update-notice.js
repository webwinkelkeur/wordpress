jQuery(document).on('click', `.${notice_params.class} .notice-dismiss`, function () {
    jQuery.post(
        ajaxurl,
        {action: notice_params.hook, _ajax_nonce: notice_params.nonce}
    )
});
