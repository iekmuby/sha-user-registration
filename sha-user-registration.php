<?php
/*
Plugin Name: User Registaration
Description: AJAX user registration and email confirmation
Version: 1.0.0
Author: Andrew Sh
License: GPLv2
Text Domain: sha-ureg
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* Variables */
if ( isset( $sha_ureg_module_name ) ) {
	die( 'sha-ureg duplicating module name' );
} else {
	$sha_ureg_module_name = 'sha-user-registration';
}

if ( isset( $sha_ureg_prefix ) ) {
	die( 'sha-ureg duplication module prefix' );
} else {
	$sha_ureg_prefix = 'sha_ureg_';
}

if ( isset( $sha_ureg_page_slug ) ) {
	die( 'sha-ureg duplicationg module page slug' );
} else {
	$sha_ureg_page_slug = 'user-activation';
}
/* End Variables */

/* Custom functions */
// Check password strength
function passwordStrength( $pass ) {
	$score = 0;
	$pass = trim( $pass );

	if ( strlen( $pass ) >= 8 ) {
		$score += 20;
	} else {
		$score -= 50;
	}

	if ( preg_match( '/[a-z]/', $pass ) ) {
		$score += 10;
	}

	if ( preg_match( '/[A-Z]/', $pass) ) {
		$score += 20;
	}
	
	if ( preg_match( '/.[,!,@,#,$,%,^,&,*,?,_,~,-,(,)]/', $pass ) ) {
		$score += 40;
	}
	
	if ( preg_match( '/[0-9]/', $pass ) ) {
		$score += 10;
	}
	
	return $score;

}

//Check, if current user activated
function sha_ureg_is_user_activated() {
	global $sha_ureg_prefix;

	if ( $id = get_current_user_id() ) {
		
		$is_activated = get_user_meta( $id, $sha_ureg_prefix . 'is_activated', true );
		
		if ( $is_activated == 1 ) {
			return true;
		}
	}

	return;
}

// Locate template
function sha_ureg_locate_template( $template ) {
	global $sha_ureg_module_name;

	if ( file_exists( get_template_directory() . '/plugins/' . $sha_ureg_module_name . '/templates/' . $template ) ) {
		$template_path = sprintf(
			'%s/plugins/%s/templates/%s',
			get_template_directory(),
			$sha_ureg_module_name,
			$template
		);
	} else {
		$template_path = sprintf(
			'%s/%s/templates/frontend/%s',
			WP_PLUGIN_DIR,
			$sha_ureg_module_name,
			$template
		);
	}
	
	return $template_path;
}

//Replace template placeholders
function sha_ureg_replace_template_placeholders( $template, $placeholders = array() ) {
	$template_contents = file_get_contents( $template );
	foreach ( $placeholders as $placeholder => $value ) {
		$template_contents = str_replace( '{' . $placeholder . '}', $value, $template_contents );
	}

	return $template_contents;
}


/* End custom functions */

//Enqueue admin styles and scripts
add_action( 'admin_enqueue_scripts', 'sha_ureg_admin_enqueue_scripts' );

function sha_ureg_admin_enqueue_scripts() {
	global $sha_ureg_module_name;

	wp_enqueue_style( 'sha-ureg-admin-styles', plugins_url() . '/' . $sha_ureg_module_name . '/css/backend/admin.css' );
	wp_enqueue_script( 'sha-ureg-admin-js', plugins_url() . '/' . $sha_ureg_module_name . '/js/backend/scripts.js', array( 'jquery' ), '2018105', true );
}

//Create rewrite rules
add_action( 'init', 'sha_ureg_init' );

function sha_ureg_init() {
	global $sha_ureg_page_slug;

	//Rewrite for activation page
    add_rewrite_rule(
        $sha_ureg_page_slug . '/?$',
        'index.php?pagename=' . $sha_ureg_page_slug,
        'top'
	);	

	//Rewrite for activation page with code
    add_rewrite_rule(
        $sha_ureg_page_slug . '/([a-zA-Z0-9]+)/?$',
        'index.php?pagename=' . $sha_ureg_page_slug . '&activation_code=$matches[1]',
        'top'
	);	
}

//Register query variables
add_filter( 'query_vars', 'sha_ureg_custom_query_vars_filter' );

function sha_ureg_custom_query_vars_filter( $vars ) {
	$vars[] = 'activation_code';

	return $vars;
}

//Catch current module
add_action( 'parse_request', 'sha_ureg_parse_request' );

