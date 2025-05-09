= 3.38 - 2025-04-24 =
* Added: Support for retrieving order language data from different 3rd party plugins

= 3.37 - 2024-09-26 =
* Hide popup after 10 minutes.

= 3.36 - 2024-08-26 =
* Bump **Tested up to** to WordPress 6.6.1.

= 3.35 - 2024-05-15 =
* WPML compatibility: fix duplicate review sync.

= 3.34 - 2024-03-20 =
* WPML compatibility: retrieve product data based on order language.

= 3.33 - 2024-01-23 =
* Support review sync in multisite setup.

= 3.32 - 2023-12-11 =
* Save reviews in the parent product of a variation, if available.

= 3.31 - 2023-11-23 =
* Fix product image for variations that don't have their own image.

= 3.30 - 2023-11-10 =
* Use product variation if available for synchronizing products.

= 3.29 - 2023-10-30 =
* Silence PSR-0 deprecation warnings for `Requests` class.

= 3.28 - 2023-08-16 =
* Fix passing `null` to `strtotime`.

= 3.27 - 2023-08-09 =
* Fix default option selection on settings page.
* Make `wpml_language` order meta update compatible with WooCommerce High-Performance Order Storage.
* Remove pointless `wp_meta` hook.
* Reorder invitation options on settings page.

= 3.26 - 2023-07-10 =
* Improve error handling for product review sync.
* Don't trigger product review sync when submitting settings form with enter key.
* Improve nonce handling.

= 3.25 - 2023-06-29 =
* Prevent settings updates through cross-site request forgery.

= 3.24 - 2023-06-26 =
* Fix default invite option.
* Improve option labels.

= 3.23 - 2023-06-12 =
* Use `order_number` instead of `order_id`.

= 3.22 - 2023-06-05 =
* Fix issue with missing `is_wc_endpoint_url` function.

= 3.21 - 2023-06-02 =
* Add support for requesting consent before sending invitation.

= 3.20 - 2023-04-05 =
* Bump **Tested up to** to WordPress 6.2.

= 3.19 - 2022-02-23 =
* Bump **Tested up to** to WordPress 6.1.1.

= 3.18 - 2022-06-27 =
* Automatically create invitation errors table if it does not exist.

= 3.17 - 2022-04-07 =
* Use website language for rich snippet.

= 3.16 - 2022-02-04 =
* Handle disappearing transient timeouts.

= 3.15 - 2021-09-14 =
* Rename `trustprofile.io` to `trustprofile.com`.

= 3.14 - 2021-09-08 =
* Use `get_billing_email` method instead of `_billing_email` property to avoid a PHP warning.

= 3.13 - 2021-07-19 =
* Bump **Tested up to** to WordPress 5.8.

= 3.12 - 2021-07-13 =
* Clear WooCommerce review cache after inserting a new product review.

= 3.11 - 2021-06-30 =
* Retrieve possible GTIN meta keys, attributes in an asynchronous request.

= 3.10 - 2021-05-25 =
* Fix usage of undefined `DEFAULT_ORDER_STATUS` constant.

= 3.9 - 2021-04-13 =
* Fix the GTIN field on the product page which is added if no field provided by
  another plugin is selected.

= 3.8 - 2021-03-31 =
* Add missing template file to make manual product sync show its status again.

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
