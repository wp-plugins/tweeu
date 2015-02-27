<?php
@session_start();

require 'twitteroauth/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;

require_once('tweeu_config.php');

$prereqOK = true;

if (!function_exists('json_decode')) {
	add_action('admin_notices', showAdminMessage('Your php installation doesn\'t support the <a href="http://us.php.net/json_decode">json_decode</a> functionality.', true));
	$prereqOK = false;
}

if (!function_exists('curl_exec')) {
	add_action('admin_notices', showAdminMessage('Your php installation doesn\'t include <a href="http://us.php.net/manual/en/ref.curl.php">curl</a> support.', true));
	$prereqOK = false;
}

if (get_option('Tweeu_initialised') != '1') {
	update_option('Tweeu_newpost-published-update', '1');
	update_option('Tweeu_newpost-published-text', 'Published a new blog post: #title#');
	update_option('Tweeu_newpost-published-showlink', '1');
	update_option('Tweeu_newpost-published-skippages', '1');

	update_option('Tweeu_oldpost-edited-update', '1');
	update_option('Tweeu_oldpost-edited-text', 'Fiddling with my blog post: #title#');
	update_option('Tweeu_oldpost-edited-showlink', '1');
	update_option('Tweeu_oldpost-edited-skippages', '1');

	update_option('Tweeu_usehashtags', '');
	update_option('Tweeu_usehashtags-cats', '');
	update_option('Tweeu_usehashtags-tags', '');
	update_option('Tweeu_usehashtags-static', '');

	update_option('Tweeu_initialised', '1');
}

if ($_POST['submit-type'] == 'options') {
	update_option('Tweeu_newpost-published-update', $_POST['newpost-published-update']);
	update_option('Tweeu_newpost-published-text', $_POST['newpost-published-text']);
	update_option('Tweeu_newpost-published-showlink', $_POST['newpost-published-showlink']);
	update_option('Tweeu_newpost-published-skippages', ($_POST['newpost-published-skippages'] == 1) ? 1 : 0);

	update_option('Tweeu_oldpost-edited-update', $_POST['oldpost-edited-update']);
	update_option('Tweeu_oldpost-edited-text', $_POST['oldpost-edited-text']);
	update_option('Tweeu_oldpost-edited-showlink', $_POST['oldpost-edited-showlink']);
	update_option('Tweeu_oldpost-edited-skippages', ($_POST['oldpost-edited-skippages'] == 1) ? 1 : 0);

	update_option('Tweeu_usehashtags', $_POST['usehashtags']);
	update_option('Tweeu_usehashtags-cats', $_POST['usehashtags-cats']);
	update_option('Tweeu_usehashtags-tags', $_POST['usehashtags-tags']);
	update_option('Tweeu_usehashtags-static', $_POST['usehashtags-static']);

	add_action('admin_notices', showAdminMessage("Post options saved.", false));
}

if (isset($_REQUEST['oauth_token']) && isset($_REQUEST['oauth_verifier'])) {
	if ($_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
		$_SESSION['oauth_status'] = 'oldtoken';
		add_action('admin_notices', showAdminMessage("Could not bind to your twitter account, please try again!", true));
	} else {
		/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
		$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

		/* Request access tokens from twitter */
		$token_credentials = $connection->oauth("oauth/access_token", array("oauth_verifier" => $_REQUEST['oauth_verifier']));

		if ($connection->getLastHttpCode() == 200) {
			update_option('Tweeu_oauthToken', $token_credentials['oauth_token']);
			update_option('Tweeu_oauthTokenSecret', $token_credentials['oauth_token_secret']);

			/* Remove no longer needed request tokens */
			unset($_SESSION['oauth_token']);
			unset($_SESSION['oauth_token_secret']);

			add_action('admin_notices', showAdminMessage("Tweeu is now connected to your Twitter account.", true));
		} else {
			add_action('admin_notices', showAdminMessage("Could not bind to your twitter account, please try again!", true));
		}
	}
}

function vc_checkCheckbox($theFieldname) {
	if (get_option($theFieldname) == '1') {
        	echo('checked="true"');
	}
}

?>
<style type="text/css">
    fieldset {
        margin: 20px 0;
        border: 1px solid #cecece;
        padding: 15px;
    }
</style>



<div class="wrap">
    <h2>Your TweeU options</h2>

