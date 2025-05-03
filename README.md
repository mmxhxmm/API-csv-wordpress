# API-csv-wordpress
WooCommerce CSV Product Importer with Variations and Images
This PHP script imports products into a WooCommerce store from a CSV file. It creates variable products with variations (based on size and color), handles categories, and sideloads product images from external URLs.

Features
Imports products from a semicolon-separated CSV file

Creates variable products and their variations (color and size)

Assigns products to categories (creates categories if they don't exist)

Sideloads product and variation images from Cloudinary

Skips duplicate products using product name and SKU checks

Automatically updates existing attributes

Requirements
WordPress with WooCommerce installed

Script placed inside a directory where WordPress is accessible (e.g., /your-site/wp-content/scripts/)

A valid CSV file with specific headers

How to Use
Place your CSV file in the same directory as the script and update the path:

php
Copy
Edit
$csvFile = fopen('./your-file.csv', 'r');
Modify the image_url construction logic if your Cloudinary naming scheme differs:

php
Copy
Edit
$image_url = "https://res.cloudinary.com/your-web-name/t_pim/TechnicalNames/". $codigoColor ."_" . $SKU . "_" . $ColorCode . ".jpg";
Run the script in a browser or terminal using PHP CLI:

bash
Copy
Edit
php import-products.php
CSV Format
Your CSV file should use semicolon (;) as the delimiter and contain the following headers:

StyleCode	StyleName	ShortDescription	LongDescription	Category	Color	Color-codigo	ColorCode	SizeCode	K3EUR	B2BSKUREF

Make sure the first row of your CSV file contains these exact headers.

Output
Products and variations will be created in WooCommerce.

Images are fetched from a specified Cloudinary URL and attached to products.

Each created or skipped item is printed in the output for debugging.

Notes
The script uses WooCommerce and WordPress internal functions, so it must be run inside the WordPress environment (hence the require_once('../wp-load.php')).

If your site or server has a timeout or memory limit, consider chunking the CSV or increasing PHP limits.

Troubleshooting
If images fail to load, verify that the constructed image URL is valid.

If nothing happens, make sure the script is placed correctly and WordPress is loaded properly.

Ensure WooCommerce is active and the wc_* functions are available.

License
MIT License – use and modify freely.