<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package    Kohana/Ion_Auth
 * @category   Libraries
 * 
 * Name:  Ion Auth
 *
 * Author: Ben Edmunds
 *		  ben.edmunds@gmail.com
 *         @benedmunds
 *
 * Added Awesomeness: Phil Sturgeon
 *
 * Location: http://github.com/benedmunds/CodeIgniter-Ion-Auth
 *
 * Created:  10.01.2009
 *
 * Description:  Modified auth system based on redux_auth with extensive customization.  This is basically what Redux Auth 2 should be.
 * Original Author name has been kept but that does not mean that the method has not been modified.
 *
 * Requirements: PHP5 or above
 *
 * Modified to work with Kohana by Eugene Kudelia
 * https://github.com/eugenekudelia/ion_auth/tree/kohana-v3.3
 */
class Ion_Auth
{
	/**
	 * Ion_Auth instance
	 */
	protected static $_instance;

	/**
	 * Ion_Auth model instance
	 */
	protected $ion_auth_model;

	/**
	 * Ion Auth email config
	 */
	protected $email_config;

	/**
	 * Email instance
	 */
	protected $email;

	/**
	 * Use built-in email?
	 */
	protected $use_builtin_email = FALSE;

	/**
	 * account status ('not_activated', etc ...)
	 *
	 * @var string
	 */
	protected $status;

	/**
	 * caching of users and their groups
	 *
	 * @var array
	 */
	protected $_cache_user_in_group = array();

	/**
	 * caching of user cms access
	 *
	 * @var array
	 */
	protected $_cache_user_has_cms_access = array();


	/**
	 * Ion Auth instance
	 */
	public static function instance()
	{
		if ( ! isset(Ion_Auth::$_instance))
		{
			// Create a new Ion_Auth instance
			Ion_Auth::$_instance = new Ion_Auth();
		}

		return Ion_Auth::$_instance;
	}

	/**
	 * __construct
	 *
	 * @return void
	 * @author Ben
	 * @kohana Eugene Kudelia
	 */
	public function __construct()
	{
		// Create Ion Auth model instance
		$this->ion_auth_model = Model::factory('Ion_Auth');

		$this->_cache_user_in_group =& $this->ion_auth_model->_cache_user_in_group;

		//auto-login the user if they are remembered
		if ( ! $this->logged_in() AND Cookie::get('identity') AND Cookie::get('remember_code'))
		{
			$this->ion_auth_model->login_remembered_user();
		}

		// Create Email instance
		if ($this->ion_auth_model->_config()->get('use_builtin_email'))
		{
			$this->email_config = $this->ion_auth_model->_config()->get('email_config');

			if ( ! empty($this->email_config) AND is_array($this->email_config))
			{
				$this->email = Email::instance($this->email_config);//Dev::prd($this->email);
				$this->use_builtin_email = TRUE;
			}
		}

		$this->ion_auth_model->trigger_events('library_constructor');
	}

	/**
	 * __call
	 *
	 * Acts as a simple way to call model methods without loads of stupid alias'
	 *
	 */
	public function __call($method, $arguments)
	{
		if ( ! method_exists( $this->ion_auth_model, $method) )
		{
			throw new Ion_Auth_Exception('Undefined method Ion_Auth:::method() called',
				array(':method' => $method));
		}

		return call_user_func_array( array($this->ion_auth_model, $method), $arguments);
	}

