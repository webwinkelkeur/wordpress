<?php

namespace WebwinkelKeur\WordPress\Tests;

class OrderNoteTest extends TestCase {
    public function test_backslashes_and_quotes_are_preserved(): void {
        $order = wc_create_order();
        $content = 'API error: {"message":"Invalid \\"email\\" for O\'Brien","path":"C:\\\\x"}';
        self::callPrivate(self::plugin()->woocommerce, 'insert_comment', $order->get_id(), $content);
        $notes = wc_get_order_notes(['order_id' => $order->get_id()]);
        $this->assertContains($content, array_column($notes, 'content'));
    }
}
