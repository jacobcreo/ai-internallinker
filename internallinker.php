<?php
/*
Plugin Name: AI Internal Linker
Plugin URI: 
Description: Creating Internal Links between posts to Improve your seo
Author: Koby Ofek
Author URI: kobyofek.com
Version: 1.0
*/

function create_submenu_callback()
// This function creates everything in the Settings page for the plugin
{
?>
    <script>
        // Used to make unwanted popups disapear
        document.querySelectorAll('.notice').forEach(function(notice) {
            if (!notice.classList.contains('internallinker')) {
                notice.style.display = 'none';
            }
        });
    </script>

    <style>
        <?php /* Styling info for the plugin */ ?>#progress {
            max-width: 480px;
            background: black;
            margin-top: 10px;
            padding: 5px;
            font-size: 110%;
            color: white;
            border: 4px;
            border-color: gold;
            border-style: double;
            min-height: 5em;
            max-height: 20em;
            overflow-y: scroll;
        }

        ::-webkit-scrollbar {
            width: 10px;
        }

        /* Track */
        #progress ::-webkit-scrollbar-track {
            background: black;
        }

        /* Handle */
        #progress ::-webkit-scrollbar-thumb {
            background: gold;
        }

        /* Handle on hover */
        #progress ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        #progress ::-webkit-scrollbar-corner {
            background: black;
        }

        #successMSG {
            padding: 10px;
        }
    </style>


    <?php /* Success / Failure Messages for link optimization process */ ?>
    <div id="successMSG" class="internallinker notice notice-success is-dismissible" style="display:none">
        <p id="psuccessMSG">Links optimized successfully!</p>
    </div>



    <div class="wrap">

        <?php /* Settings menu for the plugin */ ?>
        <h1><?php _e('Optimize Links', 'textdomain'); ?></h1>
        <?php display_internal_linker_form(); ?>
        <p><?php _e('Press the button to start optimizing links', 'textdomain'); ?></p>
        <button id="optimize-button" class="button-primary">
            <?php _e('Optimize Links', 'textdomain'); ?>
        </button>
        <button id="removeLinks-button" class="button-primary">
            <?php _e('Remove All Internal Links', 'textdomain'); ?>
        </button>
        <div id="spinner" style="display:none;">
            <img src="<?php echo admin_url('/images/spinner.gif'); ?>" alt="Spinner">
        </div>
        <div id="progress"></div>
    </div>

    <script>

        function callLinkRemoval(skip) {
            <?php /* Ajax call to remove all links already enabled */ ?>

            const xhr = new XMLHttpRequest();
            var totalLength = 0;
            xhr.open("POST", "<?php echo plugins_url('/ai-internallinker/optimizeLinks.php'); ?>", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {

                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = xhr.responseText.slice(totalLength + 3);
                    if (response === "success") {
                        document.getElementById("successMSG").innerHTML = "All Internal Links Removed";
                        document.getElementById("successMSG").style.display = "block";
                        document.getElementById("optimize-button").disabled = false;
                        document.querySelector('#spinner').style.display = 'none';
                        document.getElementById("submit").disabled = false;
                        document.getElementById("optimize-button").disabled = false;
                        document.querySelector('#removeLinks-button').disabled = false;
                        


                    } else if (response === "showmore") {
                        callLinkRemoval(skip + 20);
                    } else {
                        document.getElementById("successMSG").innerHTML = response;
                        document.getElementById("successMSG").innerHTML = "There was a problem with Links Removal";
                        document.getElementById("successMSG").style.display = "block";
                        document.getElementById("submit").disabled = false;
                        document.getElementById("optimize-button").disabled = false;
                        document.querySelector('#removeLinks-button').disabled = false;
                        document.querySelector('#spinner').style.display = 'none';

                        
                    }

                } else if ((xhr.readyState === 3) && (!xhr.responseText.includes("|||"))) {
                    
                    document.getElementById("progress").innerHTML = xhr.responseText;
                    totalLength = xhr.responseText.length;


                }
            };
            
            xhr.onerror = function() {
                if (xhr.status === 0) {
                    if (retryCount < 3) {
                        // Retry the request
                        retryCount++;
                        callOptimization(skip);
                    } else {
                        // Stop trying after 3 attempts
                        console.error('Failed to load resource');
                    }
                }
            };
            xhr.send("action=removeAllLinks&skip=" + skip);


        }

        var retryCount = 0;

        function callOptimization(skip, newID) {

            <?php /* Ajax call to start creating internal links */ ?>


            const xhr = new XMLHttpRequest();
            var totalLength = 0;
            xhr.open("POST", "<?php echo plugins_url('/ai-internallinker/optimizeLinks.php'); ?>", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {

                

                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = xhr.responseText.slice(totalLength + 3);
                    if (response === "success") {
                        document.getElementById("successMSG").innerHTML = "Links Optimized Successfully";
                        document.getElementById("successMSG").style.display = "block";
                        document.getElementById("optimize-button").disabled = false;
                        document.querySelector('#spinner').style.display = 'none';
                        document.getElementById("submit").disabled = false;
                        document.getElementById("optimize-button").disabled = false;
                        document.querySelector('#removeLinks-button').disabled = false;
                        

                    } else if (response === "showmore") {
                        callOptimization(skip + 20, newID);
                    } else {
                        document.getElementById("successMSG").innerHTML = response;
                        document.getElementById("successMSG").innerHTML = "There was a problem with Links Optimization";
                        document.getElementById("successMSG").style.display = "block";
                        document.getElementById("submit").disabled = false;
                        document.getElementById("optimize-button").disabled = false;
                        document.querySelector('#removeLinks-button').disabled = false;
                        document.querySelector('#spinner').style.display = 'none';

                        
                    }

                } else if ((xhr.readyState === 3) && (!xhr.responseText.includes("|||"))) {
                    
                    document.getElementById("progress").innerHTML = xhr.responseText;
                    totalLength = xhr.responseText.length;


                }
            };
            
            xhr.onerror = function() {
                if (xhr.status === 0 && xhr.statusText === 'net::ERR_NETWORK_CHANGED') {
                    if (retryCount < 3) {
                        // Retry the request
                        retryCount++;
                        callOptimization(skip, newID);
                    } else {
                        // Stop trying after 3 attempts
                        console.error('Failed to load resource: net::ERR_NETWORK_CHANGED');
                    }
                } else {
                    console.error('Some Sort Of Error. Sorry. Maybe try again later.');
                    // Handle other error cases
                }
            };
            xhr.send("action=optimizeLinks&newID=" + newID + "&skip=" + skip);


        }


        // Optimize Button logic
        document.querySelector('#optimize-button').addEventListener('click', function() {
            var skip = 0;
            this.setAttribute('disabled', true);
            document.getElementById("submit").disabled = true;
            document.querySelector('#removeLinks-button').disabled = true;
            document.getElementById("successMSG").style.display = "none";
            document.querySelector('#spinner').style.display = 'inline-block';
            retryCount = 0
            var newID = generateId();
            callOptimization(0, newID);

        });


        // End Optimization Button logic.


        // removeLinks Button logic
        document.querySelector('#removeLinks-button').addEventListener('click', function() {
            var skip = 0;
            this.setAttribute('disabled', true);
            document.getElementById("optimize-button").disabled = true;
            document.getElementById("submit").disabled = true;

            document.getElementById("successMSG").style.display = "none";
            document.querySelector('#spinner').style.display = 'inline-block';
            retryCount = 0
            callLinkRemoval(0);
        });

        function generateId() {
            <?php /* ID will be used for a future version when big jobs will be spilt to smaller tasks */ ?>
            // Get the current timestamp
            var timestamp = new Date().getTime();

            // Convert the timestamp to a string
            var timestampString = timestamp.toString();

            // Generate a random number based on the timestamp
            var randomNumber = Math.floor(Math.random() * timestampString.length);

            // Concatenate the random number with the timestamp string
            var id = randomNumber + timestampString;

            // Return the alphanumeric string
            return id;
        }

    </script>
