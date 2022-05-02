<?php

/**
 * Plugin Name:     HKH Gallery
 * Author:          Harry Harrison
 * Description:     A plugin to display a gallery using images.
 * Version:         1.0.0
 */

// ORM
require 'HKH_Gallery.php';
require 'HKH_Gallery_Image.php';

class HKH_Gallery_Plugin
{
    public function __construct()
    {
        add_action("wp_enqueue_scripts", [$this, "enqueue_scripts"]);
        add_action("admin_enqueue_scripts", [$this, "admin_enqueue_scripts"]);

        add_action("admin_menu", [$this, "admin_menu"]);
        add_action("admin_init", [$this, "form_submit"]);

        add_shortcode("hkh-gallery", [$this, "gallery_shortcode"]);
        add_shortcode("hkh-gallery-recent", [$this, "gallery_recent_shortcode"]);
    }

    function enqueue_scripts() {
        // JS
        wp_enqueue_script("hkh-gallery-js", plugin_dir_url(__FILE__) . "dist/hkh_gallery.js", ["jquery"], false, true);

        // CSS
        wp_enqueue_style("glightbox", plugin_dir_url(__FILE__) . "node_modules/glightbox/dist/css/glightbox.css");
        wp_enqueue_style("hkh-gallery-css", plugin_dir_url(__FILE__) . "src/main.css");
    }

    function admin_enqueue_scripts() {
        if (!did_action("wp_enqueue_media")) {
            wp_enqueue_media();
        }

        wp_enqueue_style("hkh-gallery-admin-style", plugin_dir_url(__FILE__) . "src/admin.css");
        wp_enqueue_script("hkh-gallery-admin-js", plugin_dir_url(__FILE__) . "src/admin.js", ["jquery"]);
    }

    function admin_menu() {
        add_menu_page("Galleries", "Galleries", "manage_options", "hkh-gallery", [$this, "display_gallery_page"], "dashicons-format-gallery", 8);
        add_submenu_page("hkh-gallery", "Add New", "Add New", "manage_options", "hkh-gallery-add", [$this, "display_gallery_add_page"]);
    }

    function display_gallery_page() {
        if (isset($_GET["action"])) {
            $page = $_GET["action"];

            if ($page === "add" || $page === "new" || $page === "edit") {
                $this->display_gallery_form($_GET["id"] ?? null);
                return;
            }
        }

        $galleries = hkh_get_galleries();

        ?>

        <div class="wrap">
            <h1 class="hkh-galleries-title">Galleries</h1>

            <div class="hkh-grid-container">
                <a class="hkh-grid-item hkh-gallery-new" id="add_new_gallery" href="<?php echo admin_url("admin.php?page=hkh-gallery&action=add") ?>">
                    <div class="hkh-image-container">
                        <div class="hkh-text-background">
                            Add New
                        </div>
                    </div>
                </a>

                <?php foreach ($galleries as $gallery): ?>
                    <a class="hkh-grid-item" href="<?php echo $gallery->get_edit_link() ?>">
                        <div class="hkh-image-container <?php echo !$gallery->get_thumbnail_url() ? "noimage" : "" ?>" <?php if ($gallery->get_thumbnail_url()): ?>style="background-image: url('<?php echo $gallery->get_thumbnail_url() ?>')"<?php endif ?>> <!--style="background-image: url('https://picsum.photos/200/200/?random')"-->
                            <div class="hkh-gallery-hover">
                                <div class="hkh-title"><?php echo $gallery->get_title() ?></div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php
    }

