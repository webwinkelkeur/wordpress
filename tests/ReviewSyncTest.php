<?php

namespace WebwinkelKeur\WordPress\Tests;

use RuntimeException;

class ReviewSyncTest extends TestCase {
    public function test_comments_table_is_utf8mb4(): void {
        global $wpdb;
        $this->assertSame('utf8mb4', $wpdb->get_col_charset($wpdb->comments, 'comment_content'));
    }

    public function textProvider(): array {
        return [
            'plain' => ['Jan', 'Mooie fles, één twee €'],
            'backslashes and quotes' => [
                'O\'Brien \\o/',
                'Path C:\\temp\\new, "quoted" and it\'s \\\\ fine',
            ],
            '4-byte characters' => ['Kim 🌸', 'Prachtig 😻 𝓐 𠀀 \\o/'],
        ];
    }

    /** @dataProvider textProvider */
    public function test_insert_stores_text_as_is(string $name, string $content): void {
        self::syncReview(1, self::createProduct(), $name, $content);
        $comment = $this->syncedReview(1);
        $this->assertSame($name, $comment->comment_author);
        $this->assertSame($content, $comment->comment_content);
        $this->assertSame('5', get_comment_meta($comment->comment_ID, 'rating', true));
        $this->assertSame('review', $comment->comment_type);
        $this->assertSame('1', $comment->comment_approved);
    }

    /** @dataProvider textProvider */
    public function test_update_stores_text_as_is(string $name, string $content): void {
        $product_id = self::createProduct();
        self::syncReview(1, $product_id, 'Jan', 'First version');
        self::syncReview(1, $product_id, $name, $content);
        $comment = $this->syncedReview(1);
        $this->assertSame($name, $comment->comment_author);
        $this->assertSame($content, $comment->comment_content);
    }

    public function test_resync_of_unchanged_review_keeps_one_comment(): void {
        $product_id = self::createProduct();
        self::syncReview(1, $product_id, 'Jan', 'Same \\ text 😻');
        self::syncReview(1, $product_id, 'Jan', 'Same \\ text 😻');
        $this->assertSame('Same \\ text 😻', $this->syncedReview(1)->comment_content);
    }

    public function test_deleted_review_is_removed(): void {
        $product_id = self::createProduct();
        self::syncReview(1, $product_id, 'Jan', 'To be deleted');
        self::syncReview(1, $product_id, 'Jan', 'To be deleted', true);
        $this->assertCount(0, self::syncedReviews(1));
    }

    public function test_unrelated_write_failure_is_reported(): void {
        $product_id = self::createProduct();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Could not insert review for product: {$product_id}");
        try {
            self::syncReview(1, $product_id, 'Jan', str_repeat('a', 70000));
        } finally {
            $this->assertCount(0, self::syncedReviews(1));
        }
    }
}
