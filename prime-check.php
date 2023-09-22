<?php
/*
Plugin Name: Prime Checker
Description: To check and store prime numbers with score 
Version: 1.0
Author: Lisa
*/


function enqueue_ajax_scripts() {
    wp_enqueue_script('jquery');
}

add_action('wp_enqueue_scripts', 'enqueue_ajax_scripts');
function prime_number_ajax_scripts() {
    wp_enqueue_script('prime-check', plugin_dir_url(__FILE__) . 'prime-check.js', array('jquery'), '1.0', true);

    // Pass the AJAX URL to the script
    wp_localize_script('prime-check', 'primeCheckAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
}

add_action('wp_enqueue_scripts', 'prime_number_ajax_scripts');

function create_table()
{
    global $wpdb;

    $table_name = $wpdb->prefix.'Prime_Checker';
    $charset_collate= $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name(
        id INT(9) NOT NULL AUTO_INCREMENT,
        user_id INT(20) NOT NULL,
        numbers INT(20) NOT NULL,
        is_prime TINYINT(20) NOT NULL,
        score INT(11) NOT NULL,
        PRIMARY KEY(id)
        ) $charset_collate;";

        require_once(ABSPATH. "wp-admin/includes/upgrade.php");
        dbDelta($sql);

}
register_activation_hook(__FILE__,"create_table");

function prime_number_ajax_shortcode() {
    ob_start();
    ?>
    <div id="desc"><?php echo esc_html(get_option('prime_number_checker_div_desc','It is a fun game!'));?></div>
    <form id="prime-number-form">
        <label for="max-number"><?php echo esc_html(get_option('prime_number_checker_label_number', 'Enter a maximum number:')); ?></label>
        <input type="number" id="max-number" name="max" min="1" required>
        <button type="button" id="check"><?php echo esc_html(get_option('prime_number_checker_label_button', 'Get Prime Numbers')); ?></button>
        <!--button id="check-score">Check Score!</button-->
    </form>
    <div id="prime-numbers"></div>
    <div id="score"></div>
    <div id="totalscore"></div>
    <?php
    return ob_get_clean();
}

add_shortcode('prime_number_ajax', 'prime_number_ajax_shortcode');



// GET REQ for getting db
function db_check() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'prime_checker';
    $user_id = get_current_user_id();
    
    // Fetch data for the current user
    $data = $wpdb->get_results("SELECT numbers FROM $table_name WHERE user_id = $user_id AND is_prime = 1");
    
    // Return the data as a JSON-encoded array
    $primenumbers=array();
    foreach($data as $entry)
    {
    $primenumbers= $entry->numbers;
    
    }
    echo json_encode($primenumbers);
    wp_die(); // Terminate the script properly
}

add_action('wp_ajax_db_check', 'db_check');
add_action('wp_ajax_nopriv_db_check', 'db_check');


// GET REQ for getting score
function score_check() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'prime_checker';
    $user_id = get_current_user_id();
    


    // Calculate the total score for prime numbers for the current user
   $total_score = $wpdb->get_var("SELECT SUM(score) FROM $table_name WHERE user_id = $user_id AND is_prime = 1");
    // Return the total score as a response
    echo json_encode(array('total_score'=>$total_score));



    exit();
        // Terminate the script properly
}

add_action('wp_ajax_score_check', 'score_check');
add_action('wp_ajax_nopriv_score_check', 'score_check');


// POST REQ for storing the data in the data base
function insert_prime_number()
 {
    if($_POST['action'] == 'insert_prime_number') {
        // Insert the number into the database and return a response
        $b = intval($_POST['max']);
        $num = (int)$b;
        global $wpdb;
        $table_name = $wpdb->prefix . 'prime_checker';
        $user_id = get_current_user_id();
        // Insert the number into the database
        $data = array(
            'user_id' => $user_id,
            'numbers' => $num,
            'is_prime' => 1,
            'score' => 1, // Increment the score by 1
        );
        $wpdb->insert($table_name, $data);

            // Insert data into the database
        //$sql = "INSERT INTO prime_checker (numbers) VALUES ('$num')"; // Replace with your table name


        echo json_encode(array('success' => true));
    }
        exit();
}
add_action('wp_ajax_insert_prime_number', 'insert_prime_number');
add_action('wp_ajax_nopriv_insert_prime_number', 'insert_prime_number');




