=== Member-Exclusive Products for WooCommerce ===
Contributors: amzil000ayoub
Tags: product, visibility, membership, access, restriction
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A WordPress plugin to control WooCommerce product visibility for logged-in users.

=== Description ===

A lightweight WordPress plugin that empowers store owners to control product visibility based on user login status. Seamlessly restrict specific WooCommerce products to logged-in users while maintaining a smooth shopping experience for guests.

=== How it works ===
To set a product as Member-Exclusive (Only registered users can see this product)

1. Go to your dashboard admin page **Products > All Products**.
2. Hover with your cursor on the product you want to change, and click on **Edit**.
3. Scroll down to the section **Product Data**.
4. In **General Tab**, you will see a new sub-tab **Enable to hide this product from guests** (for more info check the screenshots). And that's it , your product now is hidden from guests.
5. Go back to your dashboard admin page **Products > All Products**, you will find 2 new things:
A new **Bulk Action**: Set as login required - Set as public. These two actions are for products mass editing.
A new **Filter**: All access types. This new filter will filter the products between the ones with login required and public access.

=== Frequently Asked Questions ===

= Can I bulk-edit restriction settings for multiple products? =
Absolutely! Go to **Products > All Products**:

1. Select products using checkboxes
2. Choose **Set as login required** or **Set as public** from the bulk actions dropdown
3. Click "Apply" to update all selected products instantly.

= What type of products can this plugin work with? =
This plugin can work with 3 different types:

* Simple product
* External/Affiliate product
* Variable product
= What happens to existing products when I install this plugin? =

* All existing products remain public (treated as if **Enable to hide this product from guests** is unchecked).
* New products default to public visibility.
* Only products explicitly marked as restricted will be hidden from guests.

=== Changelog ===

* 1.0.0
Initial release.

=== Installation ===

= Installation from within WordPress =

1. Visit **Plugins > Add New Plugin**.
2. Search for **Member-Exclusive Products for WooCommerce**.
3. Install and activate the **Member-Exclusive Products for WooCommerce** plugin.
= Or Manual installation =

1. Upload the entire `member-exclusive-products-for-woocommerce` folder to the `/wp-content/plugins/` directory.
2. Visit **Plugins**.
3. Activate **Member-Exclusive Products for WooCommerce** plugin.

== Screenshots ==
1. A new filter and bulk action
2. Simple product
3. External/Affiliate product
4. Variable product