<?php
}




function display_internal_linker_form()
    // This function actually displays the settings form
{

?>

    <form id="optimize-links-form">
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="max-links">Max links to update</label></th>
                <td><input name="max-links" type="text" id="max-links" value=<?php echo get_option('InternalLinkerMAXLinks', 3); ?> class="regular-text" /></td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="what_to_optimize">What to optimize?</label>
                </th>
                <td>
                    <select name="what_to_optimize" id="what_to_optimize">
                        <option value="0" <?php if (get_option('whatToOptimize', 1) == 0) {
                                                echo 'selected="selected"';
                                            } ?>>Pages</option>
                        <option value="1" <?php if (get_option('whatToOptimize', 1) == 1) {
                                                echo 'selected="selected"';
                                            } ?>>Posts</option>
                        <option value="2" <?php if (get_option('whatToOptimize', 1) == 2) {
                                                echo 'selected="selected"';
                                            } ?>>Posts & Pages</option>
                    </select>
                </td>
            </tr>


            <tr>
                <th scope="row">
                    <label for="FocusWordType">Focus Word to Use:</label>
                </th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><span>Date Format</span></legend>
                        <label><input type="radio" name="FocusWordType" value="plugin" <?php if (get_option('focusWordType') == 'plugin') {
                                                                                            echo 'checked="checked"';
                                                                                        } ?>> <span class="">Internal Linker Meta Field</span></label><br>
                        <label><input type="radio" name="FocusWordType" value="yoast" <?php if (get_option('focusWordType') == 'yoast') {
                                                                                            echo 'checked="checked"';
                                                                                        } ?>><span class="">YOAST Focus Keyword</span></label><br>

                        </p>
                    </fieldset>
                </td>
            </tr>


            <tr>
                <th scope="row">
                    <label for="apply_to_all_categories">Apply to all categories:</label>
                </th>
                <td>
                    <input type="checkbox" name="apply_to_all_categories" id="apply_to_all_categories" <?php if (get_option('optimizeAllCategories', true)) {
                                                                                                            echo "checked";
                                                                                                        } ?> />
                </td>
            </tr>




            <tr>
                <th scope="row">
                    <label for="selected_categories">Selected categories:</label>
                </th>
                <td>
                    <select name="selected_categories[]" id="selected_categories" multiple <?php if (get_option('optimizeAllCategories', true)) {
                                                                                                echo 'disabled';
                                                                                            } ?>>
                        <?php $categories = get_categories();
                        $selected_categories = get_option('optimizeSpecificCategories', []);
                        foreach ($categories as $category) {
                            $selected = in_array($category->term_id, $selected_categories) ? 'selected' : '';
                            echo '<option value="' . $category->term_id . '" ' . $selected . '>' . $category->name . '</option>';
                        } ?>

                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="doBold">Emphasize Internal Links:</label>
                </th>
                <td>
                    <input type="checkbox" name="doBold" id="doBold" <?php if (get_option('doBold', true)) {
                                                                            echo "checked";
                                                                        } ?> />
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="ShowInternalPingBacks">Allow Pingbacks from Internal Links:</label>
                </th>
                <td>
                    <input type="checkbox" name="ShowInternalPingBacks" id="ShowInternalPingBacks" <?php if (get_option('ShowInternalPingBacks', true)) {
                                                                                                        echo "checked";
                                                                                                    } ?> />
                </td>
            </tr>

            <script>
                document.getElementById("apply_to_all_categories").addEventListener("change", function() {
                    var selectedCategories = document.getElementById("selected_categories");
                    selectedCategories.disabled = this.checked;

                    if (this.checked) {
                        selectedCategories.selectedIndex = -1;
                    }
                });
            </script>


        </table>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes" /></p>
    </form>

    <script>
        document.getElementById("optimize-links-form").addEventListener("submit", function(e) {
            e.preventDefault();
            document.getElementById("successMSG").style.display = "none";
            document.getElementById("optimize-button").disabled = true;
            document.getElementById("submit").disabled = true;
            document.getElementById("removeLinks-button").disabled = true;

            var values = {
                "max_links": document.getElementById("max-links").value,
                "what_to_optimize": document.getElementById("what_to_optimize").value,
                "all_categories": document.querySelector('#apply_to_all_categories').checked,
                "specific_categories": [...document.querySelectorAll('#selected_categories option:checked')].map(opt => opt.value),
                "focusWordType": document.querySelector('input[name="FocusWordType"]:checked').value,
                "doBold": document.querySelector('#doBold').checked,
                "ShowInternalPingBacks": document.querySelector('#ShowInternalPingBacks').checked


            };

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "<?php echo plugins_url('/ai-internallinker/optimizeLinks.php'); ?>", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                    // Handle successful response here
                    document.getElementById("successMSG").innerHTML = "Changes Saved Successfully"
                    document.getElementById("successMSG").style.display = "block";
                    document.getElementById("optimize-button").disabled = false;
                    document.getElementById("submit").disabled = false;
                    document.getElementById("removeLinks-button").disabled = false;


                } else {
                    document.getElementById("successMSG").innerHTML = "There was a problem with Saving Changes";
                    document.getElementById("successMSG").style.display = "block";
                    document.getElementById("optimize-button").disabled = false;
                    document.getElementById("submit").disabled = false;
                    document.getElementById("removeLinks-button").disabled = false;
                    document.querySelector('#spinner').style.display = 'none';
                }
            };
            xhr.send("change=true&values=" + JSON.stringify(values));
        });
    </script>


