<?php

/**
 * Get all galleries
 *
 * @return HKH_Gallery[]
 */
function hkh_get_galleries() {
    global $wpdb;

    $galleries = $wpdb->get_col("SELECT id FROM {$wpdb->prefix}hkh_galleries");

    return array_map(function($id) {
        return new HKH_Gallery($id);
    }, $galleries);
}

/**
 * @return HKH_Gallery
 */
function hkh_get_gallery_by_slug($slug) {
    global $wpdb;

    $id = $wpdb->get_row("SELECT id FROM {$wpdb->prefix}hkh_galleries WHERE slug = '{$slug}'");

    return new HKH_Gallery($id);
}

class HKH_Gallery
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var string
     */
    private $description;

    /**
     * @var int|null
     */
    private $thumbnail_id;

    /**
     * @param  int|null  $id
     */
    public function __construct(?int $id = 0)
    {
        $this->id = $id;

        if ($id) $this->load_from_db();
    }

    /**
     * @return string
     */
    public function get_title(): string
    {
        return $this->title ?? "";
    }

    /**
     * @param  string  $title
     */
    public function set_title(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function get_slug(): string
    {
        return $this->slug ?? "";
    }

    /**
     * @param  string  $slug
     */
    public function set_slug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function get_description(): string
    {
        return $this->description ?? "";
    }

    /**
     * @param  string  $description
     */
    public function set_description(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function get_thumbnail_id(): ?int
    {
        return $this->thumbnail_id;
    }

    public function get_thumbnail_url(): ?string
    {
        return $this->thumbnail_id ? wp_get_attachment_url($this->thumbnail_id) : null;
    }

    /**
     * @param  int  $thumbnail_id
     */
    public function set_thumbnail_id(int $thumbnail_id): void
    {
        $this->thumbnail_id = $thumbnail_id;
    }

    public function add_image($image_id, $title, $description = ""): HKH_Gallery_Image
    {
        $image = new HKH_Gallery_Image();

        $image->set_gallery($this->id);
        $image->set_title($title);
        $image->set_description($description);
        $image->set_attachment_id($image_id);
        $image->save();

        return $image;
    }

    /**
     * @return HKH_Gallery_Image[]
     */
    public function get_images()
    {
        if (!$this->id) return [];

        $image_ids = $this->get_image_ids();

        return array_map(function ($id) {
            return new HKH_Gallery_Image($id);
        }, $image_ids);
    }

    /**
     * Get the image IDs for this gallery
     *
     * @return int[]
     */
    public function get_image_ids(): array
    {
        if (!$this->id) return [];

        global $wpdb;

        return $wpdb->get_col("SELECT id FROM {$wpdb->prefix}hkh_gallery_images WHERE gallery_id = {$this->id}");
    }

    /**
     * Get an image by the image's ID (only works if the image is in this gallery, otherwise returns null)
     *
     * @param $id
     *
     * @return HKH_Gallery_Image|null
     */
    public function get_image($id): ?HKH_Gallery_Image
    {
        $image = new HKH_Gallery_Image($id);

        return $image->get_gallery()->get_id() === $this->get_id() ? $image : null;
    }

    /**
     * @param $attachment_id
     *
     * @return HKH_Gallery_Image|null
     */
    public function get_image_by_attachment_id($attachment_id): ?HKH_Gallery_Image
    {
        global $wpdb;

        $image_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}hkh_gallery_images WHERE media_id = {$attachment_id}");

        return $image_id ? new HKH_Gallery_Image($image_id) : null;
    }

    public function get_url(): string
    {
        return "/gallery?gallery={$this->get_slug()}";
    }

    public function get_image_count()
    {
        global $wpdb;

        return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}hkh_gallery_images WHERE gallery_id = {$this->id}");
    }

    public function delete_image($image_id)
    {
        $image = new HKH_Gallery_Image($image_id);

        if ($image->get_gallery()->get_id() !== $this->get_id()) return;

        $image->delete();
    }

    private function load_from_db()
    {
        global $wpdb;

        $row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}hkh_galleries WHERE id = {$this->id}");

        if ($row) {
            $this->title = $row->title;
            $this->description = $row->description;
            $this->thumbnail_id = $row->thumbnail_id;
            $this->slug = $row->slug ?? sanitize_title($this->title);
        }
    }

    public function get_edit_link()
    {
        return admin_url("admin.php?page=hkh-gallery&action=edit&id={$this->get_id()}");
    }

    public function save()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hkh_galleries';

        if ($this->id > 0) {
            $wpdb->update($table, [
                "title" => $this->title,
                "description" => $this->description,
                "thumbnail_id" => $this->thumbnail_id,
            ], [
                "id" => $this->id
            ]);
        } else {
            $wpdb->insert($table, [
                "title" => $this->title,
                "description" => $this->description,
                "thumbnail_id" => $this->thumbnail_id,
            ]);

            $this->id = $wpdb->insert_id;
        }
    }

    public function delete()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hkh_galleries';

        $wpdb->delete($table, [
            "id" => $this->id
        ]);
    }
}