function sha_ureg_parse_request( $query ) {
	global $sha_ureg_page_slug, $sha_ureg_prefix;

	if ( $query->query_vars['pagename'] == $sha_ureg_page_slug ) {
		if ( $code = $query->query_vars['activation_code'] ) {
			$args = array(
				'meta_key'		=> $sha_ureg_prefix . 'activation_code',
				'meta_value' 	=> $code,
				'number'		=> 1,
				'count_total'	=> false
			);
			
			if ( $user_data = get_users( $args ) ) {
				delete_user_meta( $user_data[0]->data->ID, $sha_ureg_prefix . 'activation_code' );
				update_user_meta( $user_data[0]->data->ID, $sha_ureg_prefix . 'is_activated', 1 );
			}
		}
	}
}

//Activation variables shortcode
/*
 * email - user email
 * 
 */
//add_shortcode( 'sha-ureg-vars', 'sha_ureg_vars_shortcode' );

//Registration form shortcode
add_shortcode( 'sha-ureg-form', 'sha_ureg_form_shortcode' );

function sha_ureg_form_shortcode( $atts = array() ) {
	global $sha_ureg_module_name, $sha_ureg_prefix;
	
	$allow_user_password = $allow_recaptcha = false;

	$recaptcha_site_key = get_option( $sha_ureg_prefix . 'rec_site_key' );

	$template = sha_ureg_locate_template( 'registration-form.php' );

	wp_enqueue_script( 'sha-ureg-core', plugins_url() . '/' . $sha_ureg_module_name . '/js/frontend/module.js', array(), '2018105', true );
	wp_enqueue_script( 'sha-ureg-script', plugins_url() . '/' . $sha_ureg_module_name . '/js/frontend/script.js', array( 'sha-ureg-core' ), '2018105', true );

	wp_enqueue_style( 'sha-ureg-styles', plugins_url() . '/' . $sha_ureg_module_name . '/css/frontend/styles.css' );


	$params = array(
	  'ajaxurl' => admin_url('admin-ajax.php', $protocol),
	  'ajax_nonce' => wp_create_nonce('any_value_here'),
	);

	wp_localize_script( 'b4s-main-scripts', 'ajax_object', $params );
	
	if ( get_option( $sha_ureg_prefix . 'use_recaptcha' ) == 1 ) {
		wp_enqueue_script( 'google-reCaptcha', 'https://www.google.com/recaptcha/api.js' );
	}
	
	ob_start();
	include $template;

	return ob_get_clean();
}


