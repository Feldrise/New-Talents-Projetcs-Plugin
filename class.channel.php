<?php 
// This class represent New Talents's Discord channel 
class NtfChannel {
    public $slug = '';
    public $name = '';
    public $webhook = '';

    public function __construct($slug, $name, $webhook) {
        $this->slug = $slug;
        $this->name = $name;
        $this->webhook = $webhook;
    }
}
?>