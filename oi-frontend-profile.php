<?
/*
Plugin Name: Oi Frontend Profile
Plugin URI: http://www.easywebsite.ru/shop/plugins-wordpress/oi-frontend-profile
Description: Plugin creates frontend profile page and redirects users from wp-admin/profile.php to this page. Form looks in Bootstrap style.
Version: 1.0
Author: Alexei Isaenko
Author URI: http://sh14.ru
License: GPL2
*/
require_once 'oi-nput.php';
// localization
function oifep_localization()
{
	load_plugin_textdomain( 'oifrontendprofile', false, plugin_basename( dirname( __FILE__ ) ) . '/lang' );
}
add_action('init', 'oifep_localization');

function oifep_path()
{
	return WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));
}

register_activation_hook( __FILE__, 'create_profile_page' ); // хук при активации плагина
function create_profile_page() // создание страницы профил€
{
	$post_id = get_option( 'oifrontendprofile_page' );
	if( $post_id == false || get_post_status( $post_id ) <> 'publish' ) // если страница еще не создана или не опубликована...
	{
		$page = array(
			'post_title' => __('Profile','oifrontendprofile'),
			'post_name' => 'profile',
			'post_content' => '[oi_frontend_profile]',
			'post_status' => 'publish',
			'post_type' => 'page',
			'comment_status' => 'closed',
			//'post_author' => 2,
			//'post_date' => '2012-08-20 15:10:30'
		);

		$post_id = wp_insert_post($page); // создаем ее
		update_option( 'oifrontendprofile_page', $post_id ); // сохран€ем в настройках id страницы
	}
}

function oifep_styles()
{
	wp_enqueue_style('oifep', oifep_path().'/css/style.css');
}
add_action('wp_enqueue_scripts', 'oifep_styles');

add_action ('init' , 'prevent_profile_access'); // инициализаци€ функции перенаправлени€
 
function prevent_profile_access() // функци€ перенаправлени€
{
	if (current_user_can('manage_options')) return ''; // если пользователь может мен€ть настройки системы - ничего не делаем, иначе...
	if (strpos ($_SERVER ['REQUEST_URI'] , 'wp-admin/profile.php' )) // перехватываем перехход в профиль
	{
		$id = get_option( 'oifrontendprofile_page' ); // получаем id страницы профил€ из настроек
		if( get_post_status( $id ) == 'publish' ) // если она существует и опубликована...
		{
			$id = '/?p=' . $id; // формируем адрес редиректа на эту страницу
		}else
		{
			$id = ''; // иначе редирект будет без параметров(на главную)
		}
		wp_redirect ( get_bloginfo('url') . $id ); // редирект
	}
}

//wp_enqueue_script( 'password-strength-meter' );