	/**
	 * forgotten password feature
	 *
	 * @return mixed  boolean / array
	 * @author Mathew
	 * @kohana Eugene Kudelia
	 */
	public function forgotten_password($identity, $switch = '')    //changed $email to $identity
	{
		if ( ! $this->ion_auth_model->forgotten_password($identity))   //changed
		{
			$this->set_error('forgot_password_unsuccessful');
			return FALSE;
		}

		// Get user information
		$query = $this->where($this->_config()->get('identity'), '=', $identity)->users();

		if ($query->rows_count() === 0)
		{
			$this->set_error('forgot_password_user_not_found');
			return FALSE;
		}

		$user = $query->row();
		$code = $user->forgotten_password_code;

		if (is_string($switch))
		{
			$data = array(
				'identity' => $user->{$this->_config()->get('identity')},
				'forgotten_password_code' => $code,
				'cms' => $switch
			);

			if ( ! $this->use_builtin_email)
			{
				$this->set_message('forgot_password_successful');
				return $data;
			}
			else
			{
				$message = View::factory('ion_auth::'.$this->_config()->get('email_templates').$this->_config()->get('email_forgot_password_complete'), $data)->render();

				$this->email->clear();
				$this->email->from($this->_config()->get('admin_email'), $this->_config()->get('site_title'));
				$this->email->to($user->email);
				$this->email->subject('['.$this->_config()->get('site_title').'] '.ion__('Reset the forgotten password', NULL, 'email'));
				$this->email->message($message);

				if ($this->email->send())
				{
					$this->set_message('forgot_password_successful');
					return TRUE;
				}
				else
				{
					$this->set_error('forgot_password_unsuccessful');
					return FALSE;
				}
			}
		}
		// added functionality of forgotten_password_complete()
		elseif ($switch === TRUE AND ($password = $this->ion_auth_model->forgotten_password_complete($code, $user->salt)))
		{
			$data = array(
				'identity'		=> $user->{$this->_config()->get('identity')},
				'password'		=> $password,
				'login_link'	=> HTML::anchor('login', NULL, NULL, 'http')
			);
			if ( ! $this->use_builtin_email)
			{
				$this->set_message('password_change_successful');
				$this->ion_auth_model->trigger_events(array('post_password_change', 'password_change_successful'));

				return $data;
			}
			else
			{
				$message = View::factory('ion_auth::'.$this->_config()->get('email_templates').$this->_config()->get('email_forgot_password_new'), $data)->render();

				$this->email->clear();
				$this->email->from($this->_config()->get('admin_email'), $this->_config()->get('site_title'));
				$this->email->to($user->email);
				$this->email->subject('['.$this->_config()->get('site_title').'] '.ion__('Reset the forgotten password', NULL, 'email'));
				$this->email->message($message);

				if ($this->email->send())
				{
					$this->set_message('password_change_successful');
					$this->ion_auth_model->trigger_events(array('post_password_change', 'password_change_successful'));

					return TRUE;
				}
				else
				{
					$this->set_error('password_change_unsuccessful');
					$this->ion_auth_model->trigger_events(array('post_password_change', 'password_change_unsuccessful'));

					return FALSE;
				}
			}

			return FALSE;
		}
	}

	/**
	 * forgotten_password_check
	 *
	 * @return void
	 * @author Michael
	 * @kohana Eugene Kudelia
	 */
	public function forgotten_password_check($code)
	{
		$query = $this->where('forgotten_password_code', '=', $code)->users();

		if ($query->rows_count() === 0)
		{
			$this->set_error('password_change_unsuccessful');
			return FALSE;
		}

		$profile = $query->row(); //pass the code to profile

		if ($this->_config()->get('forgot_password_expiration') > 0)
		{
			//Make sure it isn't expired
			$expiration = $this->_config()->get('forgot_password_expiration');
			if (time() - $profile->forgotten_password_time > $expiration)
			{
				//it has expired
				$this->clear_forgotten_password_code($code);
				$this->set_error('password_change_unsuccessful');
				return FALSE;
			}
		}

		return $profile;
	}

	/**
	 * Send email with the new password
	 * if user have successfully changed password
	 *
	 * @return bool
	 * @author Eugene Kudelia
	 */
	function new_password_email($email, $username, $password, $new_user = FALSE)
	{
		$site_name = $this->_config()->get('site_title');

		$data = array(
			'username'	=> $username,
			'password'	=> $password
		);
		if ($new_user)
		{
			$tpl = 'email_new_user_details';
			$subject = ion__('Welcome to :site_name!', array(':site_name' => $site_name), 'email');
			$data['site_name'] = $site_name;
		}
		else
		{
			$tpl = 'email_new_password';
			$subject = '['.$site_name.'] '.ion__('Notice of the new password', NULL, 'email');
			$data['identity'] = $this->_config()->get('identity') == 'email' ? $email : $username;
		}

		$tpl = $new_user ? 'email_new_user_details' : 'email_new_password';
		$message = View::factory('ion_auth::'.$this->_config()->get('email_templates').$this->_config()->get($tpl), $data)->render();
		
		$this->email->clear();
		$this->email->set_newline("\r\n");
		$this->email->from($this->_config()->get('admin_email'), $site_name);
		$this->email->to($email);
		$this->email->subject($subject);
		$this->email->message($message);

		if ($this->email->send())
		{
			$text = $new_user
				? 'new_user_email_successful'
				: 'new_password_email_successful';

			$this->set_message($text);
			return TRUE;
		}
		else
		{
			$text = $new_user
				? 'new_user_email_unsuccessful'
				: 'new_password_email_unsuccessful';

			$this->set_error($text);
			return FALSE;
		}
	}

