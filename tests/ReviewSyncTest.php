<?php

function review_xml(int $review_id, int $product_id, string $name, string $content): SimpleXMLElement {
    $x = new SimpleXMLElement('<review/>');
    $x->review_id = $review_id;
    $x->email = "reviewer{$review_id}@example.com";
    $x->deleted = 0;
    $x->valid = 1;
    $x->review_timestamp = '2026-11-01T12:00:00+01:00';
    $x->addChild('reviewer')->name = $name;
    $x->content = $content;
    $x->addChild('ratings')->overall = 5;
    $x->addChild('products')->addChild('product')->external_id = $product_id;
    return $x;
}

function sync_review(int $review_id, int $product_id, string $name, string $content): void {
    call_private(plugin()->woocommerce, 'doProcessReview',
        review_xml($review_id, $product_id, $name, $content));
}

/** @return WP_Comment[] */
function synced_reviews(int $review_id): array {
    return get_comments([
        'type' => 'review',
        'meta_key' => call_private(plugin()->woocommerce, 'getReviewIdMetaKey'),
        'meta_value' => $review_id,
    ]);
}

function synced_review(int $review_id): WP_Comment {
    $reviews = synced_reviews($review_id);
    assert_same(1, count($reviews), "Number of comments for review {$review_id}");
    return $reviews[0];
}

$next_review_id = 1000;

test('review: plain text is stored as-is', function () use (&$next_review_id) {
    $id = $next_review_id++;
    sync_review($id, create_product(), 'Jan', 'Mooie fles, één twee €');
    $c = synced_review($id);
    assert_same('Jan', $c->comment_author);
    assert_same('Mooie fles, één twee €', $c->comment_content);
    assert_same('5', get_comment_meta($c->comment_ID, 'rating', true));
});

test('review: backslashes and quotes are preserved on insert', function () use (&$next_review_id) {
    $id = $next_review_id++;
    sync_review($id, create_product(), 'O\'Brien \\o/', 'Path C:\\temp\\new, "quoted" and it\'s \\\\ fine');
    $c = synced_review($id);
    assert_same('O\'Brien \\o/', $c->comment_author);
    assert_same('Path C:\\temp\\new, "quoted" and it\'s \\\\ fine', $c->comment_content);
});

test('review: backslashes are preserved on update', function () use (&$next_review_id) {
    $id = $next_review_id++;
    $product_id = create_product();
    sync_review($id, $product_id, 'Jan', 'First version');
    sync_review($id, $product_id, 'Jan \\ Piet', 'Second \\ version');
    $c = synced_review($id);
    assert_same('Jan \\ Piet', $c->comment_author);
    assert_same('Second \\ version', $c->comment_content);
});

test('review: re-syncing an unchanged review keeps one comment', function () use (&$next_review_id) {
    $id = $next_review_id++;
    $product_id = create_product();
    sync_review($id, $product_id, 'Jan', 'Same \\ text 😻');
    sync_review($id, $product_id, 'Jan', 'Same \\ text 😻');
    assert_same('Same \\ text 😻', synced_review($id)->comment_content);
});

test('review: deleted review is removed', function () use (&$next_review_id) {
    $id = $next_review_id++;
    $product_id = create_product();
    sync_review($id, $product_id, 'Jan', 'To be deleted');
    $xml = review_xml($id, $product_id, 'Jan', 'To be deleted');
    $xml->deleted = 1;
    call_private(plugin()->woocommerce, 'doProcessReview', $xml);
    assert_same(0, count(synced_reviews($id)));
});

test('review: utf8mb4 table stores 4-byte characters as-is', function () use (&$next_review_id) {
    with_comment_charset('utf8mb4', function () use (&$next_review_id) {
        $id = $next_review_id++;
        sync_review($id, create_product(), 'Kim 🌸', 'Prachtig 😻 𝓐 𠀀 \\o/');
        $c = synced_review($id);
        assert_same('Kim 🌸', $c->comment_author);
        assert_same('Prachtig 😻 𝓐 𠀀 \\o/', $c->comment_content);
    });
});

test('review: utf8mb3 table stores 4-byte characters as entities', function () use (&$next_review_id) {
    with_comment_charset('utf8mb3', function () use (&$next_review_id) {
        $id = $next_review_id++;
        sync_review($id, create_product(), 'Kim 🌸', 'Prachtig 😻 𝓐 𠀀 één \\o/');
        $c = synced_review($id);
        assert_same('Kim &#x1f338;', $c->comment_author);
        assert_same('Prachtig &#x1f63b; &#x1d4d0; &#x20000; één \\o/', $c->comment_content);
    });
});

test('review: utf8mb3 table encodes 4-byte characters on update', function () use (&$next_review_id) {
    with_comment_charset('utf8mb3', function () use (&$next_review_id) {
        $id = $next_review_id++;
        $product_id = create_product();
        sync_review($id, $product_id, 'Jan', 'Goed');
        sync_review($id, $product_id, 'Jan', 'Goed 👍🏽 \\o/');
        assert_same('Goed &#x1f44d;&#x1f3fd; \\o/', synced_review($id)->comment_content);
    });
});

test('review: unrelated write failure is still reported', function () use (&$next_review_id) {
    $id = $next_review_id++;
    $product_id = create_product();
    assert_throws(RuntimeException::class, function () use ($id, $product_id) {
        sync_review($id, $product_id, 'Jan', str_repeat('a', 70000));
    });
    assert_same(0, count(synced_reviews($id)));
});
