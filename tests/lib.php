<?php

class AssertionFailed extends Exception {}

class Tests {
    private static $tests = [];

    public static function add(string $name, callable $fn): void {
        self::$tests[$name] = $fn;
    }

    public static function run(): bool {
        $failed = 0;
        foreach (self::$tests as $name => $fn) {
            try {
                $fn();
                echo "ok   {$name}\n";
            } catch (Throwable $e) {
                $failed++;
                echo "FAIL {$name}\n";
                echo '     ' . str_replace("\n", "\n     ", get_class($e) . ': ' . $e->getMessage()) . "\n";
                if (!$e instanceof AssertionFailed) {
                    echo '     at ' . $e->getFile() . ':' . $e->getLine() . "\n";
                }
            }
        }
        printf("\n%d tests, %d failed\n", count(self::$tests), $failed);
        return $failed === 0;
    }
}

function test(string $name, callable $fn): void {
    Tests::add($name, $fn);
}

function assert_same($expected, $actual, string $message = ''): void {
    if ($expected !== $actual) {
        throw new AssertionFailed(($message ? "{$message}\n" : '')
            . 'expected: ' . var_export($expected, true) . "\n"
            . 'actual:   ' . var_export($actual, true));
    }
}

function assert_throws(string $class, callable $fn, string $message = ''): void {
    try {
        $fn();
    } catch (Throwable $e) {
        if ($e instanceof $class) {
            return;
        }
        throw $e;
    }
    throw new AssertionFailed($message ?: "Expected {$class} to be thrown");
}

function plugin(): \WebwinkelKeur\WordPress\Plugin {
    return \WebwinkelKeur\WordPress\Plugin::getInstance();
}

function call_private($object, string $method, ...$args) {
    $m = new ReflectionMethod($object, $method);
    $m->setAccessible(true);
    return $m->invoke($object, ...$args);
}

function create_product(): int {
    $product = new WC_Product_Simple();
    $product->set_name('Test product');
    $product->set_regular_price('10');
    return $product->save();
}

/** Run $fn with the comment text columns converted to $charset. */
function with_comment_charset(string $charset, callable $fn) {
    global $wpdb;
    $collations = [
        'utf8mb3' => 'utf8mb3_general_ci',
        'utf8mb4' => 'utf8mb4_unicode_520_ci',
    ];
    $alter = function (string $charset) use ($wpdb, $collations) {
        foreach (['comment_author' => 'TINYTEXT', 'comment_content' => 'TEXT'] as $column => $type) {
            $wpdb->query("ALTER TABLE {$wpdb->comments} MODIFY {$column} {$type}
                CHARACTER SET {$charset} COLLATE {$collations[$charset]} NOT NULL");
        }
        // Forget wpdb's cached charsets, or it won't notice the change. These
        // are protected, and wpdb::__set() silently ignores writes to them.
        foreach (['col_meta', 'table_charset'] as $property) {
            $p = new ReflectionProperty($wpdb, $property);
            $p->setAccessible(true);
            $p->setValue($wpdb, []);
        }
    };
    $original = $wpdb->get_col_charset($wpdb->comments, 'comment_content');
    $alter($charset);
    try {
        return $fn();
    } finally {
        $alter($original === 'utf8' ? 'utf8mb3' : $original);
    }
}
