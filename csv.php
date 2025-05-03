<?php
require_once('../wp-load.php');
require_once(ABSPATH . '/wp-admin/includes/media.php');
require_once(ABSPATH . '/wp-admin/includes/file.php');
require_once(ABSPATH . '/wp-admin/includes/image.php');

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
global $wpdb;

$csvFile = fopen('./your-file.csv', 'r'); // add your csv file here
$product_ids = [];
$product_count = 0;
$loaded_images = [];

$headers = fgetcsv($csvFile, 0, ";"); // Assuming CSV uses semicolons as delimiters

while (($row = fgetcsv($csvFile, 0, ";")) !== FALSE) {
    $product_data = array_combine($headers, $row);

    echo "<pre>";
    print_r($product_data);
    echo "</pre>";

    if ($product_data["B2BSKUREF"] == 'B2BSKUREF' || empty($product_data["StyleCode"])) {
        echo "Skipping row with StyleCode: {$product_data['StyleCode']}\n";
        continue;
    }

    $SKU = $product_data["StyleCode"];
    $ColorCode = $product_data["ColorCode"];
    $size = $product_data["SizeCode"];
    $name = $product_data["StyleName"];
    $description = $product_data["LongDescription"];
    $short_description = $product_data["ShortDescription"];
    $categoryGroupDesc = $product_data["Category"];
    $color = $product_data["Color"];
    $price = $product_data["K3EUR"]; 
    $codigoColor = $product_data["Color-codigo"];
    $image_url = "https://res.cloudinary.com/your-web-name/t_pim/TechnicalNames/". $codigoColor ."_" . $SKU . "_" . $ColorCode . ".jpg";
    // Check if product already exists
    if (!isset($product_ids[$name])) {
        $product_id = wc_get_product_id_by_name($name);

        if (!$product_id) {
            echo "Creating new product: $name\n";
            // Create a new variable product if it doesn't exist
            $product = new WC_Product_Variable();
            $product->set_name($name);
            $product->set_sku($SKU);

            // Handle product image upload
            $image_key_principal = $SKU . '_' . $ColorCode;
            if (!array_key_exists($image_key_principal, $loaded_images)) {
                $image_id = sideload_image($image_url); // Use the new function to sideload the image
                
                if ($image_id) {
                    echo "Image uploaded successfully for product: $image_url\n";
                    $loaded_images[$image_key_principal] = $image_id;
                } else {
                    echo "Failed to upload image for product: $image_url\n";
                }
            } else {
                $image_id = $loaded_images[$image_key_principal];
            }

            if (isset($image_id)) {
                $product->set_image_id($image_id);
            }

            // Set product description and price
            $product->set_description($description);
            $product->set_short_description($short_description);
            $product->set_regular_price($price);
            $product->save();
            $product_id = $product->get_id();

            // Handle product categories
            handleCategory($categoryGroupDesc, $product_id);
        }

        $product_ids[$name] = $product_id;
    } else {
        echo "Product already exists: $name\n";
        $product_id = $product_ids[$name];
    }

    // Create or update product variations (if any)
    $product = wc_get_product($product_id);
    $attributes = $product->get_attributes();
    $updated = false;

    // Set color and size as attributes
    $new_attributes = [
        'color' => $color,
        'size' => $size
    ];

    foreach ($new_attributes as $attr => $value) {
        // If the attribute does not exist or needs to be updated
        if (!array_key_exists($attr, $attributes) || !in_array($value, $attributes[$attr]->get_options())) {
            $options = array_key_exists($attr, $attributes) ? $attributes[$attr]->get_options() : [];
            $options[] = $value;

            $attributes_data = new WC_Product_Attribute();
            $attributes_data->set_id(wc_attribute_taxonomy_id_by_name($attr));
            $attributes_data->set_name($attr);
            $attributes_data->set_options($options);
            $attributes_data->set_visible(true);
            $attributes_data->set_variation(true);
            $attributes[$attr] = $attributes_data;
            $updated = true;
        }
    }

    if ($updated) {
        $product->set_attributes($attributes);
        $product->save();
    }

    // Generate variations for each combination of size and color
    $variation_sku = $SKU . '_' . $ColorCode . '_' . $size;
    if (!wc_get_product_id_by_sku($variation_sku)) {
        $variation = new WC_Product_Variation();
        $variation->set_parent_id($product_id);
        $variation->set_sku($variation_sku);

        // Set variation attributes
        $variation->set_attributes([
            'color' => $color,
            'size' => $size,
        ]);

        // Set price for the variation
        $variation->set_regular_price($price);

        // Handle variation image (use the color and style combination to get the image)
        $image_key = $SKU . '_' . $ColorCode . '_' . $size;
        if (!array_key_exists($image_key, $loaded_images)) {
            $image_id = sideload_image($image_url); // Use the new function to sideload the image
            
            if ($image_id) {
                echo "Variation image uploaded successfully: $image_url\n";
                $loaded_images[$image_key] = $image_id;
            } else {
                echo "Failed to upload variation image: $image_url\n";
            }
        } else {
            $image_id = $loaded_images[$image_key];
        }

        if (isset($image_id)) {
            $variation->set_image_id($image_id);
        }

        // Save the variation
        $variation->save();
        $product_count++;
    }
}

// Close the file after processing
fclose($csvFile);
echo "Total products processed: $product_count";

// Helper function to handle categories
function handleCategory($categoryDesc, $product_id) {
    $parent_term_id = 0;
    $parent_term = term_exists($categoryDesc, 'product_cat');

    if (!$parent_term) {
        $parent_term = wp_insert_term($categoryDesc, 'product_cat');
        if (!is_wp_error($parent_term)) {
            $parent_term_id = $parent_term['term_id'];
        }
    } else {
        $parent_term_id = $parent_term['term_id'];
    }

    wp_set_object_terms($product_id, array($categoryDesc), 'product_cat', true);
}

function wc_get_product_id_by_name($product_name) {
    global $wpdb;
    $product_id = $wpdb->get_var($wpdb->prepare("SELECT posts.ID FROM $wpdb->posts as posts LEFT JOIN $wpdb->postmeta as postmeta ON posts.ID = postmeta.post_id WHERE posts.post_title = %s AND posts.post_type = 'product' LIMIT 1", $product_name));
    return $product_id ? (int)$product_id : 0;
}

// Function to sideload an image from a URL and return the attachment ID
function sideload_image($image_url) {
    // Download image
    $tmp = download_url($image_url);

    if (is_wp_error($tmp)) {
        return false; // If download failed
    }

    $file_array = array(
        'name' => basename($image_url),
        'tmp_name' => $tmp
    );

    $id = media_handle_sideload($file_array, 0);

    if (is_wp_error($id)) {
        @unlink($file_array['tmp_name']); 
        return false;
    }

    return $id;
}
?>