<?php
}


function my_custom_admin_menu()
/* Adds the Settings sub-menu item to the setting menu in Admin Dashboard */
{
    add_submenu_page(
        'options-general.php',
        'Internal Linker',
        'Internal Linker',
        'manage_options',
        'internal-linker',
        'create_submenu_callback'
    );
}

add_action('admin_menu', 'my_custom_admin_menu');

function show_Admin_notice()
/* Creates a notification bar for showing messages */
{
?>
    <div class="notice notice-success is-dismissible" style="display:none">
        <p><?php _e('Links optimized successfully!', 'textdomain'); ?></p>
    </div>
    <script>
        document.querySelector('#optimize-button').removeAttribute('disabled');
        document.querySelector('#spinner').style.display = 'none';
    </script>
<?php
}

add_action('admin_notices', 'show_admin_notice');

function createDBOptions()
/* Creates DB Options for the plugin */
{
    add_option('InternalLinkerMAXLinks', 1);
    add_option('whatToOptimize', 1);
    add_option('optimizeAllCategories', true);
    add_option('optimizeSpecificCategories', []);
    add_option('focusWordType', 'plugin');
    add_option('doBold', false);
    add_action('add_meta_boxes', 'add_internalLinkerFocusWord');
    add_action('save_post', 'save_internalLinkerFocusWord');
    add_action('ShowInternalPingBacks', true);
}

