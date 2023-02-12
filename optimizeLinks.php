<?php
ini_set('session.gc_maxlifetime', 3600);
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');


// Get options for link creation / removal
$addbold = get_option('doBold', false);
$maxLinksInPost = get_option('InternalLinkerMAXLinks', 1);
$whatToOptimize = get_option('whatToOptimize', 2);
$allCats = get_option('optimizeAllCategories', true);
$specificCats = get_option('optimizeSpecificCategories', []);
$focusWordType = get_option('focusWordType', "plugin");
$ShowInternalPingBacks = get_option('ShowInternalPingBacks', true);



switch ($whatToOptimize) {
    case 0:
        $post_type = ['page'];
        break;
    case 1:
        $post_type = ['post'];
        break;
    case 2:
        $post_type = ['post', 'page'];
        break;

    default:
        $post_type = ['post', 'page'];
        break;
}

$optimizeAllCategories = get_option('optimizeAllCategories', true);
$optimizeSpecificCategories = get_option('optimizeSpecificCategories', []);

// End Get options


if (isset($_POST['action']) && $_POST['action'] === 'optimizeLinks') {

    // While skipping isn't used for this version of the plugin, it is implemented here for future versions, 
    // in case optimization jobs will be too big to be preformed in one go.

    if ((isset($_POST['skip'])) && ((is_int($_POST['skip']))  || (is_int(intval($_POST['skip']))))) {
        $skip = intval($_POST['skip']);
    } else {
        $skip = 0;
    }

    if (isset($_POST['newID'])) {
        $newID = $_POST['newID'];
    } else {
        $newID = null;
    }

    if (isset($_SESSION[$newID])) {
        var_dump($_SESSION[$newID]);
    }
    // call the optimizeLinks function here
    optimizeLinks($maxLinksInPost, $post_type, $allCats, $specificCats, $addbold, $skip, $focusWordType, $newID);
} else if ((isset($_POST['action']) && $_POST['action'] === 'removeAllLinks')) {

    // While skipping isn't used for this version of the plugin, it is implemented here for future versions, 
    // in case optimization jobs will be too big to be preformed in one go.


    if ((isset($_POST['skip'])) && ((is_int($_POST['skip']))  || (is_int(intval($_POST['skip']))))) {
        $skip = intval($_POST['skip']);
    } else {
        $skip = 0;
    }
    // call the remove links function here
    removeAllInternalLinks($skip);
} else if ((isset($_POST['change'])) && (isset($_POST['values']))) {

    $values = json_decode(stripslashes($_POST['values']), true);

    // call the change settings function here
    changeValues($values);
}

function changeValues($values) // The change settings function
{
    $success = true;
    if (isset($values['max_links'])) {
        ctype_digit($values['max_links']);
        if (($values['max_links'] >= 0) && ($values['max_links'] <= 10)) {
            update_option('InternalLinkerMAXLinks', $values['max_links']);
        } else {
            $success = false;
        }
    } else {
        $success = false;
    }
    if (isset($values['what_to_optimize'])) { // Optmize Posts, Pages or both
        ctype_digit($values['what_to_optimize']);

        if (($values['what_to_optimize'] >= 0) && ($values['what_to_optimize'] < 4)) {
            update_option('whatToOptimize', $values['what_to_optimize']);
        } else {
            $success = false;
        }
    } else {
        $success = false;
    }
    if (isset($values['all_categories'])) { // Add Internal Links to all categories or just selected categories
        if ($values['all_categories']) {
            update_option('optimizeAllCategories', true);
            update_option('optimizeSpecificCategories', []);
        } else if ((isset($values['specific_categories']))) {
            update_option('optimizeAllCategories', false);
            update_option('optimizeSpecificCategories', $values['specific_categories']);
        } else {
            $success = false;
        }
    } else {
        $success = false;
    }
    if (isset($values['focusWordType'])) { // Currently word type options are a special meta field created by the plugin or YOAST focus word
        if (($values['focusWordType'] == 'plugin') || ($values['focusWordType'] == 'yoast')) {
            update_option('focusWordType', $values['focusWordType']);
        } else {
            $success = false;
        }
    } else {
        $success = false;
    }
    if (isset($values['doBold'])) { // Make Internal links bold (actually adds <strong> tags inside links, styling is up to the admin)
        if ($values['doBold']) {
            update_option('doBold', true);
        } else {
            update_option('doBold', false);
        }
    } else {
        $success = false;
    }

    if (isset($values['ShowInternalPingBacks'])) { // Allow pingbacks for Internal links. Default is false to prevent an influx of pingbacks between posts.
        if ($values['ShowInternalPingBacks']) {
            update_option('ShowInternalPingBacks', true);
        } else {
            update_option('ShowInternalPingBacks', false);
        }
    } else {
        $success = false;
    }

    if ($success) {
        echo "success";
    }
}



function removeAllInternalLinks($skip = 0)

