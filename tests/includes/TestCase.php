<?php

namespace WebwinkelKeur\WordPress\Tests;

use ReflectionMethod;
use ReflectionProperty;
use SimpleXMLElement;
use WC_Product_Simple;
use WebwinkelKeur\WordPress\Plugin;
use WP_UnitTestCase;

abstract class TestCase extends WP_UnitTestCase {
    use Helpers;
}

trait Helpers {
    protected static function plugin(): Plugin {
        return Plugin::getInstance();
    }

    /** Call a private or protected method. */
    protected static function callPrivate($object, string $method, ...$args) {
        $m = new ReflectionMethod($object, $method);
        $m->setAccessible(true);
        return $m->invoke($object, ...$args);
    }

    protected static function createProduct(): int {
        $product = new WC_Product_Simple();
        $product->set_name('Test product');
        $product->set_regular_price('10');
        return $product->save();
    }

    protected static function createAdmin(): int {
        $user_id = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($user_id);
        return $user_id;
    }

    protected static function reviewXml(
        int $review_id,
        int $product_id,
        string $name,
        string $content,
        bool $deleted = false
    ): SimpleXMLElement {
        $x = new SimpleXMLElement('<review/>');
        $x->review_id = $review_id;
        $x->email = "reviewer{$review_id}@example.com";
        $x->deleted = (int) $deleted;
        $x->valid = 1;
        $x->review_timestamp = '2026-11-01T12:00:00+01:00';
        $x->addChild('reviewer')->name = $name;
        $x->content = $content;
        $x->addChild('ratings')->overall = 5;
        $x->addChild('products')->addChild('product')->external_id = $product_id;
        return $x;
    }

    /** Process a review as the review sync would. */
    protected static function syncReview(
        int $review_id,
        int $product_id,
        string $name,
        string $content,
        bool $deleted = false
    ): void {
        self::callPrivate(
            self::plugin()->woocommerce,
            'doProcessReview',
            self::reviewXml($review_id, $product_id, $name, $content, $deleted),
        );
    }

    /** @return \WP_Comment[] */
    protected static function syncedReviews(int $review_id): array {
        return get_comments([
            'type' => 'review',
            'meta_key' => self::callPrivate(self::plugin()->woocommerce, 'getReviewIdMetaKey'),
            'meta_value' => $review_id,
        ]);
    }

    protected function syncedReview(int $review_id): \WP_Comment {
        $reviews = self::syncedReviews($review_id);
        $this->assertCount(1, $reviews, "Comments for review {$review_id}");
        return $reviews[0];
    }

    /**
     * Convert the comment text columns to $charset. This is DDL, so it
     * commits the current transaction: only call it outside of tests.
     */
    protected static function setCommentCharset(string $charset): void {
        global $wpdb;
        $collation = [
            'utf8mb3' => 'utf8mb3_general_ci',
            'utf8mb4' => 'utf8mb4_unicode_520_ci',
        ][$charset];
        foreach (['comment_author' => 'TINYTEXT', 'comment_content' => 'TEXT'] as $column => $type) {
            $wpdb->query("ALTER TABLE {$wpdb->comments} MODIFY {$column} {$type}
                CHARACTER SET {$charset} COLLATE {$collation} NOT NULL");
        }
        // Forget wpdb's cached charsets, or it won't notice the change. These
        // are protected, and wpdb::__set() silently ignores writes to them.
        foreach (['col_meta', 'table_charset'] as $property) {
            $p = new ReflectionProperty($wpdb, $property);
            $p->setAccessible(true);
            $p->setValue($wpdb, []);
        }
    }
}
