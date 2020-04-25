<?php
// This class represent a project.
class NtfProject {
    public $title = '';
    public $category = '';
    public $webhook = '';

    public $description = '';

    public function __construct($title, $category, $webhook, $description) {
        $this->title = $title;
        $this->category = $category;
        $this->webhook = $webhook;

        $this->description = $description;
    }

    // This function create an array which represent a post for Wordpress
    public function to_wp_post() {
        // TODO: modify the content to be more than just the description
        return array(
			'post_title'     => $this->title,
			'post_category'  => array($this->category),
			'post_content'   => wp_kses_post($this->description), // For now we only show the description... 
            'comment_status' => get_option('default_comment_status'),
            'post_status'    => 'publish'
		);
    }

    // This function return the message to send on Discord
    public function to_discord_post() {
        return json_encode([
            // The general "message" shown above your embeds
            "content" => $this->description,
            // The username shown in the message
            "username" => "Projet",
            // The image location for the senders image
            "avatar_url" => "https://new-talents.fr/wp-content/uploads/2019/07/mini.png",
            // Whether or not to read the message in Text-to-speech
            "tts" => false,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    }
}
?>