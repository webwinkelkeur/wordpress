= 3.7 - 2021-03-31 =
* Bump tested WordPress version to 5.7.

= 3.6 - 2021-03-31 =
* Fix product review sync when settings form has not been saved since this functionality was added.
* Add button to sync all product reviews manually.
* Show error messages when manual product sync fails.

= 3.5 - 2021-03-22 =
* Allow overriding the sidebar template by creating `webwinkelkeur/sidebar.php` in your theme.

= 3.4 - 2021-01-27 =
* Support GTINs stored via Yoast SEO.

= 3.3 - 2021-01-13 =
* Add notice about product reviews feature.
* Make sure product review scheduled task is registered on every load.

= 3.2 - 2020-12-15 =
* Add sample values to GTIN meta/attribute selector.
* Add button for manually syncing product reviews.

= 3.1 - 2020-12-10 =
* Fix undefined function error when the `woocommerce-product-feeds` plugin is installed but the `woocommerce_gpf_show_element` function does not exist.

= 3.0 - 2020-12-07 =
* Allow order status selection for invites.
* Add product reviews.
* Require PHP 7.0.

= 2.4 - 2020-11-24 =
* Fix disabling JavaScript integration. Previously, the setting wasn't saved.
* Fix setting invitations to "Yes, after a customer's first order.". This setting was converted to "Yes, after every order." silently before.

= 2.3 - 2020-11-24 =
* Bump **Tested up to** to WordPress 5.6.

= 2.2 =
* Use new sidebar load method so only one request is needed.

= 2.1 =
* Bump **Tested up to** to WordPress 5.5.

= 2.0 =
* Release TrustProfile integration.

= 1.9 =
* Set `wpml_language` on orders, in case WooCommerce Multilingual plugin is missing.
* Bump WordPress, WooCommerce version compatibility.

= 1.8 =
* Add `[webwinkelkeur_rich_snippet]` shortcode.

= 1.7 =
* Add webwinkelkeur_request_invitation hook.
* Only request invitations for orders with type `shop_order`.

= 1.6.10 =
* Restore error silencing when invoking get_data.

= 1.6.9 =
* Fix fatal error when order contains incomplete unserialized objects.

= 1.6.8 =
* Bump WordPress compatibility to 5.2.

= 1.6.7 =
* Check required parameters before calling get_data().

= 1.6.6 =
* Log rich snippet errors to the developer console.

= 1.6.5 =
* Update changelog.

= 1.6.4 =
* Bump WordPress, WooCommerce compatibility.
* Fix error messages.

= 1.6.3 =
* Fix compatibility with some plug-ins that mess up get_data().

= 1.6.2 =
* Require PHP 5.6

= 1.6.1 =
* Note required PHP version.

= 1.6.0 =
* Send order totals.
* Fix phones sending

= 1.5.0 =
* Use new WebwinkelKeur API.
* Add sending additional order information feature

= 1.4.4 =
* Do not send interface language to WebwinkelKeur API.

= 1.4.3 =
* Add this changelog.
* Use order number for invitations, not order ID. (This is usually the same.)

= 1.4.2 =
* If using WPML, use the customer's language for the invitation.
* Move the plugin menu item from "Plugins" to "Settings".