<?php if ($prereqOK) { ?>

    <h3>Your Twitter account details</h3>

<?php $tweeu = new Tweeupdater(get_option('Tweeu_oauthToken'), get_option('Tweeu_oauthTokenSecret')); ?>

    <fieldset>
        <legend>Twitter login</legend>
        <form method="post">
            <div>
            <?php
            if (!$tweeu->twitterVerifyCredentials()) {
                add_action('admin_notices', showAdminMessage("You twitter login could not be verified!" . false));
                $callBackUrl = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?page=tweeu/tweeu.php');
                ?>
                    <p>
                    <?php
                    echo('<a href="' . WP_PLUGIN_URL . '/tweeu/tweeu_redirect.php?callback=' . $callBackUrl . '">');
                    echo('<img src="' . WP_PLUGIN_URL . '/tweeu/images/lighter.png" alt="Sign in with Twitter"/>');
                    echo('</a>');
                    ?>
                    </p>
                    <p>
                        <strong>Don't have a Twitter account? <a href="http://www.twitter.com">Get one for free
                            here!</a></strong>
                    </p>
                <?php

            } else {
                ?><p>Tweeu is successfully connected to your Twitter account. Manage your application
                    connections <a href="http://twitter.com/settings/connections">here</a>.</p><?php

            }
            ?>
            </div>
        </form>
    </fieldset>

    <h3>Post settings</h3>

    <p>
        You can use placholders in your tweet templates.
    <ul>
        <li>#title# - Placeholder for page title</li>
        <li>#firstcategory# - The name of the first category</li>
    </ul>
    </p>

    <form method="post">
        <div>
            <fieldset>
                <legend>New post published</legend>
                <p>
                    <input type="checkbox" name="newpost-published-update" id="newpost-published-update"
                           value="1" <?php vc_checkCheckbox('Tweeu_newpost-published-update')?> />
                    <label for="newpost-published-update">Update Twitter when the new post is published</label>
                </p>

                <p>
                    <label for="newpost-published-text">Text for this Twitter update</label><br/>
                    <input type="text" name="newpost-published-text" id="newpost-published-text" size="60"
                           maxlength="146" value="<?php echo(get_option('Tweeu_newpost-published-text')) ?>"/>
                    &nbsp;&nbsp;
                    <input type="checkbox" name="newpost-published-showlink" id="newpost-published-showlink"
                           value="1" <?php vc_checkCheckbox('Tweeu_newpost-published-showlink')?> />
                    <label for="newpost-published-showlink">Link title to blog?</label>
                </p>

                <p>
                    <input type="checkbox" name="newpost-published-skippages" id="newpost-published-skippages"
                           value="1" <?php vc_checkCheckbox('Tweeu_newpost-published-skippages')?> />
                    <label for="newpost-published-skippages">Skip pages</label>
                </p>
            </fieldset>
            <fieldset>
                <legend>Existing posts</legend>
                <p>
                    <input type="checkbox" name="oldpost-edited-update" id="oldpost-edited-update"
                           value="1" <?php vc_checkCheckbox('Tweeu_oldpost-edited-update')?> />
                    <label for="oldpost-edited-update">Update Twitter when the an old post has been edited</label>
                </p>

                <p>
                    <label for="oldpost-edited-text">Text for this Twitter update</label><br/>
                    <input type="text" name="oldpost-edited-text" id="oldpost-edited-text" size="60" maxlength="146"
                           value="<?php echo(get_option('Tweeu_oldpost-edited-text')) ?>"/>
                    &nbsp;&nbsp;
                    <input type="checkbox" name="oldpost-edited-showlink" id="oldpost-edited-showlink"
                           value="1" <?php vc_checkCheckbox('Tweeu_oldpost-edited-showlink')?> />
                    <label for="oldpost-edited-showlink">Link title to blog?</label>
                </p>

                <p>
                    <input type="checkbox" name="oldpost-edited-skippages" id="oldpost-edited-skippages"
                           value="1" <?php vc_checkCheckbox('Tweeu_oldpost-edited-skippages')?> />
                    <label for="oldpost-edited-skippages">Skip pages</label>
                </p>
            </fieldset>
            <fieldset>
                <legend>Hashtags</legend>
                <p>
                    <input type="checkbox" name="usehashtags" id="usehashtags"
                           value="1" <?php vc_checkCheckbox('Tweeu_usehashtags')?> />
                    <label for="usehashtags">Generate hashtags from the posts categories and/or tags</label>
                </p>

                <p>
                    <input type="checkbox" name="usehashtags-cats" id="usehashtags-cats"
                           value="1" <?php vc_checkCheckbox('Tweeu_usehashtags-cats')?>" />
                    <label for="usehashtags-cats">Use categories</label>
                    &nbsp;&nbsp;
                    <input type="checkbox" name="usehashtags-tags" id="usehashtags-tags"
                           value="1" <?php vc_checkCheckbox('Tweeu_usehashtags-tags')?>" />
                    <label for="usehashtags-tags">Use tags</label>
                </p>

                <p>
                    <label for="newpost-published-text">Add fixed hash tags(i.e. #foo #bar)</label><br/>
                    <input type="text" name="usehashtags-static" id="usehashtags-static" size="60"
                           maxlength="146" value="<?php echo(get_option('Tweeu_usehashtags-static')) ?>"/>
                </p>
            </fieldset>
            <input type="hidden" name="submit-type" value="options">
            <input type="submit" name="submit" class="button-primary" value="save options"/>
        </div>
    </form>

<?php } else { ?>
    <p><strong>The plugin is deactivated, please check error messages above!</strong></p>
<?php } ?>

</div>

