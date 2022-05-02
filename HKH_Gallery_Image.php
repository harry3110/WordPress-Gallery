<?php

class HKH_Gallery_Image
{
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var int|null
     */
    private $attachment_id;

    /**
     * @var int
     */
    private $gallery_id;

    /**
     * @var bool
     */
    private $is_active;

    /**
     * @param  int|null  $id
     */
    public function __construct(?int $id = null)
    {
        $this->id = $id;

        if ($id) $this->load_from_db();
    }

    /**
     * @return string
     */
    public function get_title(): string
    {
        return $this->title;
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
    public function get_description(): string
    {
        return $this->description;
    }

    /**
     * @param  string  $description
     */
    public function set_description(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return HKH_Gallery
     */
    public function get_gallery(): HKH_Gallery
    {
        return new HKH_Gallery($this->gallery_id);
    }

    /**
     * @param  HKH_Gallery|int  $gallery
     */
    public function set_gallery($gallery): void
    {
        if ($gallery instanceof HKH_Gallery) {
            $this->gallery_id = $gallery->get_id();
        } else {
            $this->gallery_id = $gallery;
        }
    }

    /**
     * @return int|mixed
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function get_attachment_id(): int
    {
        return $this->attachment_id;
    }

    public function get_attachment_url(): ?string
    {
        return $this->attachment_id ? wp_get_attachment_url($this->attachment_id) : null;
    }

    /**
     * @param  int  $attachment_id
     */
    public function set_attachment_id(int $attachment_id): void
    {
        $this->attachment_id = $attachment_id;
    }

    public function get_active(): bool
    {
        return $this->is_active;
    }

    /**
     * @param  bool  $is_active
     */
    public function set_active(bool $is_active): void
    {
        $this->is_active = $is_active;
    }

    private function load_from_db()
    {
        global $wpdb;

        $row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}hkh_gallery_images WHERE id = {$this->id}");

        if ($row) {
            $this->title = $row->title;
            $this->description = $row->description;
            $this->attachment_id = $row->media_id;
            $this->gallery_id = $row->gallery_id;
            $this->is_active = $row->active;
        }
    }

    public function save()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hkh_gallery_images';

        if ($this->id > 0) {
            $wpdb->update($table, [
                "title" => $this->title,
                "description" => $this->description,
                "media_id" => $this->attachment_id,
                "gallery_id" => $this->gallery_id,
                "active" => $this->is_active,
            ], [
                "id" => $this->id
            ]);
        } else {
            $wpdb->insert($table, [
                "title" => $this->title,
                "description" => $this->description,
                "media_id" => $this->attachment_id,
                "gallery_id" => $this->gallery_id,
                "active" => $this->is_active,
            ]);

            $this->id = $wpdb->insert_id;
        }
    }

    public function delete()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hkh_gallery_images';

        $wpdb->delete($table, [
            "id" => $this->id
        ]);
    }
}