function oi_public_display($profileuser=null) // make list of "options" for select box
{
	if($profileuser<>null)
	{
		$public_display = array();
		$public_display['display_nickname']  = $profileuser->nickname;
		$public_display['display_username']  = $profileuser->user_login;

		if ( !empty($profileuser->first_name) )
			$public_display['display_firstname'] = $profileuser->first_name;

		if ( !empty($profileuser->last_name) )
			$public_display['display_lastname'] = $profileuser->last_name;

		if ( !empty($profileuser->first_name) && !empty($profileuser->last_name) ) {
			$public_display['display_firstlast'] = $profileuser->first_name . ' ' . $profileuser->last_name;
			$public_display['display_lastfirst'] = $profileuser->last_name . ' ' . $profileuser->first_name;
		}

		if ( !in_array( $profileuser->display_name, $public_display ) ) // Only add this if it isn't duplicated elsewhere
			$public_display = array( 'display_displayname' => $profileuser->display_name ) + $public_display;

		$public_display = array_map( 'trim', $public_display );
		$public_display = array_unique( $public_display );
		$names = array();
		foreach($public_display as $k=>$v)
		{
			$names[$v] = $v;
		}
		return $names;
	}
}
function oi_frontend_profile()
{
	if(!function_exists('get_user_to_edit')){
		require_once(ABSPATH.'/wp-admin/includes/user.php');
	}

	// if-not-user-logged-in
	if(!is_user_logged_in())
	{
		get_template_part( 'login-form' ); 
	}else
	{
		global $userdata, $wp_http_referer;
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;

		if ( !isset( $_POST['submit'] ) )
		{
			$profileuser = get_user_to_edit($user_id);
		}else
		{
			ob_start();
				check_admin_referer('update-user_' . $user_id);
				$errors = edit_user( $user_id );
				
				if ( is_wp_error( $errors ) )
				{
					$alert_message = $errors->get_error_message();
					$alert_type = 'alert-error text-danger';
				}else
				{
					$alert_message = __('Your profile has been updated successfully.','oifrontendprofile');
					$alert_type = 'bg-success text-success';
					do_action( 'personal_options_update', $user_id );
				}
				if($alert_message){$alert_message = '<div class="alert '.$alert_type.'">'.$alert_message.'</div>';}
			ob_end_clean();
			$profileuser = get_user_to_edit($user_id);
		}
		ob_start();
	?>
		
		<!-- .step-form -->
		<div class="step-form oifrontendprofile">
			<?=$alert_message?>
		
			<!-- .step-form-wrap -->
			<div class="form-table">

				<form role="form" id="your-profile" action="<?=$_SERVER['REQUEST_URI']?>" method="post"<? do_action('user_edit_form_tag'); ?>>
					<?=wp_nonce_field('update-user_' . $user_id,'_wpnonce',true,false) ?>
					
					<? if ( $wp_http_referer ) : ?>
						<input type="hidden" name="wp_http_referer" value="<? echo esc_url( $wp_http_referer ); ?>" />
					<? endif; ?>	
					
					<input type="hidden" name="from" value="profile" />
					<input type="hidden" name="checkuser_id" value="<? echo $user_id ?>" />
					
					<? do_action( 'personal_options', $profileuser ); ?>
					<? do_action( 'profile_personal_options', $profileuser ); ?>
					
					<div class="row">
						<div class="col-xs-6 col-md-6 form-group">
							<?=oinput(array('key'=>'user_login','before'=>__('Username'),'disabled'=>true,'value'=>esc_attr($profileuser->user_login),'hint'=>__('Usernames cannot be changed.','oifrontendprofile'),))?>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-6 col-md-6 form-group">
							<?=oinput(array('key'=>'first_name','before'=>__('First Name', 'oifrontendprofile'),'value'=>esc_attr($profileuser->first_name),))?>
						</div>

						<div class="col-xs-6 col-md-6 form-group">
							<?=oinput(array('key'=>'last_name','before'=>__('Last Name', 'oifrontendprofile'),'value'=>esc_attr($profileuser->last_name),))?>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-6 col-md-6 form-group">
							<?=oinput(array('key'=>'nickname','before'=>__('Nickname', 'oifrontendprofile'),'value'=>esc_attr($profileuser->nickname),))?>
						</div>

						<div class="col-xs-6 col-md-6 form-group">
						<?/*?>
						<pre><code><?=print_r(oi_public_display($profileuser))?></code></pre>
						<pre><code><?=$profileuser->display_name?></code></pre>
						<?*/?>
							<?$value = oinput(array('type'=>'option','key'=>oi_public_display($profileuser),'value'=>$profileuser->display_name));?>
							<?=oinput(array('type'=>'select','key'=>'display_name','before'=>__('Display name publicly as', 'oifrontendprofile'),'value'=>$value,))?>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xs-12 col-md-12 form-group">
							<h3><? _e('Contact Info', 'oifrontendprofile') ?></h3>
						</div>	
					</div>	
					<div class="row">
						<div class="col-xs-6 col-md-6 form-group">
							<?=oinput(array('key'=>'email','before'=>__('E-mail', 'oifrontendprofile'),'value'=>esc_attr($profileuser->user_email),'required'=>'data-validation="email"',))?>
						</div>	

						<div class="col-xs-6 col-md-6 form-group">
 							<?=oinput(array('key'=>'url','before'=>__('Website', 'oifrontendprofile'),'value'=>esc_attr($profileuser->user_url),))?>
						</div>							
					</div>

					<div class="row">
						<div class="col-xs-12 col-md-12 form-group">
							<h3><? _e('About Yourself', 'oifrontendprofile') ?></h3>
							<?=oinput(array('type'=>'textarea','key'=>'description','before'=>__('Biographical Info', 'oifrontendprofile'),'value'=>esc_attr($profileuser->description),'hint'=>__('Share a little biographical information to fill out your profile. This may be shown publicly.','oifrontendprofile')))?>
						</div>
					</div>

					<? do_action( 'show_user_profile', $profileuser ); ?>
					<div class="row">
					<?
						$show_password_fields = apply_filters('show_password_fields', true, $profileuser);
						if ( $show_password_fields )
						{
					?>
						<div class="col-xs-12 col-md-12 form-group">
						<input type="password" value="" style="display: none;" /><? // hook for webkit ?>
						<?=oinput(array('key'=>'pass1','type'=>'password','before'=>__('New Password', 'oifrontendprofile'),'addon'=>' autocomplete="off"','hint'=>__('If you would like to change the password type a new one. Otherwise leave this blank.','oifrontendprofile')))?>
						</div>
						<div class="col-xs-12 col-md-12 form-group">
						<?=oinput(array('key'=>'pass2','type'=>'password','before'=>__('Type your new password again.', 'oifrontendprofile'),'addon'=>'autocomplete="off"',))?>
						</div>
					<?}?>		
					</div>

					<div class="row">
						<div class="col-xs-12 col-md-12 form-group">
							<?//=oinput(array('type'=>'hidden','key'=>'admin_bar_front','value'=>esc_attr($profileuser->admin_bar_front),))// пр€чет админбар при сохранении?>
							<input type="hidden" name="action" value="update" />
							<input type="hidden" name="user_id" id="user_id" value="1">
							<button name="submit" id="submit" type="submit" class="btn btn-success pull-right"><?=__('Update Profile', 'oifrontendprofile')?></button>
						</div>
					</div>
						
				</form>
				
				<script>if(window.location.hash == '#password'){document.getElementById('pass1').focus();}</script>
			</div><!-- .step-form-wrap -->

		</div><!-- end - .step-form -->				
	
	<?
		$out = ob_get_contents();
		ob_end_clean();
		return $out;
	}
	?><!-- end-if-user-is-logged -->
<?
}
add_shortcode('oi_frontend_profile','oi_frontend_profile');
?>