// Callback function for AJAX request
// function get_prime_numbers() {
//     // Get the max number from the AJAX request
//     $b = intval($_POST['max']);

//     if (preg_match('/^\d{1,9}$/', $b)) {
//         $num = (int)$b;

//         if ($b == 0 || $b < 0) {
//             $prime_numbers = "Enter a number greater than 0.";
//         } elseif ($b == 1) {
//             $prime_numbers = "1 is not a prime number.";
//         } else {
//             $isPrime = true;

//             for ($i = 2; $i * $i <= $b; $i++) {
//                 if ($b % $i == 0) {
//                     $isPrime = false;
//                     break;
//                 }
//             }

//             $happyemoji = "&#128512;";
//             $sadEmoji = "&#128532;";

//             if ($isPrime) {
//                 // Check if the number already exists in the database for the current user
//                 global $wpdb;
//                 $table_name = $wpdb->prefix . 'prime_checker';
//                 $user_id = get_current_user_id();

//                 $existing_record = $wpdb->get_row("SELECT id FROM $table_name WHERE user_id = $user_id");

//                 if ($existing_record && isset($existing_record->id)) {
//                     // Handle the case when the number is a duplicate (you can display a message or take other actions)
//                     $prime_numbers = "Number $num is already in your list.";
//                 } else {
//                     // If the number is not a duplicate, insert it into the database and increment the score
//                     $data = array(
//                         'user_id' => $user_id,
//                         'numbers' => $num,
//                         'is_prime' => 1,
//                         'score' => 1, // Increment the score by 1
//                     );

//                     $wpdb->insert($table_name, $data);

//                     $prime_numbers = "Yes, it is the right answer!" . $happyemoji;
//                 }

//             } else {
//                 $prime_numbers = "Oh No! Try again," . $sadEmoji;
//             }
//         }
//     } else {
//         $prime_numbers = "Invalid input";
//     }

//     echo json_encode($prime_numbers);

//     // Important: Always exit to prevent further execution
//     exit();
// }

// add_action('wp_ajax_get_prime_numbers', 'get_prime_numbers');
// add_action('wp_ajax_nopriv_get_prime_numbers', 'get_prime_numbers');


// function update_score() {
//     global $wpdb;
//     $table_name = $wpdb->prefix . 'prime_checker';
//     $user_id = get_current_user_id();
//     $score = intval($_POST['score']);
//     $max = intval($_POST['max']);

//     // Update the user's score in the database
//     $wpdb->update(
//         $table_name,
//         array('score' => 1),
//         array('user_id' => $user_id , 'numbers' => $max ),
//         array('%d'),
//         array('%d')
//     );

//     // Return a success response
//     echo json_encode(array('success' => true));
//     exit();
// }

// add_action('wp_ajax_update_score', 'update_score');
// add_action('wp_ajax_nopriv_update_score', 'update_score');

// Callback function for checking the total score
// function check_total_score() {
//     global $wpdb;
//     $table_name = $wpdb->prefix . 'prime_checker';
//     $user_id = get_current_user_id();

//     // Calculate the total score for prime numbers for the current user
//     $total_score = $wpdb->get_var("SELECT SUM(score) FROM $table_name WHERE user_id = $user_id AND is_prime = 1");

//     // Return the total score as a response
//     echo json_encode(array('total_score' => $total_score));

//     // Important: Always exit to prevent further execution
//     exit();
// }



// add_action('wp_ajax_check_total_score', 'check_total_score');
// add_action('wp_ajax_nopriv_check_total_score', 'check_total_score');

?>
