<?php

namespace WebwinkelKeur\WordPress\Tests;

/**
 * Review sync on a comments table that is still utf8 (utf8mb3), which can't
 * store 4-byte characters.
 */
class ReviewSyncUtf8mb3Test extends TestCase {
    public static function set_up_before_class() {
        parent::set_up_before_class();
        self::setCommentCharset('utf8mb3');
    }

    public static function tear_down_after_class() {
        self::setCommentCharset('utf8mb4');
        parent::tear_down_after_class();
    }

    public function test_comments_table_is_utf8mb3(): void {
        global $wpdb;
        $this->assertContains(
            $wpdb->get_col_charset($wpdb->comments, 'comment_content'),
            ['utf8', 'utf8mb3'],
        );
    }

    public function test_insert_encodes_4_byte_characters(): void {
        self::syncReview(1, self::createProduct(), 'Kim 🌸', 'Prachtig 😻 𝓐 𠀀 één \\o/');
        $comment = $this->syncedReview(1);
        $this->assertSame('Kim &#x1f338;', $comment->comment_author);
        $this->assertSame('Prachtig &#x1f63b; &#x1d4d0; &#x20000; één \\o/', $comment->comment_content);
    }

    public function test_update_encodes_4_byte_characters(): void {
        $product_id = self::createProduct();
        self::syncReview(1, $product_id, 'Jan', 'Goed');
        self::syncReview(1, $product_id, 'Jan', 'Goed 👍🏽 \\o/');
        $this->assertSame('Goed &#x1f44d;&#x1f3fd; \\o/', $this->syncedReview(1)->comment_content);
    }

    public function test_text_without_4_byte_characters_is_not_encoded(): void {
        self::syncReview(1, self::createProduct(), 'Jan', 'Mooie fles, één twee € & meer \\o/');
        $this->assertSame('Mooie fles, één twee € & meer \\o/', $this->syncedReview(1)->comment_content);
    }
}