// skip is currently not functional, as it is always = 0. This is created in advance to support future versions.

{


    $posts = get_posts(array(
        'posts_per_page' => -1,
        'post_type'    => array('post', 'page'),
    ));

    $howManyPostsInTotal = count($posts);
    echo $howManyPostsInTotal . " Total Posts to clean (" . $skip . " Done)<br>";
    flush();
    ob_flush();
    $posts = array_slice($posts, $skip);


    $numberOfPosts = count($posts);

    $SendAtTheEnd = "success";

    $countPosts = 0;
    foreach ($posts as $post) {

        echo "Processing... " . $post->ID;

        $replacements = 0;

        $content = preg_replace('/<a\b[^>]*data-madeby="internallinker"[^>]*>(.*?)<\/a>/', '$1', $post->post_content, -1, $replacements);

        $content = preg_replace('/<strong\b[^>]*data-madeby="internallinker"[^>]*>(.*?)<\/strong>/', '$1', $content);

        echo " - " . $replacements . " links removed" . "<br>";
        flush();
        ob_flush();

        $updateWordpressArray[] = array('ID' => $post->ID, 'content' => $content);

        $countPosts++;

        if ($countPosts > 19) {
            // Will be used in future versions, if skip is enabled.
        }
    } // End foreach post

    global $wpdb;
    // Makes a single DB push for all changes.
    $wpdb->query('START TRANSACTION');
    foreach ($updateWordpressArray as $singlePost) {
        // var_dump($singlePost);
        $wpdb->update(
            $wpdb->posts,
            array('post_content' => $singlePost['content']),
            array('ID' => $singlePost['ID']),
            array('%s'),
            array('%d')
        );
    }

    // Commit the transaction
    $wpdb->query('COMMIT');

    echo "|||" . $SendAtTheEnd;
}



