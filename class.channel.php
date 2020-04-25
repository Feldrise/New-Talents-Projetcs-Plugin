<?php 
// This class represent New Talents's Discord channel 
class NtfChannel {
    public $slug = '';
    public $name = '';
    public $category_id = '';
    public $webhook = '';

    public function __construct($slug, $name, $category_id, $webhook) {
        $this->slug = $slug;
        $this->name = $name;
        $this->category_id = $category_id;
        $this->webhook = $webhook;
    }
}
?>