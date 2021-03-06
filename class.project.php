<?php
// This class represent a project.
class NtfProject {
    public $id = ''; // This id correspond to the post id

    public $title = '';
    public $author = '';
    public $category = '';
    public $webhook = '';

    public $logoUrl = '';
    public $bannerUrl = '';

    public $launchDate = '';
    public $description = '';
    public $novelty = '';
    public $team = '';
    public $isLucratif = false;
    public $isSearchingPeople = false;

    public $errorMessage = '';

    // public function __construct($title, $category, $webhook, $description) {
    //     $this->title = $title;
    //     $this->category = $category;
    //     $this->webhook = $webhook;

    //     $this->description = $description;
    // }

    // This function allows to create the project from the post data of the form in
    // the widget
    public function construct_from_form($form_data, $category, $webhook) {
        $this->title = $form_data['project_name'];
        $this->author = $form_data['project_author'];
        $this->category = $category; // Can't be deduced from form post
        $this->webhook = $webhook; // Can't be deduced from form post

        $this->logoUrl = (isset($form_data['project_logo']) && !empty($form_data['project_logo'])) ? $form_data['project_logo'] : "https://new-talents.fr/wp-content/uploads/2019/07/mini.png";
        $this->bannerUrl = (isset($form_data['project_banner']) && !empty($form_data['project_banner'])) ? $form_data['project_banner'] : "";

        $this->launchDate = strtotime($form_data['project_launch_date']);
        $this->description = $form_data['project_description'];
        $this->novelty = $form_data['project_novelty'];
        $this->team = $form_data['project_team'];
        
        $this->isLucratif = isset($form_data['project_is_lucratif']);
        $this->isSearchingPeople = isset($form_data['project_is_searching_people']);
    }

    // This function create an array which represent a post for Wordpress
    public function to_wp_post() {
        // This one show if the project is recruting etc.
        $finalCitation = (($this->isLucratif) ? 'Ce projet est lucratif' : "Ce projet n'est pas lucratif") . " et " . (($this->isSearchingPeople) ? 'recherche de nouveaux profils' : 'ne recherche pas de nouveaux profils');

        // This is the content of the WP post
        $content =  "<h2>$this->title</h2>";
        $content .= "<p>Un projet par $this->author, lancement le " . date('d/m/Y', $this->launchDate) . ".";
        if (isset($this->bannerUrl) && !empty($this->bannerUrl)) {
            $content .= "<figure class=\"wp-block-image size-large\"><img src=\"$this->bannerUrl\" alt=\"Bannière du projet\"/><figcaption>Bannière du projet</figcaption></figure>";
        }
        $content .= "<h3>Description du projet</h3>";
        $content .= "<p>$this->description</p>";
        $content .= "<h3>L'originalité du projet</h3>";
        $content .= "<p>$this->novelty</p>";
        $content .= "<h3>L'équipe du projet</h3>";
        $content .= "<p>$this->team</p>";
        $content .= "<blockquote class=\"wp-block-quote\"><p>$finalCitation</p></blockquote>";

        return array(
			'post_title'     => $this->title,
			'post_category'  => array($this->category),
			'post_content'   => wp_kses_post($content), // For now we only show the description... 
            'comment_status' => get_option('default_comment_status'),
            'post_status'    => 'publish'
		);
    }

    // This function return the message to send on Discord
    public function to_discord_post() {
        return json_encode([
            // The general "message" shown above your embeds
            "content" => "",
            // The username shown in the message
            "username" => "Projet [$this->title]",
            // The image location for the senders image
            "avatar_url" => $this->logoUrl,
            // Whether or not to read the message in Text-to-speech
            "tts" => false,
            // The embeds
            "embeds" => [
                // The first embeds with most informations
                [
                    "title" => $this->title,
                    // The type of your embed, will ALWAYS be "rich"
                    "type" => "rich",

                    "description" => "**Description du projet :** $this->description\n\n**Originalité du projet :** $this->novelty\n\n**L'équipe du projet :** $this->team",

                    "url" => get_permalink($this->id),
                    
                    "color" => hexdec( "d81b60" ),

                    "author" => [
                        "name" => $this->author
                    ],

                    "footer" => [
                        "text" => "Lancement le " . date('d/m/Y', $this->launchDate)
                    ]
                ],
                [
                    "description" => (($this->isLucratif) ? 'Ce projet est lucratif' : "Ce projet n'est pas lucratif") . " et " . (($this->isSearchingPeople) ? 'recherche de nouveaux profils' : 'ne recherche pas de nouveaux profils'),
                    "color" => hexdec( "f5ff00" ),
                    // Image object
                    "image" => [
                        "url" => $this->bannerUrl
                    ]
                ]
            ]
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    }
}
?>