function optimizeLinks($maxLinksInPost, $post_type, $allCats, $specificCats, $addbold, $skip = 0, $focusWordType, $newID)
// skip is currently not functional, as it is always = 0. This is created in advance to support future versions.
{

    // create the pattern to match H tags (shouldn't be optimized)
    $h_tag_pattern = '/<h[1-6][^>]*>(.*?)<\/h[1-6]>/i';
    $a_tag_pattern = '/<a[^>]*>(.*?)<\/a>/i';

    $addboldStart = '<strong data-madeby="internallinker">';
    $addboldEnd = "</strong>";

    ini_set('max_execution_time', 6000); // Sets high execution time!

    if ((isset($_SESSION[$newID])) && (!is_null($_SESSION[$newID]))) {
        $map1 = $_SESSION[$newID];
    } else {


        if ($allCats) {
            $posts = get_posts(array(
                'post_type'    => $post_type,
                'posts_per_page' => -1,

            ));
        } else {
            $specificCats = array_map('intval', $specificCats);

            $posts = get_posts(array(
                'post_type'    => $post_type,
                'posts_per_page' => -1,
                'category__in' => $specificCats

            ));
        }

        // create a map of posts and yoast/plugin keyphrases
        $map1 = array();

        foreach ($posts as $post) {

            if ($focusWordType == 'yoast') {

                $keyphrases = get_post_meta($post->ID, '_yoast_wpseo_focuskw', true);
            } else {

                $keyphrases = get_post_meta($post->ID, 'internalLinkerFocusWord', true);
            }

            if ($keyphrases) {
                $keyphrases = explode(',', $keyphrases);
                foreach ($keyphrases as $keyphrase) {
                    $map1[$keyphrase] = $post->ID;
                }
            }
        }
        try {
            // $_SESSION[$newID] = $map1;
            // Used for future version
        } catch (\Throwable $th) {
            // echo "there was a problem here";
        }
    }

    if (isset($_SESSION[$newID . "posts"])) {
        // Used for future version
        // $posts = $_SESSION[$newID . "posts"];
    } else {

        $posts = get_posts(array(
            'post_type'    => $post_type,
            'posts_per_page' => -1,
            'category__in' => $specificCats,
        ));
        // $_SESSION[$newID . "posts"] = $posts;
        // Used for future version
    }

    $howManyPostsInTotal = count($posts);
    echo $howManyPostsInTotal . " Total Posts <br>";
    flush();
    ob_flush();
    
    $SendAtTheEnd = "success";

    // go over all the posts and link to the original post linked to the yoast keyword / plugin keyword on map1

    $updateWordpressArray = [];
    $countPosts = 0;
    $posts = casttoclass('stdClass', $posts);
    foreach ($posts as $post) {
        
        if ($skip == 20) {
            // used for future version
        }
        $countLinksAdded = 0;
        echo "Processing... " . $post->ID;
        
        if ($skip == 20) {
            // used for future version
        }
        $content = preg_replace('/<a\b[^>]*data-madeby="internallinker"[^>]*>(.*?)<\/a>/', '$1', $post->post_content);

        $content = preg_replace('/<strong\b[^>]*data-madeby="internallinker"[^>]*>(.*?)<\/strong>/', '$1', $content);

        $dupContent = $content;



        $linked_posts = array();

        $countInternalLinksInPost = 0;

        $allMatchesInPost = [];

        foreach ($map1 as $keyphrase => $linked_post_id) {

            if ($linked_post_id !== $post->ID) {


                
                preg_match_all("/\b" . preg_quote($keyphrase, '/') . "\b/i", $content, $matches, PREG_OFFSET_CAPTURE); // /i - case insensitive \b on both sides for full words only
                
                foreach ($matches[0] as $match) {
                    $match_start = $match[1];
                    $match_end = $match_start + strlen($keyphrase);

                    // check if the match is between H tags (not optimizing Headlines)
                    $is_between_h_tags = false;

                    if (preg_match_all($h_tag_pattern, $content, $h_tag_matches, PREG_OFFSET_CAPTURE)) {
                        foreach ($h_tag_matches[0] as $h_tag_match) {
                            $h_tag_start = $h_tag_match[1];
                            $h_tag_end = $h_tag_start + strlen($h_tag_match[0]);

                            if ($match_start > $h_tag_start && $match_end < $h_tag_end) {
                                $is_between_h_tags = true;
                                break;
                            }
                        }
                    }

                    // check if the match is between a tags
                    $is_between_a_tags = false;

                    if (preg_match_all($a_tag_pattern, $content, $a_tag_matches, PREG_OFFSET_CAPTURE)) {
                        foreach ($a_tag_matches[0] as $a_tag_match) {
                            $a_tag_start = $a_tag_match[1];
                            $a_tag_end = $a_tag_start + strlen($a_tag_match[0]);

                            if ($match_start > $a_tag_start && $match_end < $a_tag_end) {
                                $is_between_a_tags = true;
                                break;
                            }
                        }
                    }
                    // replace the match if it isn't between H tags
                    if ((!$is_between_h_tags) && (!$is_between_a_tags)) {

                        $allMatchesInPost[] = array('match_start' => $match_start, 'keyphrase' => $keyphrase, 'linked_post_id' => $linked_post_id);
                    }
                } // End foreach ($matches[0] as $match) 
            } // End if ($linked_post_id !== $post->ID)
        } // End foreach ($map1 as $keyphrase => $linked_post_id)

        $NumberOfMatches = count($allMatchesInPost);
        if ($maxLinksInPost > 1) { // No Point to sort if only one match required
            usort($allMatchesInPost, function ($a, $b) {
                return $a['match_start'] - $b['match_start'];
            });
        }

        if ($NumberOfMatches > $maxLinksInPost) {
            array_splice($allMatchesInPost, $maxLinksInPost - $NumberOfMatches); // remove the last elements so we won't exceed maxlinksinpost.
        }
        $allMatchesInPost = array_reverse($allMatchesInPost); // better to start from the end to avoid shifting


        $content = $dupContent;

        foreach ($allMatchesInPost as $currentMatch) {




            // echo 'In Post ' . $post->ID . ' Replacing ' . $keyphrase . ' with ' . '<a data-madeby="internallinker" href="' . get_permalink($linked_post_id) . '">' . $keyphrase . '</a>' . '<br>';

            if ($addbold) {
                $beginBold = $addboldStart;
                $endBold = $addboldEnd;
            } else {
                $beginBold = "";
                $endBold = "";
            }

            $content = substr_replace($content, '<a data-madeby="internallinker" href="' . get_permalink($currentMatch['linked_post_id']) . '">'
                . $beginBold
                . $currentMatch['keyphrase']
                . $endBold
                . '</a>', $currentMatch['match_start'], strlen($currentMatch['keyphrase']));
            $countInternalLinksInPost++;
        } // End foreach phrase in the post


        echo " - " . count($allMatchesInPost) . " links added" . "<br>";
        flush();
        ob_flush();

        $post->post_content = $content;

        $updateWordpressArray[] = array('ID' => $post->ID, 'content' => $content);




        // wp_update_post($post); Previous Way...

        // show_Admin_notice();
        $countPosts++;
        // var_dump($countPosts);

        if ($countPosts > 19) {
            // break;
        }
    } // End foreach post
    // echo json_encode(array("status" => "success", "message" => "Action was successful"));

    global $wpdb;
    $wpdb->query('START TRANSACTION');
    foreach ($updateWordpressArray as $singlePost) {
        // var_dump($singlePost);
        $wpdb->update(
            $wpdb->posts,
            array('post_content' => $singlePost['content']),
            array('ID' => $singlePost['ID']),
            array('%s'),
            array('%d')
        );
    }

    // Commit the transaction
    $wpdb->query('COMMIT');

    echo "|||" . $SendAtTheEnd;
}
function casttoclass($class, $object)
{
    return unserialize(preg_replace('/^O:\d+:"[^"]++"/', 'O:' . strlen($class) . ':"' . $class . '"', serialize($object)));
}