    function display_gallery_form($id = 0) {
        $gallery = new HKH_Gallery($id);
        $updating = $id > 0;

        // Add the gallery
        // if ($_SERVER["REQUEST_METHOD"] === "POST") {
        //
        // }

        ?>

        <form method="POST">
            <input type="hidden" name="hkh_admin_action" value="gallery_form_submit">

            <div class="hkh-gallery-add-container">
                <h1><?php echo $updating ? "Editing gallery" : "Add New Gallery" ?></h1>

                <?php if ($updating): ?>
                    <button type="submit" class="button delete-button" name="delete_gallery" value="true">Delete Gallery</button>
                <?php endif ?>

                <button type="submit" class="button"><?php echo $updating ? "Save gallery" : "Add Gallery" ?></button>
            </div>

            <div class="wrap">
                <div class="hkh-new-gallery-form">
                    <input type="hidden" name="gallery_id" value="<?php echo $gallery->get_id() ?>">

                    <p>Title</p>
                    <input type="text" name="title" id="title" placeholder="Gallery Title" value="<?php echo $gallery->get_title() ?>" required />

                    <p>Description</p>
                    <textarea name="description" id="description" placeholder="Gallery Description" rows="3"><?php echo $gallery->get_description() ?></textarea>

                    <p>Thumbnail Image</p>
                    <!--<input type="text" name="thumbnail" id="thumbnail" placeholder="Thumbnail Image URL">-->

                    <a href="#" class="hkh-upload-media hkh-upload-button" data-image-id="<?php echo $gallery->get_thumbnail_id() ?? 0 ?>">
                        <div class="hkh-image-upload-container" style="background-image: url('<?php echo $gallery->get_thumbnail_url() ?>')">
                            <div class="hkh-overlay">
                                <div class="hkh-placeholder-text">
                                    <?php echo $gallery->get_id() > 0 ? "Change thumbnail" : "Upload an image (not required)" ?>
                                </div>
                            </div>
                        </div>
                    </a>

                    <input type="hidden" name="thumbnail_id" value="<?php echo $gallery->get_thumbnail_id() ?>" />
                </div>

                <div class="hkh-new-gallery-images">
                    <h2 class="hkh-galleries-subtitle">Images</h2>

                    <div class="hkh-image-info-grid">
                        <?php foreach ($gallery->get_images() as $image): ?>
                            <div class="hkh-grid-row">
                                <input type="hidden" name="hkh_images[<?php echo $image->get_id() ?>][id]" value="<?php echo $image->get_id() ?>" />

                                <div class="hkh-col">
                                    <div class="hkh-grid-item hkh-replace-image">
                                        <div class="hkh-image-container" style="background-image: url('<?php echo $image->get_attachment_url() ?>')">
                                            <div class="hkh-image-hover">
                                                <p>Click to Replace</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="button hkh-remove-image">Remove</div>
                                </div>

                                <input class="hkh-gallery-image-input" type="hidden" name="hkh_images[<?php echo $image->get_id() ?>][image_id]" value="<?php echo $image->get_attachment_id() ?>" />

                                <div class="hkh-grid-desc">
                                    <div class="hkh-desc-title">
                                        <p>Title</p>
                                        <input type="text" name="hkh_images[<?php echo $image->get_id() ?>][title]" placeholder="Title" value="<?php echo $image->get_title() ?>" />
                                    </div>

                                    <div class="hkh-desc-text">
                                        <p>Description</p>
                                        <textarea name="hkh_images[<?php echo $image->get_id() ?>][description]" placeholder="Description" rows="3"><?php echo $image->get_description() ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <hr style="width: 100%" />
                        <?php endforeach; ?>
                    </div>

                    <div class="button button-primary button-large hkh-gallery-new-image">
                        Add image(s)
                    </div>
                </div>
            </div>
        </form>

        <?php
    }

    function form_submit() {
        if (!isset($_POST["hkh_admin_action"]) || $_POST["hkh_admin_action"] !== "gallery_form_submit") {
            return;
        }

        $id = $_GET["id"] ?? null;
        $gallery = new HKH_Gallery($id);
        $updating = $id > 0;

        if ($updating && isset($_POST["delete_gallery"]) && $_POST["delete_gallery"] === "true") {
            $gallery->set_inactive();

            wp_redirect(admin_url("admin.php?page=hkh-gallery"));
            exit;
        }

        $gallery->set_title($_POST["title"]);
        $gallery->set_slug(sanitize_title($_POST["title"]));
        $gallery->set_description($_POST["description"]);

        if (isset($_POST["thumbnail_id"]) && $_POST["thumbnail_id"]) {
            $gallery->set_thumbnail_id($_POST["thumbnail_id"]);
        }

        $gallery_saved = $gallery->save();

        if ($gallery_saved === false) {
            echo "<div class='notice notice-error'><p>There was an error saving the gallery.</p>";

            global $wpdb;
            echo "<pre>SQL: " . $wpdb->last_query . "</pre>";
            echo "<hr /><pre>" . print_r($wpdb->last_error, true) . "</pre>";

            echo "</div>";

            return;
        }

        $images = $_POST["hkh_images"] ?? [];

        // New images
        $new_images = array_filter($images, function($image) {
            return !($image["id"] > 0);
        });

        // Existing images
        $existing_images = array_filter($images, function($image) {
            return $image["id"] > 0;
        });

        // Deleted images
        $deleted_images = array_diff($gallery->get_image_ids(), array_column($existing_images, "id"));

        // Add images
        foreach ($new_images as $image) {
            $gallery->add_image($image["image_id"], $image["title"], $image["description"]);
        }

        // Update existing images
        foreach ($existing_images as $image) {
            $image = $gallery->get_image($image["image_id"]);

            if (!$image) continue;

            $image->set_title($image["title"]);
            $image->set_description($image["description"]);
            $image->set_attachment_id($image["image_id"]);
        }

        // Delete images
        foreach ($deleted_images as $image_id) {
            $gallery->delete_image($image_id);
        }

        wp_redirect(admin_url("admin.php?page=hkh-gallery"));
    }

    function gallery_shortcode($attr) {
        if (isset($_GET["g"])) {
            return $this->display_gallery($_GET["g"]);
        } else {
            return $this->display_list();
        }
    }

    function display_gallery($gallery_slug) {
        if (!$gallery_slug) {
            return "<p>Gallery not found. View all galleries <a href='" . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) . "'>here</a>.</p>";
        }