//Ajax processor for user register
function sha_ureg_ajax_register() {
	global $sha_ureg_prefix, $sha_ureg_page_slug;

    $response = array(
		'status'	=> 'failed'
    );

    // First check the nonce, if it fails the function will break
    if ( wp_verify_nonce( sanitize_text_field( $_POST['security'] ), 'registration_nonce' ) ) {
		if ( get_option( $sha_ureg_prefix . 'use_recaptcha' ) == 1 ) {
			$recaptcha = $_POST['g-recaptcha-response'];
			if ( !empty( $recaptcha ) ) {
				$google_url = "https://www.google.com/recaptcha/api/siteverify";
				$secret = get_option( $sha_ureg_prefix . 'rec_secret' );
				$ip = $_SERVER['REMOTE_ADDR'];
				$url = $google_url . "?secret=" . $secret . "&response=" . $recaptcha . "&remoteip=" . $ip;
				$curl = curl_init();
				curl_setopt( $curl, CURLOPT_URL, $url );
				curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
				curl_setopt( $curl, CURLOPT_TIMEOUT, 10 );
				curl_setopt( $curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16" );
				$results = curl_exec( $curl );
				curl_close( $curl );
				$res = json_decode( $results, true );

				if ( !$res['success'] ) {
					$response['error'] = true;
					$response['errors'][]  = array(
						'id'	=> 'globalErrors',
						'error'	=> __( 'reCAPTCHA invalid', 'sha-ureg' )
					);
				}
			} else {
				$response['error'] = true;
				$response['errors'][]  = array(
					'id'	=> 'globalErrors',
					'error'	=> __( 'Please enter reCAPTCHA', 'sha-ureg' )
				);
			}
		}
		
		if ( ( get_option( $sha_ureg_prefix . 'allow_username' ) == 1 ) ) {
			if ( empty( trim( $_POST['login'] ) ) ) {
				$response['error'] = true;
				$response['errors'][] = array(
					'id'	=> 'regUser',
					'error'	=> __( 'Username is empty', 'sha-ureg' )
				);
			}

			if ( preg_match( '/[ ]+/', trim( $_POST['login'] ) ) ) {
				$response['error'] = true;
				$response['errors'][] = array(
					'id'	=> 'regUser',
					'error'	=> __( 'Username cannot contain spaces', 'sha-ureg' )
				);
			}

		}

		if ( ( get_option( $sha_ureg_prefix . 'allow_password' ) == 1 ) ) {

			if ( preg_match( '/[ ]+/', trim( $_POST['pass'] ) ) ) {
				$response['error'] = true;
				$response['errors'][] = array(
					'id'	=> 'regPass',
					'error'	=> __( 'Password cannot contain spaces', 'sha-ureg' )
				);
			}

			if ( strlen( trim( $_POST['pass'] ) ) < 5 ) {
				$response['error'] = true;
				$response['errors'][] = array(
					'id'	=> 'regPass',
					'error'	=> __( 'Password too short', 'sha-ureg' )
				);
			}

			if ( get_option( $sha_ureg_prefix . 'validate_password' ) == 1 ) {
				if ( passwordStrength( sanitize_text_field( $_POST['pass'] ) ) < 60 ) {
					$response['error'] = true;
					$response['errors'][] = array(
						'id'	=> 'regPass',
						'error'	=> __( 'Password too weak', 'sha-ureg' )
					);
				}
			}
		}

		if ( !filter_var( trim( $_POST['email'] ), FILTER_VALIDATE_EMAIL ) ) {
			$response['error'] = true;
			$response['errors'][] = array(
				'id'	=> 'regEmail',
				'error'	=> __( 'Enter a valid e-mail address', 'sha-ureg' )
			);
		}

		if ( !empty( $response['error'] ) ) {
			header('Content-type: application/json');
			echo json_encode( $response );
			die;
		}

		// Nonce is checked, get the POST data and sign user on
		$info = array();
		$info['user_login'] = sanitize_user( $_POST['email'] );
		$info['user_pass'] = sanitize_text_field( $_POST['pass'] );
		$info['user_email'] = sanitize_email( $_POST['email'] );

		// Register the user
		$user_id = wp_insert_user( $info );
		if ( is_wp_error( $user_id ) ) { 
			$error  = $user_id->get_error_codes();

			$error_data = array(
				'id'	=> 'globalErrors',
				'error'	=> __( 'Unknown error. Please try again', 'sha-ureg' )
			);

			if( in_array( 'existing_user_login', $error ) ) {
				$error_data = array(
					'id'	=> 'regUser',
					'error'	=> __( 'This username already exists', 'sha-ureg' )
				);
				if ( get_option( $sha_ureg_prefix . 'allow_username' ) != 1 ) {
					$error_data['id'] = 'regEmail';
				}
			}

			if( in_array( 'existing_user_email', $error ) ) {
				$error_data = array(
					'id'	=> 'regEmail',
					'error'	=> __( 'This user already exists', 'sha-ureg' )
				);
			}

			$response['errors'][] = $error_data;

		} else {
			//auth_user_login($info['nickname'], $info['user_pass'], 'Registration');
			if ( get_option( $sha_ureg_prefix . 'need_activation' ) == 1 ) {
				$activation_code = md5( $user_id . time() );
				add_user_meta( $user_id, $sha_ureg_prefix . 'is_activated', 0, true );
				add_user_meta( $user_id, $sha_ureg_prefix . 'activation_code', $activation_code, true );
				$activation_template = sha_ureg_locate_template( 'activation-email.php' );
				$template_contents = file_get_contents( $activation_template );
				$activation_template = sha_ureg_locate_template( 'activation-email.php' );
				
				$placeholders = array(
					'activation-link'	=> sprintf(
						'%s/%s/%s/',
						get_bloginfo( 'url' ),
						$sha_ureg_page_slug,
						$activation_code
					)
				);
				$template_contents = sha_ureg_replace_template_placeholders( $activation_template, $placeholders );
				wp_mail( sanitize_user( $_POST['email'] ), __( 'Complete your registration at ', 'sha-ureg' ) . get_bloginfo( 'name' ), $template_contents, array('Content-Type: text/html; charset=UTF-8') );
			} else {
				add_user_meta( $user_id, $sha_ureg_prefix . 'is_activated', 1, true );				
			}
			$response['status'] = 'success';
			if ( $page_id = get_option( $sha_ureg_prefix . 'registration_redirect' ) ) {
				$response['redirect'] = get_page_link( $page_id );
			}
		}

		header('Content-type: application/json');
		echo json_encode( $response );
		die;
	}
}


//Enable the user with no privileges to run ajax_register() in AJAX
add_action( 'wp_ajax_nopriv_ajaxregister', 'sha_ureg_ajax_register' );

//Generate user activation page
add_filter( 'the_posts','sha_ureg_generate_page' );

function sha_ureg_generate_page( $posts ) {
    global $wp, $wp_query, $sha_ureg_fake_post_detected, $sha_ureg_page_slug;
        
    if ( !$sha_ureg_fake_post_detected && (strtolower($wp->request) == $sha_ureg_page_slug || ( isset( $wp->query_vars['pagename'] ) && $wp->query_vars['pagename'] == $sha_ureg_page_slug ) ) ) {
    
        // stop interferring with other $posts arrays on this page (only works if the sidebar is rendered *after* the main page)
        $sha_ureg_fake_post_detected = true;
        
        // create a fake virtual page
        $post = new stdClass;
        $post->post_author = 1;
        $post->post_name = $sha_ureg_page_slug;
        $post->guid = get_bloginfo('wpurl') . '/' . $user_sht_this_url;

        $post->post_content = '';
        $post->ID = -999;
        $post->post_type = 'page';
        $post->post_status = 'static';
        $post->comment_status = 'closed';
        $post->ping_status = 'open';
        $post->comment_count = 0;
        $post->post_date = current_time('mysql');
        $post->post_date_gmt = current_time('mysql', 1);
        $posts = NULL;
        $posts[] = $post;
        
        // make wpQuery believe this is a real page too
        $wp_query->is_page = true;
        $wp_query->is_singular = true;
        $wp_query->is_home = false;
        $wp_query->is_archive = false;
        $wp_query->is_category = false;
        unset( $wp_query->query["error"] );
        $wp_query->query_vars["error"] = '';
        $wp_query->is_404 = false;
    }
    
    return $posts;
}


// Register frontend template
add_filter( 'the_content', 'ussr_sht_include_template_function', 1 );

function ussr_sht_include_template_function( $content ) {
	global $post, $sha_ureg_page_slug;
    
    if ( $post->post_name == $sha_ureg_page_slug ) {
		if ( $code = get_query_var( $sha_ureg_prefix . 'activation_code', 1 ) ) {
			$template = sha_ureg_locate_template( 'activation-page.php' );
		} else {
			$template = sha_ureg_locate_template( 'activation-page.php' );
		}
		
		ob_start();
		include $template;
		
		return ob_get_clean();
	}
    
	return $content;
}

//Register system settings
function sha_ureg_register_system_settings() {
	
	global $sha_ureg_prefix;
	
	//options array as variable => callback
	$option_variables = array(
		'allow_username' => false,
		'allow_password' => false,
		'validate_password' => false,
		'need_agreement' => false,
		'agreement_label' => false,
		'use_recaptcha' => false,
		'rec_site_key' => false,
		'need_activation' => false,
		'activation_success' => false,
		'activation_fail' => false,
		'registration_redirect' => false,
		'rec_secret' => 'sha_ureg_precheck_secret_value'
	);
	
	foreach ( $option_variables as $variable => $callback ) {
		if ( $callback ) {
			register_setting( 'sha-ureg-settings-group', $sha_ureg_prefix . $variable, $callback );
		} else {
			register_setting( 'sha-ureg-settings-group', $sha_ureg_prefix . $variable );
		}
	}	
}

//Add admin page
add_action( 'admin_menu', 'sha_ureg_settings_page_register' );

//Prevent saving wrong or empty value
function sha_ureg_precheck_secret_value( $input ) {
	global $sha_ureg_prefix;

	if ( empty( $input ) ) {
		return get_option( $sha_ureg_prefix . 'rec_secret' );
	}

	if ( substr_count( $input, '*' ) >= 3 ) {
		return get_option( $sha_ureg_prefix . 'rec_secret' );
	}
	
	return $input;
}


function sha_ureg_settings_page_register() {
	global $sha_ureg_module_name;

	add_options_page( 
		__( 'User Registration Settings', 'sha-ureg' ),
		__( 'User Registration Settings', 'sha-ureg' ),
		'manage_options',
		$sha_ureg_module_name . '.php',
		'sha_ureg_settings_page_content'
	);

	add_action( 'admin_init', 'sha_ureg_register_system_settings' );
}

//Admin page content
function sha_ureg_settings_page_content() {
	global $sha_ureg_module_name, $sha_ureg_prefix;

	$allow_username = get_option( $sha_ureg_prefix . 'allow_username' );
	$allow_password = get_option( $sha_ureg_prefix . 'allow_password' );
	$validate_password = get_option( $sha_ureg_prefix . 'validate_password' );
	
	$need_agreement = get_option( $sha_ureg_prefix . 'need_agreement' );
	$agreement_label = get_option( $sha_ureg_prefix . 'agreement_label' );
	
	$need_activation = get_option( $sha_ureg_prefix . 'need_activation' );
	$activation_success = get_option( $sha_ureg_prefix . 'activation_success' );
	$activation_fail = get_option( $sha_ureg_prefix . 'activation_fail' );
	
	$registration_redirect = get_option( $sha_ureg_prefix . 'registration_redirect' );
	
	$use_recaptcha = get_option( $sha_ureg_prefix . 'use_recaptcha' );
	$site_key = get_option( $sha_ureg_prefix . 'rec_site_key' );
	$secret_key = get_option( $sha_ureg_prefix . 'rec_secret' );

	$template = sprintf( '%s/%s/templates/backend/options-page.php', WP_PLUGIN_DIR, $sha_ureg_module_name );

	include $template;
}


/*
//add_filter( 'views_users', 'modify_views_users_so_15295853' );

function modify_views_users_so_15295853( $views ) 
{
    // Manipulate $views
    
    print_r( $views );
    die;

    return $views;
}

function my_custom_bulk_actions( $actions ){
        $actions['verify'] = __( 'Verify user', 'sha-ureg' );
        $actions['unverify'] = __( 'Unverify user', 'sha-ureg' );
        return $actions;
    }

add_filter('bulk_actions-users','my_custom_bulk_actions');

add_filter( 'handle_bulk_actions-users', 'my_bulk_action_handler', 10, 3 );
 
function my_bulk_action_handler( $redirect_to, $doaction, $post_ids ) {
var_dump( $doaction );die;
  if ( $doaction !== 'email_to_eric' ) {
    return $redirect_to;
  }
  foreach ( $post_ids as $post_id ) {
    // Perform action for each post.
  }
  $redirect_to = add_query_arg( 'bulk_emailed_posts', count( $post_ids ), $redirect_to );
  return $redirect_to;
}
//*/


//Ajax processor for password change
function sha_ureg_user_verification_ajax() {
	global $sha_ureg_prefix;

    $response = array(
		'status'	=> 'failed'
    );

    // First check the nonce, if it fails the function will break
    if ( wp_verify_nonce( sanitize_text_field( $_POST['security'] ), 'nonce' ) ) {
		$user_id = get_current_user_id();
		$upload_dir   = wp_upload_dir();
		$user_upload_dir = sprintf( '%s/userdocs/%d', $upload_dir['basedir'], $user_id );
		if ( !file_exists( $user_upload_dir ) ) {
			mkdir( $user_upload_dir, 0775, true );
		}

		$existing_files = get_user_meta( $user_id, $sha_ureg_prefix . 'docs', true );
		$existing_files = unserialize( $existing_files );

		foreach ( $existing_files as $id => $existing_file ) {
			if ( !in_array( $existing_file['filename'], $_POST['existing_files'] ) ) {
				//echo $user_upload_dir . '/' . $existing_file['filename'];				
				unset( $existing_files[ $id ] );
				unlink( $user_upload_dir . '/' . $existing_file['filename'] );
			}
		}

		$existing_files = array_values( $existing_files );

		$files = array();
		foreach ( array_keys( $_FILES['userfiles']['name'] ) as $id ) {
			if ( $_FILES['userfiles']['error'][ $id ] != 0) {
				continue;
			}

			$file_info = pathinfo( $_FILES['userfiles']['name'][ $id ] );
			$file_name = uniqid() . '.' . $file_info['extension'];
			$file_path = sprintf( '%s/%s', $user_upload_dir, $file_name );
			if ( move_uploaded_file( $_FILES['userfiles']['tmp_name'][ $id ], $file_path ) ) {
				$existing_files[] = array(
					'name'			=> trim( $_FILES['userfiles']['name'][ $id ] ),
					'filename'		=> $file_name
				);
			}

			//if ( ) {
				
			//}
		}
		
		//if ( !empty( $existing_files ) ) {
			$response['status'] = 'success';
			update_user_meta( $user_id, $sha_ureg_prefix . 'docs', serialize( $existing_files ) );
		//}
	} else {
		$response['message'] = __( 'Wrong security nonce', 'b4s' );
	}

	header('Content-type: application/json');
	echo json_encode( $response );
	die;
}


//Enable the user with no privileges to run ajax_register() in AJAX
add_action( 'wp_ajax_user_verification', 'sha_ureg_user_verification_ajax' );


add_action( 'show_user_profile', 'wpse_237901_user_edit_section', 999 );
add_action( 'edit_user_profile', 'wpse_237901_user_edit_section', 999 );

function wpse_237901_user_edit_section() {
	global $sha_ureg_prefix, $sha_payouts_prefix;

    echo '<h2>Verification</h2>';
    if ( defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE ) {
    $user_id = get_current_user_id();
	// If is another user's profile page
	} elseif (! empty($_GET['user_id']) && is_numeric($_GET['user_id']) ) {
		$user_id = $_GET['user_id'];
	// Otherwise something is wrong.
	} else {
		die( 'No user id defined.' );
	}

    $verification_docs = get_user_meta( $user_id, $sha_ureg_prefix . 'docs', true );
    $verification_docs = unserialize( $verification_docs );
    $company_data = get_user_meta( $user_id, $sha_ureg_prefix . 'company_data', true );
    $company_data = unserialize( $company_data );
	$payouts_data = get_user_meta( $user_id, $sha_payouts_prefix . 'payout_data', true );
	$payouts_data = unserialize( $payouts_data );

    $field_mapping = array(
		'company_name' => 'Full registered company name',
		'name' => 'Your full name',
		'id' => 'Company registration number',
		'position'	=> 'Your position',
		'address' => 'Registered company address',
		'zip' => 'Post Code',
		'city' => 'City',
		'country' => 'Company registration country',
		'description' => 'Company description'
	);

    $docs_html = array();
	$upload_dir   = wp_upload_dir();

    foreach ( $verification_docs as $doc ) {
		$docs_html[] = sprintf( '<a href="%s/userdocs/%d/%s" target="blank">%s</a>', $upload_dir['baseurl'], $user_id, $doc['filename'], $doc['name'] );
	}
	
	foreach ( $company_data as $k => $v ) {
		printf( '<strong>%s</strong> - %s<br />', $field_mapping[ $k ], ( $k == 'country' ) ? b4s_get_country_by_code( $v ) : $v );
	}
	
	echo '<h3>Docs</h3>';
	echo implode( ', ', $docs_html );
	$status = get_user_meta( $user_id, $sha_ureg_prefix . 'company_status', true );
	echo '<br /><select name="company_status">';
	foreach ( array( 'unverified' => 'Unverified', 'verified' => 'Verified' ) as $k => $v ) {
		$selected = ( $status == $k ) ? ' selected' : '';
		printf( '<option value="%s"%s>%s</option>', $k, $selected, $v );
	}
	echo '</select>';
	
	echo '<h2>Payout addresses</h2>';
	foreach ( $payouts_data as $cur => $payout_data ) {
		echo '<div style="margin-bottom: 20px"><label for="' . $cur . '">' . strtoupper( $cur ) . ':</label> ';
		echo '<select name="payout_status[' . $cur . ']" id="' . $cur . '">';
		foreach ( array( 'inactive' => 'Inactive', 'active' => 'Active' ) as $k => $v ) {
			$selected = ( $payout_data['status'] == $k ) ? ' selected' : '';
			printf( '<option value="%s"%s>%s</option>', $k, $selected, $v );
		}
		echo '</select></div>';
	}
}


add_action( 'personal_options_update', 'save_extra_user_fields' );
add_action( 'edit_user_profile_update', 'save_extra_user_fields' );
function save_extra_user_fields( $user_id ) {
	global $sha_ureg_prefix, $sha_payouts_prefix;

	$payouts_data = get_user_meta( $user_id, $sha_payouts_prefix . 'payout_data', true );
	$payouts_data = unserialize( $payouts_data );
	foreach ( $_POST['payout_status'] as $cur_symbol => $status ) {
		$payouts_data[ $cur_symbol ]['status'] = $status;
	}

	update_user_meta( $user_id, $sha_payouts_prefix . 'payout_data', serialize( $payouts_data ) );
	update_user_meta( $user_id, $sha_ureg_prefix . 'company_status', sanitize_text_field( $_POST['company_status'] ) );
}

