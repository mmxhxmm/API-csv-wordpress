<h1><b>WooCommerce CSV Product Importer with Variations and Images</b></h1>

  <p>
    This PHP script imports products into a WooCommerce store from a CSV file.
    It creates variable products with variations (based on size and color),
    handles categories, and sideloads product images from external URLs.
  </p>

  <h2><b>Features</b></h2>
  <ul>
    <li>Imports products from a semicolon-separated CSV file</li>
    <li>Creates variable products and their variations (color and size)</li>
    <li>Assigns products to categories (creates categories if they don't exist)</li>
    <li>Sideloads product and variation images from Cloudinary</li>
    <li>Skips duplicate products using product name and SKU checks</li>
    <li>Automatically updates existing attributes</li>
  </ul>

  <h2><b>Requirements</b></h2>
  <ul>
    <li>WordPress with WooCommerce installed</li>
    <li>Script placed inside a directory where WordPress is accessible (e.g., <code>/your-site/wp-content/scripts/</code>)</li>
    <li>A valid CSV file with specific headers</li>
  </ul>

  <h2><b>How to Use</b></h2>
  <ol>
    <li>Place your CSV file in the same directory as the script and update the path:</li>
    <pre><code>$csvFile = fopen('./your-file.csv', 'r');</code></pre>

    <li>Modify the <code>image_url</code> construction logic if your Cloudinary naming scheme differs:</li>
    <pre><code>$image_url = "https://res.cloudinary.com/your-web-name/t_pim/TechnicalNames/" . $codigoColor . "_" . $SKU . "_" . $ColorCode . ".jpg";</code></pre>

    <li>Run the script in a browser or terminal using PHP CLI:</li>
    <pre><code>php import-products.php</code></pre>
  </ol>

  <h2><b>CSV Format</b></h2>
  <p>Your CSV file should use semicolon (<code>;</code>) as the delimiter and contain the following headers:</p>
  <pre><code>StyleCode	StyleName	ShortDescription	LongDescription	Category	Color	Color-codigo	ColorCode	SizeCode	K3EUR	B2BSKUREF</code></pre>
  <p>Make sure the first row of your CSV file contains these exact headers.</p>

  <h2><b>Output</b></h2>
  <ul>
    <li>Products and variations will be created in WooCommerce</li>
    <li>Images are fetched from a specified Cloudinary URL and attached to products</li>
    <li>Each created or skipped item is printed in the output for debugging</li>
  </ul>

  <h2><b>Notes</b></h2>
  <ul>
    <li>The script uses WooCommerce and WordPress internal functions, so it must be run inside the WordPress environment (via <code>require_once('../wp-load.php')</code>)</li>
    <li>If your site or server has a timeout or memory limit, consider chunking the CSV or increasing PHP limits</li>
  </ul>

  <h2><b>Troubleshooting</b></h2>
  <ul>
    <li>If images fail to load, verify that the constructed image URL is valid</li>
    <li>If nothing happens, make sure the script is placed correctly and WordPress is loaded properly</li>
    <li>Ensure WooCommerce is active and the <code>wc_*</code> functions are available</li>
  </ul>

