<?php 
// This widget allow users to submit a project in the Discord and in the site
class WidgetProjectForm {
    public static $channels = array(); // We store channels in an array

    public static $statusMessage = ''; // We can show the current statue of the form

    // Init the plugin
    public static function init() {
        // We add all channels 
        self::$channels['agroalimentaire'] = new NtfChannel('agroalimentaire', 'Agroalimentaire', NtfWpCategories::AGROALIMENTAIRE, NtfWebhooks::AGROALIMENTAIRE);
        self::$channels['etudes_et_conseils'] = new NtfChannel('etudes_et_conseils', 'Etudes et Conseils', NtfWpCategories::ETUDES_ET_CONSEILS, NtfWebhooks::ETUDES_ET_CONSEILS);
    }

    // This defines what to do when the form is submited
    public static function project_submit() {
        if(isset($_POST['project-send'])) {
            // We create the post object from the info
            $project = new NtfProject();
            $project->construct_from_form(
                $_POST, 
                self::$channels[$_POST['project_category']]->category_id,      // The project category id on New Talents site
                self::$channels[$_POST['project_category']]->webhook
            );
            
            // We add the post on the site
            self::submit_wordpress_post($project);

            // We send the embed to Discord
            self::send_discord_message($project);
        }
    }

    // This is the form for the project submition.
    public static function project_form() {
        // We may show a status message
        if (!empty(self::$statusMessage)) {
?>
    <p style="background-color: #F0F0F0; border-left: 3px solid #d81b60; padding: 8px">
        <?php echo(self::$statusMessage); ?>
    </p>
<?php
        }
?>
<form class="project-submition-form" method="post" action="#">
    <p class="project-submition-form-select">
        <label>Catégorie du projet : </label>
        <select id="project_category" name="project_category">
<?php
        // We show each channels on the select
        foreach (self::$channels as $key => $channel) {
            echo("<option value=\"$key\">$channel->name</option>");
            self::$statusMessage = "";
            $_POST = array();
        }
?>
        </select>
    </p>
    <p>
        <label>Nom du projet : </label><input class="project-form-field" type="text" id="project_name" name="project_name" placeholder="Nom du projet" required/>
    </p>
    <p>
        <label>Nom de l'auteur : </label><input type="text" id="project_author" name="project_author" placeholder="Nom de l'auteur" required/>
    </p>
    <p>
        <label>Url du logo (optionnel)</label><input type="text" id="project_logo" name="project_logo" placeholder="Logo du projet (optionnel)" />
    </p>
    <p>
        <label>Url de la bannière (optionnel)</label><input type="text" id="project_banner" name="project_banner" placeholder="Bannière du projet (optionnel)" />
    </p>
    <p>
        <label>Date de lancement : </label><input type="date" id="project_launch_date" name="project_launch_date" value="<?php echo date('Y-m-d'); ?>" required/><br/>
    </p>
    <p>
        <label>Description du projet : </label><textarea id="project_description" name="project_description" placeholder="Description du projet" row="4" required></textarea>
    </p>
    <p>
        <label>Qu'est-ce que votre projet apporte de nouveau ? : </label><textarea id="project_novelty" name="project_novelty" placeholder="Qu'est-ce que votre projet apporte de nouveau ?" row="4" required></textarea>
    </p>
    <p>
        <label>Constitution de l'équipe : </label><input type="text" id="project_team" name="project_team" placeholder="Constitution de l'équipe" required/>
    </p>
    <p>
        <label for="project_is_lucratif">Le projet est à but lucratif </label><input type="checkbox" id="project_is_lucratif" name="project_is_lucratif" /><br/>
    </p>
    <p>
        <label for="project_is_searching_people">Le projet cherche de nouveaux profils </label><input type="checkbox" id="project_is_searching_people" name="project_is_searching_people" />
    </p>
    <p class="project-submition-form-submit">
        <input type="submit" name="project-send" value="Envoyer" />
    </p>
</form>
<?php
    }

    // This function allows us to set a featured image to the WordPress Post
    public static function set_post_featured_image($project) {
        // We want to do this only if there is actually a banner
        if (!isset($project->bannerUrl) || empty($project->bannerUrl)) {
            return;
        }

        $image_url        = $project->bannerUrl; // Define the image URL here
        $image_name       = basename($project->bannerUrl);
        $upload_dir       = wp_upload_dir(); // Set upload folder
        $image_data       = file_get_contents($image_url); // Get image data
        $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
        $filename         = basename( $unique_file_name ); // Create image file name

        // Check folder permission and define file location
        if( wp_mkdir_p( $upload_dir['path'] ) ) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }

        // Create the image  file on the server
        file_put_contents($file, $image_data);

        // Check image file type
        $wp_filetype = wp_check_filetype($filename, null);

        // Set attachment data
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => sanitize_file_name($filename),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        // Create the attachment
        $attach_id = wp_insert_attachment($attachment, $file, $project->id);

        // Include image.php
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Define attachment metadata
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);

        // Assign metadata to attachment
        wp_update_attachment_metadata($attach_id, $attach_data);

        // And finally assign featured image to post
        set_post_thumbnail($project->id, $attach_id);
    }

    // We add the post on the site
    public static function submit_wordpress_post($project) {
        $new_post_id = wp_insert_post($project->to_wp_post(), true);

		if (is_wp_error($new_post_id)) {
            // throw new Exception($new_post_id->get_error_message(), 1);
            echo ($new_post_id->get_error_message());
        }
        
        $project->id = $new_post_id;
        self::set_post_featured_image($project);

        // We clean the post to ensure the project is not submited twice
        self::$statusMessage = "Votre projet à bien été publié !";
        $_POST = array();
    }

    // We show the project in the corresponding channel on the Discord server
    public static function send_discord_message($project) {
        $url = $project->webhook;
        $hookObject = $project->to_discord_post();

        $ch = curl_init();

        curl_setopt_array( $ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $hookObject,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ]
        ]);

        $response = curl_exec( $ch );
        curl_close( $ch );
    }
}

// Register the shortcode for Wordpress
function ntf_projects_shortcode() {
    ob_start();

    WidgetProjectForm::project_submit();
    WidgetProjectForm::project_form();

    return ob_get_clean();
}

add_shortcode('ntf_projects_form', 'ntf_projects_shortcode' );

?>