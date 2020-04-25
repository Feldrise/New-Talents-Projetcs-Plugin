<?php 
// This widget allow users to submit a project in the Discord and in the site
class WidgetProjectForm {
    public static $channels = array(); // We store channels in an array

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
            $project = new NtfProject(
                $_POST['project_name'],                                        // The project name
                self::$channels[$_POST['project_category']]->category_id,      // The project category id on New Talents site
                self::$channels[$_POST['project_category']]->webhook,          // The project category webhook on Discord
                $_POST['project_description']                                  // The project description
            );

            // We add the post on the site
            self::submit_wordpress_post($project);

            // We send the embed to Discord
            self::send_discord_message($project);
        }
    }

    // This is the form for the project submition.
    public static function project_form() {
?>
<form class="project-submition-form" method="post" action="#">
    <p class="project-submition-form-select">
        <select id="project_category" name="project_category">
<?php
        // We show each channels on the select
        foreach (self::$channels as $key => $channel) {
            echo('<option value=' . $key . '>' . $channel->name . '</option>');
        }
?>
        </select>
    </p>
    <p>
        <label>Nom du projet : </label><input type="text" id="project_name" name="project_name" placeholder="Nom du projet" />
        <label>Description du projet : </label><textarea id="project_description" name="project_description" placeholder="Description du projet" row="4"></textarea>
    </p>
    <p class="project-submition-form-submit">
        <input type="submit" name="project-send" value="Envoyer" />
    </p>
</form>
<?php
    }

    // We add the post on the site
    public static function submit_wordpress_post($project) {
        $new_post_id = wp_insert_post($project->to_wp_post(), true);

		if (is_wp_error($new_post_id)) {
            // throw new Exception($new_post_id->get_error_message(), 1);
            echo ($new_post_id->get_error_message());
        }
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