function add_internalLinkerSettings($plugin_actions, $plugin_file)
{

    $new_actions = array();

    if (basename(plugin_dir_path(__FILE__)) . '/internallinker.php' === $plugin_file) {
        $new_actions['cl_settings'] = sprintf(__('<a href="%s">Settings</a>', 'internal-linker'), esc_url(admin_url('options-general.php?page=internal-linker')));
    }

    return array_merge($new_actions, $plugin_actions);
}
add_filter('plugin_action_links', 'add_internalLinkerSettings', 11, 2);

register_activation_hook(__FILE__, 'createDBOptions');

register_deactivation_hook(__FILE__, 'uninstall_meta_box');

function uninstall_meta_box()
{
    remove_meta_box('internalLinkerFocusWord', 'post', 'side');
}

function add_internalLinkerFocusWord()
{
    add_meta_box(
        'internalLinkerFocusWord', // Unique ID
        'Focus Word', // Title
        'display_internalLinkerFocusWord', // Callback function
        'post', // Screen to which to add the meta box
        'side', // Context
        'high' // Priority
    );
}
add_action('add_meta_boxes', 'add_internalLinkerFocusWord');

function display_internalLinkerFocusWord($post)
/* Shows meta box in Edit posts/pages pages for Focus Word */
{
    // Retrieve the current value of the custom field
    $value = get_post_meta($post->ID, 'internalLinkerFocusWord', true);

    // Output the custom field
?>
    <label for="internalLinkerFocusWord">Custom Field:</label>
    <input type="text" id="internalLinkerFocusWord" name="internalLinkerFocusWord" value="<?php echo esc_attr($value); ?>">
<?php
}

function save_internalLinkerFocusWord($post_id)
{
    // Check if the custom field has been submitted
    if (isset($_POST['internalLinkerFocusWord'])) {
        // Save the custom field
        update_post_meta($post_id, 'internalLinkerFocusWord', sanitize_text_field($_POST['internalLinkerFocusWord']));
    }
}
add_action('save_post', 'save_internalLinkerFocusWord');

function disable_internal_pingbacks(&$links)
{
    foreach ($links as $l => $link)
        if (0 === strpos($link, get_option('home')))
            unset($links[$l]);
}

if (get_option('ShowInternalPingBacks', true)) {
    add_action('pre_ping', 'disable_internal_pingbacks');
}

register_uninstall_hook(__FILE__, 'removeDBOptions');
function removeDBOptions()
// cleaning up after the plugin
{
    delete_option('InternalLinkerMAXLinks');
    delete_option('whatToOptimize');
    delete_option('optimizeAllCategories');
    delete_option('optimizeSpecificCategories');
    delete_option('focusWordType');
    delete_option('doBold');
    remove_action('add_meta_boxes', 'add_internalLinkerFocusWord');
    remove_action('save_post', 'save_internalLinkerFocusWord');
    remove_action('ShowInternalPingBacks', true);








    remove_action('pre_ping', 'disable_internal_pingbacks');

remove_action('admin_menu', 'my_custom_admin_menu');

remove_action('admin_notices', 'show_admin_notice');
}