	/**
	 * register
	 *
	 * @return void
	 * @author Mathew
	 * @kohana Eugene Kudelia
	 */
	public function register($email, $username, $password, $display_name = NULL, $group_ids = array(), $profile = array(), $cms = FALSE)
	{
		$this->ion_auth_model->trigger_events('pre_account_creation');

		$email_activation = ! $cms
			? $this->_config()->get('email_activation')
			: FALSE; // method is called from the CMS module

		if ( ! $email_activation)
		{
			$id = $this->ion_auth_model->register($email, $username, $password, $display_name, $group_ids, $profile);
			if ($id !== FALSE)
			{
				$this->set_message('account_creation_successful');
				$this->ion_auth_model->trigger_events(array('post_account_creation', 'post_account_creation_successful'));
				return $id;
			}
			else
			{
				$this->set_error('account_creation_unsuccessful');
				$this->ion_auth_model->trigger_events(array('post_account_creation', 'post_account_creation_unsuccessful'));
				return FALSE;
			}
		}
		else
		{
			$id = $this->ion_auth_model->register($email, $username, $password, $display_name, $group_ids, $profile);

			if ( ! $id)
			{
				$this->set_error('account_creation_unsuccessful');
				return FALSE;
			}

			$deactivate = $this->ion_auth_model->deactivate($id, $cms);

			if ( ! $deactivate)
			{
				$this->set_error('deactivate_unsuccessful');
				$this->ion_auth_model->trigger_events(array('post_account_creation', 'post_account_creation_unsuccessful'));
				return FALSE;
			}

			$activation_code = $this->ion_auth_model->activation_code;
			$identity        = $this->_config()->get('identity');
			$user            = $this->ion_auth_model->user($id)->row();

			$data = array(
				'identity'   => $user->{$identity},
				'id'         => $user->id,
				'email'      => $email,
				'activation_code' => $activation_code,
			);
			if ( ! $this->use_builtin_email)
			{
				$this->ion_auth_model->trigger_events(array('post_account_creation', 'post_account_creation_successful', 'activation_email_successful'));
				$this->set_message('activation_email_successful');

				return $data;
			}
			else
			{
				$message = View::factory('ion_auth::'.$this->_config()->get('email_templates').$this->_config()->get('email_activate'), $data)->render();

				$this->email->clear();
				$this->email->from($this->_config()->get('admin_email'), $this->_config()->get('site_title'));
				$this->email->to($email);
				$this->email->subject('['.$this->_config()->get('site_title').'] '.ion__('Account Activation', NULL, 'email'));
				$this->email->message($message);

				if ($this->email->send() == TRUE)
				{
					$this->ion_auth_model->trigger_events(array('post_account_creation', 'post_account_creation_successful', 'activation_email_successful'));
					$this->set_message('activation_email_successful');
					return $id;
				}
			}

			$this->ion_auth_model->trigger_events(array('post_account_creation', 'post_account_creation_unsuccessful', 'activation_email_unsuccessful'));
			$this->set_error('activation_email_unsuccessful');
			return FALSE;
		}
	}

	/**
	 * logout
	 *
	 * @return void
	 * @author Mathew
	 * @kohana Eugene Kudelia
	 */
	public function logout()
	{
		$this->ion_auth_model->trigger_events('logout');

		//Destroy the session and restart with new session id
		if ($this->session_reset())
		{
			$this->set_message('logout_successful');
			return TRUE;
		}

		$this->set_error('logout_unsuccessful');
		return FALSE;
	}

	/**
	 * logged_in
	 *
	 * @return bool
	 * @author Mathew
	 * @kohana Eugene Kudelia
	 */
	public function logged_in()
	{
		$this->ion_auth_model->trigger_events('logged_in');

		$identity = $this->_config()->get('identity');
		return (bool) $this->_session()->get($identity);
	}

	/**
	 * get_user_id
	 *
	 * @return integer
	 * @author jrmadsen67
	 * @kohana Eugene Kudelia
	 */
	public function get_user_id()
	{
		$user_id = $this->_session()->get('user_id');

		return $user_id ? (int) $user_id : NULL;
	}

