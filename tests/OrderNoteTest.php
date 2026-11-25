<?php

test('order note: backslashes and quotes are preserved', function () {
    $order = wc_create_order();
    $content = 'API error: {"message":"Invalid \\"email\\" for O\'Brien","path":"C:\\\\x"}';
    call_private(plugin()->woocommerce, 'insert_comment', $order->get_id(), $content);
    $notes = wc_get_order_notes(['order_id' => $order->get_id()]);
    $contents = array_map(function ($note) { return $note->content; }, $notes);
    assert_same(true, in_array($content, $contents, true),
        'Order notes: ' . var_export($contents, true));
});