        $gallery = hkh_get_gallery_by_slug($gallery_slug);

        $images = $gallery->get_images();
        $title = $gallery->get_title();

        return $this->get_images_html($images, $title);
    }

    function get_images_html($images, $title = null, $args = []) {
        $html = "<div class='hkh-gallery'>";

        if ($title) {
            $html .= '<h2>' . $title . '</h2>';
        }

        $html .= "<div class=\"row\">";

        // Create three bootstrap columns of images
        for ($col = 1; $col <= 3; $col++) {
            $html .= "<div class=\"col-md-4\">";

            foreach ($images as $i => $image) {
                if ($i % 3 !== $col - 1) continue;

                $lightbox_data = "";
                $title = $image->get_title();
                $description = $image->get_description();

                if ($title) {
                    $lightbox_data .= "data-title=\"" . str_replace('"', '\"', $title) . "\"";
                }

                if ($description) {
                    $lightbox_data .= " data-description=\"" . str_replace('"', '\"', $description) . "\"";
                }

                $html .= "<div class=\"hkh-container mb-4\">";
                $html .= "<a class=\"hkh-inner-container glightbox\" href=\"{$image->get_attachment_url()}\" data-gallery=\"{$image->get_gallery()->get_id()}\" {$lightbox_data}>";
                $html .= "<img class=\"hkh-gallery-image\" src=\"{$image->get_attachment_url()}\" " . (isset($args["img_max_height"]) ? "style=\"max-height: {$args["img_max_height"]}\"" : "") . " loading='lazy' />";
                $html .= "</a>";
                $html .= "</div>";
            }

            $html .= "</div>";
        }

        $html .= "</div>";

        $html .= "</div>";

        return $html;
    }

    function display_list() {
        $html = "<div class='hkh-gallery'>";

        $galleries = hkh_get_galleries();

        $html .= "<div class=\"row\">";

        // Create three bootstrap columns of images
        for ($col = 1; $col <= 3; $col++) {
            $html .= "<div class=\"col-md-4\">";

            foreach ($galleries as $i => $gallery) {
                if ($i % 3 !== $col - 1) continue;

                $lightbox_data = "";
                $title = $gallery->get_title();
                $description = $gallery->get_description();

                if ($title) {
                    $lightbox_data .= "data-title=\"" . str_replace('"', '\"', $title) . "\"";
                }

                if ($description) {
                    $lightbox_data .= " data-description=\"" . str_replace('"', '\"', $description) . "\"";
                }

                $image_url = $gallery->get_thumbnail_id() ? $gallery->get_thumbnail_url() : $gallery->get_images()[0]->get_attachment_url();
                $gallery_url = $gallery->get_url();

                $html .= "<div class=\"mb-4\">";
                $html .= "<div class=\"hkh-container mb-2\">";
                $html .= "<a class=\"hkh-inner-container\" href=\"{$gallery_url}\" data-gallery=\"{$gallery->get_id()}\" {$lightbox_data}>";
                $html .= "<img class=\"hkh-gallery-image\" src=\"{$image_url}\" />";
                $html .= "</a>";
                $html .= "</div>";
                $html .= "<div class='text-center'><strong>{$title}</strong></div>";
                $html .= "</div>";
            }

            $html .= "</div>";
        }

        $html .= "</div>";

        $html .= "</div>";

        return $html;
    }

    function gallery_recent_shortcode($attr) {
        global $wpdb;

        $limit = $attr["limit"] ?? 3;

        $image_ids = $wpdb->get_col("SELECT id FROM {$wpdb->prefix}hkh_gallery_images ORDER BY date_created DESC LIMIT {$limit}");

        $images = array_map(function($image_id) {
            return new HKH_Gallery_Image($image_id);
        }, $image_ids);

        return $this->get_images_html($images, null, [
            "img_max_height" => "150px"
        ]);
    }
}

new HKH_Gallery_Plugin();

register_activation_hook(__FILE__, "hkh_gallery_install");

function hkh_gallery_install() {
    if (!function_exists("dbDelta")) {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    }

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $queries[] = "CREATE TABLE `{$wpdb->prefix}hkh_galleries` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `title` VARCHAR(255) NOT NULL,
        `slug` VARCHAR(255) NOT NULL,
        `description` LONGTEXT NULL,
        `thumbnail_id` INT(11) NULL,
        `date_created` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `active` TINYINT(1) NOT NULL DEFAULT 1,

        PRIMARY KEY (`id`)
    ) $charset_collate;";

    $queries[] = "CREATE TABLE `{$wpdb->prefix}hkh_gallery_images` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `gallery_id` INT(11) NOT NULL,
        `media_id` INT(11) NOT NULL,
        `title` VARCHAR(255) NULL,
        `description` LONGTEXT NULL,
        `date_created` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `active` TINYINT(1) NOT NULL DEFAULT 1,

        PRIMARY KEY (`id`)
    ) $charset_collate;";

    dbDelta($queries);
}