	/**
	 * is user (default) admin
	 *
	 * @uses   Ion_Auth::in_group()
	 * @return bool
	 * @author Ben Edmunds
	 * @kohana Eugene Kudelia
	 */
	public function is_admin($id = FALSE, $default = FALSE)
	{
		$this->ion_auth_model->trigger_events('is_admin');

		if ($default === TRUE)
		{
			$default_admin = $this->_config()->get('default_admin');
			return $this->in_group($default_admin, $id);
		}

		return $this->has_cms_access($id);
	}

	/**
	 * in_group
	 *
	 * @return bool
	 * @author Phil Sturgeon
	 * @kohana Eugene Kudelia
	 */
	public function in_group($check_group, $id = FALSE)
	{
		$this->ion_auth_model->trigger_events('in_group');

		$id OR $id = $this->_session()->get('user_id');

		if ( ! is_array($check_group))
		{
			$check_group = array($check_group);
		}

		if (isset($this->_cache_user_in_group[$id]))
		{
			$groups_array = $this->_cache_user_in_group[$id];
		}
		else
		{
			$users_groups = $this->ion_auth_model->get_users_groups($id, TRUE);
			$groups_array = array();
			foreach ($users_groups as $group)
			{
				$groups_array[$group->id] = $group->name;
			}
			$this->_cache_user_in_group[$id] = $groups_array;
		}
		foreach ($check_group as $value)
		{
			$groups = is_string($value) ? $groups_array : array_keys($groups_array);

			if (in_array($value, $groups))
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * is logged in user (default) admin
	 *
	 * @return bool
	 * @author Eugene Kudelia
	 */
	public function admin($default = FALSE)
	{
		if ($this->logged_in())
		{
			$user = is_object($this->user()->row()) ? $this->user()->row()->cms : NULL;
			if (is_numeric($user))
			{
				return $default ? $user == 2 : $user > 0;
			}
		}

		return FALSE;
	}

	/**
	 * has_cms_access
	 *
	 * @return bool
	 * @kohana Eugene Kudelia
	 */
	public function has_cms_access($id = FALSE)
	{
		$this->ion_auth_model->trigger_events('has_cms_access');

		$id OR $id = $this->_session()->get('user_id');

		if (isset($this->_cache_user_has_cms_access[$id]))
		{
			return $this->_cache_user_has_cms_access[$id];
		}

		$users_groups = $this->ion_auth_model->get_users_groups($id, TRUE);
		$cms_access = $this->_config()->get('cms_access');

		foreach ($users_groups as $group)
		{
			$has_cms_access[] = $group->{$cms_access};
		}
		$this->_cache_user_has_cms_access[$id] = in_array(TRUE, $has_cms_access);

		return $this->_cache_user_has_cms_access[$id];
	}

	/**
	 *
	 */
	public function unique_email($email)
	{
		return ! $this->row_exists($this->ion_auth_model->tables['users'], 'email', $email);
	}

	/**
	 *
	 */
	public function unique_username($username)
	{
		return ! $this->row_exists($this->ion_auth_model->tables['users'], 'username', $username);
	}

	/**
	 *
	 */
	public function unique_groupname($name)
	{
		return ! $this->row_exists($this->ion_auth_model->tables['groups'], 'name', $name);
	}

	/**
	 *
	 */
	public function unique_grouptitle($title)
	{
		return ! $this->row_exists($this->ion_auth_model->tables['groups'], 'title', $title);
	}

	/**
	 *
	 */
	public function username_length()
	{
		$min = $this->_config()->get('min_username_length');
		$max = $this->_config()->get('max_username_length');
		
		return $min.'&ndash;'.$max;
	}

	/**
	 *
	 */
	public function password_length()
	{
		$min = $this->_config()->get('min_password_length');
		$max = $this->_config()->get('max_password_length');
		
		return $min.'&ndash;'.$max;
	}

} // End Ion_Auth

/**
 * Ion Auth for Kohana translation/internationalization function.
 *
 *    ion__('Welcome back, Ben);
 * 
 * @uses    I18n::get
 * @uses    Lang::module
 * @param   string  $string text to translate
 * $param   array   $path   local path to lang file
 * @param   array   $values values to replace in the translated text
 * @param   string  $lang   target language
 * @return  string
 */
function ion__($string, array $values = NULL, $path = '', $lang = NULL)
{
	$lang = Lang::module('ion_auth', $path, $lang);
	$string = I18n::get($string, $lang);

	return empty($values) ? $string : strtr($string, $values);
}
