<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package    Kohana/Ion_Auth
 * @category   Models
 * 
 * Name:  Ion Auth Model
 *
 * Author:  Ben Edmunds
 * 		   ben.edmunds@gmail.com
 *	  	   @benedmunds
 *
 * Added Awesomeness: Phil Sturgeon
 *
 * Location: http://github.com/benedmunds/CodeIgniter-Ion-Auth
 *
 * Created:  10.01.2009
 * 
 * Last Change: 3.22.13
 *
 * Changelog:
 * * 3-22-13 - Additional entropy added - 52aa456eef8b60ad6754b31fbdcc77bb
 * 
 * Description:  Modified auth system based on redux_auth with extensive customization.  This is basically what Redux Auth 2 should be.
 * Original Author name has been kept but that does not mean that the method has not been modified.
 *
 * Requirements: PHP5 or above
 *
 * Modified to work with Kohana by Eugene Kudelia
 * https://github.com/eugenekudelia/ion_auth/tree/kohana-v3.3
 */
class Model_Ion_Auth extends Model_Common
{
	/**
	 *
	 */
	protected $config;

	/**
	 * Holds an array of tables used
	 *
	 * @var array
	 */
	public $tables = array();

	/**
	 * Identity column config
	 *
	 * @var string
	 */
	public $identity_column;

	/**
	 * Config items: name of Users table column
	 * and Groups table column you want to join WITH
	 *
	 * @var array
	 */
	public $join;

	/**
	 * activation code
	 *
	 * @var string
	 */
	public $activation_code;

	/**
	 * forgotten password key
	 *
	 * @var string
	 */
	public $forgotten_password_code;

	/**
	 * Store salt
	 *
	 * @var bool
	 */
	protected $store_salt;

	/**
	 * Salt length
	 *
	 * @var integer
	 */
	protected $salt_length;

	/**
	 * Ion Auth Query Builder properties
	 * moved to parent Model_Common
	 * for common use 
	 */

	/**
	 * Hooks
	 *
	 * @var object
	 */
	protected $_ion_hooks;

	/**
	 * message
	 *
	 * @var array
	 */
	protected $messages = array();

	/**
	 * error message
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * error start delimiter
	 *
	 * @var string
	 */
	protected $error_start_delimiter;

	/**
	 * error end delimiter
	 *
	 * @var string
	 */
	protected $error_end_delimiter;

	/**
	 * caching of users and their groups
	 *
	 * @var array
	 */
	public $_cache_user_in_group = array();

	/**
	 * caching of groups
	 *
	 * @var array
	 */
	protected $_cache_groups = array();

	/**
	 * Session instance
	 */
	protected $session;

	/**
	 * Bcrypt class parameter
	 */
	protected $rounds;

	/**
	 *
	 */
	protected $_ip_address;

	/**
	 *
	 */
	protected $_group_id = array();

	/**
	 *
	 */
	protected $_cache_user_permissions = array();


	public function __construct()
	{
		// Load Ion Auth config object
		$this->config = Kohana::$config->load('ion_auth');

		// Create Session instance
		$this->session = Session::instance($this->config->get('session_type'));
		//initialize db tables data
		$this->tables  = $this->config->get('tables');

		// initialize data
		$this->identity_column = $this->config->get('identity');
		$this->store_salt      = $this->config->get('store_salt');
		$this->salt_length     = $this->config->get('salt_length');
		$this->join			   = $this->config->get('join');

		// initialize messages and error
		$this->message_start_delimiter = $this->config->get('message_start_delimiter');
		$this->message_end_delimiter   = $this->config->get('message_end_delimiter');
		$this->error_start_delimiter   = $this->config->get('error_start_delimiter');
		$this->error_end_delimiter     = $this->config->get('error_end_delimiter');

		// initialize our hooks object
		$this->_ion_hooks = new stdClass;

		// set Bcrypt class parameter if needed
		if ($this->config->get('hash_method') == 'bcrypt')
		{
			if ($this->config->get('random_rounds'))
			{
				$rand = rand($this->config->get('min_rounds'), $this->config->get('max_rounds'));
				$this->rounds = array('rounds' => $rand);
			}
			else
			{
				$this->rounds = array('rounds' => $this->config->get('default_rounds'));
			}
		}

		$this->trigger_events('model_constructor');
	}

	/**
	 * Returns Kohana session object
	 *
	 * @return object
	 * @author Eugene Kudelia
	 */
	public function _config()
	{
		return $this->config;
	}

	/**
	 * Returns Ion Auth config object
	 *
	 * @return object
	 * @author Eugene Kudelia
	 */
	public function _session()
	{
		return $this->session;
	}

	/**
	 * Misc functions
	 *
	 * Hash password : Hashes the password to be stored in the database.
	 * Hash password db : This function takes a password and validates it
	 * against an entry in the users table.
	 * Salt : Generates a random salt value.
	 *
	 * @author Mathew
	 */

	/**
	 * Hashes the password to be stored in the database.
	 *
	 * @return void
	 * @author Mathew
	 * @kohana Eugene Kudelia
	 */
	public function hash_password($password, $salt = FALSE, $use_sha1_override = FALSE)
	{
		if (empty($password))
		{
			return FALSE;
		}

		//bcrypt
		if ($use_sha1_override === FALSE AND $this->config->get('hash_method') == 'bcrypt')
		{
			$bcrypt = new Bcrypt($this->rounds);
			return $bcrypt->hash($password);
		}

		// sha1
		if ($this->store_salt AND $salt)
		{
			return  sha1($password.$salt);
		}
		else
		{
			$salt = $this->salt();
			return  $salt.substr(sha1($salt.$password), 0, -$this->salt_length);
		}
	}

	/**
	 * This function takes a password and validates it
	 * against an entry in the users table.
	 *
	 * @return void
	 * @author Mathew
	 * @kohana Eugene Kudelia
	 */
	public function hash_password_db($id, $password, $use_sha1_override = FALSE)
	{
		if (empty($id) OR empty($password))
		{
			return FALSE;
		}

		$this->trigger_events('extra_where');

		$hash_password_db = DB::select('password', 'salt')
					            ->from($this->tables['users'])
								->where('id', '=', $id)
					            ->limit(1)
								->execute();

		if ($hash_password_db->count() !== 1)
		{
			return FALSE;
		}

		$hash_password = $hash_password_db->get('password');

		// bcrypt
		if ($use_sha1_override === FALSE AND $this->config->get('hash_method') == 'bcrypt')
		{
			$bcrypt = new Bcrypt($this->rounds);
			return $bcrypt->verify($password, $hash_password);
		}

		// sha1
		if ($this->store_salt)
		{
			$db_password = sha1($password.$hash_password_db->get('salt'));
		}
		else
		{
			$salt = substr($hash_password, 0, $this->salt_length);
			$db_password =  $salt.substr(sha1($salt.$password), 0, -$this->salt_length);
		}

		return $db_password == $hash_password;
	}

	/**
	 * Generates a random salt value for forgotten passwords or any other keys. Uses SHA1.
	 *
	 * @return void
	 * @author Mathew
	 */
	public function hash_code($password)
	{
		return $this->hash_password($password, FALSE, TRUE);
	}

	/**
	 * Generates a random salt value.
	 *
	 * @return void
	 * @author Mathew
	 */
	public function salt()
	{
		return substr(md5(uniqid(rand(), true)), 0, $this->salt_length);
	}

	/**
	 * Activation functions
	 *
	 * Activate : Validates and removes activation code.
	 * Deactivae : Updates a users row with an activation code.
	 *
	 * @author Mathew
	 */

	/**
	 * activate
	 *
	 * @return void
	 * @author Mathew
	 * @kohana Eugene Kudelia
	 */
	public function activate($id, $code = FALSE)
	{
		$this->trigger_events('pre_activate');

		if ($code !== FALSE)
		{
			$query = DB::select($this->identity_column)
						->from($this->tables['users'])
			            ->where('activation_code', '=', $code)
			            ->limit(1)
						->execute();

			if ($query->count() !== 1)
			{
				$this->trigger_events(array('post_activate', 'post_activate_unsuccessful'));
				$this->set_error('activate_unsuccessful');

				return FALSE;
			}

			$column = $this->identity_column;
			$value = $query->get($column);
		}
		else
		{
			$column = 'id';
			$value = $id;
		}

		$data = array(
		    'activation_code' => NULL,
		    'active'          => 1
		);

		$this->trigger_events('extra_where');

		$affected_rows = DB::update($this->tables['users'])
							->set($data)
							->where($column, '=', $value)
							->execute();

		if ($return = ($affected_rows === 1))
		{
			$this->trigger_events(array('post_activate', 'post_activate_successful'));
			$this->set_message('activate_successful');
		}
		else
		{
			$this->trigger_events(array('post_activate', 'post_activate_unsuccessful'));
			$this->set_error('activate_unsuccessful');
		}

		return $return;
	}

	/**
	 * Deactivate
	 *
	 * @return void
	 * @author Mathew
	 * @kohana Eugene Kudelia
	 */
	public function deactivate($id, $cms = TRUE)
	{
		$this->trigger_events('deactivate');

		$activation_code       = sha1(md5(microtime()));
		$this->activation_code = $activation_code;

		$data = array(
		    'activation_code'	=> $activation_code,
			'remember_code'		=> NULL,
		    'active'			=> 0
		);

		$this->trigger_events('extra_where');

		$affected_rows = DB::update($this->tables['users'])
							->set($data)
							->where('id', '=', $id)
							->execute();

		if ($return = ($affected_rows === 1))
		{
			if ($cms)
			{
				$this->set_message('deactivate_successful');
			}
		}
		else
		{
			$this->set_error('deactivate_unsuccessful');
		}

		return $return;
	}

	/**
	 * @kohana Eugene Kudelia
	 */
	public function clear_forgotten_password_code($code)
	{
		if (empty($code))
		{
			return FALSE;
		}

		$rows = DB::select('forgotten_password_code')
					->from($this->tables['users'])
					->where('forgotten_password_code', '=', $code)
					->execute();

		if ($rows->count() > 0)
		{
			$data = array(
			    'forgotten_password_code' => NULL,
			    'forgotten_password_time' => NULL
			);

			DB::update($this->tables['users'])
				->set($data)
				->where('forgotten_password_code', '=', $code)
				->execute();

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * reset password
	 *
	 * @return bool
	 * @author Mathew
	 * @kohana Eugene Kudelia
	 */
	public function reset_password($identity, $new)
	{
		$this->trigger_events('pre_change_password');

		if ( ! $this->row_exists($this->tables['users'], $this->identity_column, $identity))
		{
			$this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
			return FALSE;
		}

		$this->trigger_events('extra_where');

		$query = DB::select('salt')
		            ->from($this->tables['users'])
		            ->where($this->identity_column, '=', $identity)
		            ->limit(1)
					->execute();

		if ($query->count() !== 1)
		{
			$this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
			$this->set_error('password_change_unsuccessful');
			return FALSE;
		}

		$salt = $query->get('salt');
		$salt = ! empty($salt) ? $salt : FALSE;

		$new = $this->hash_password($new, $salt);

		//store the new password and reset the remember code so all remembered instances have to re-login
		//also clear the forgotten password code
		$data = array(
		    'password' => $new,
		    'remember_code' => NULL,
		    'forgotten_password_code' => NULL,
		    'forgotten_password_time' => NULL,
		);

		$this->trigger_events('extra_where');

		$affected_rows = DB::update($this->tables['users'])
							->set($data)
							->where($this->identity_column, '=', $identity)
							->execute();

		if ($return = ($affected_rows === 1))
		{
			$this->session_reset();

			$this->trigger_events(array('post_change_password', 'post_change_password_successful'));
			$this->set_message('password_change_successful');
		}
		else
		{
			$this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
			$this->set_error('password_change_unsuccessful');
		}

		return $return;
	}

	/**
	 * change password
	 *
	 * @return mixed
	 * @author Mathew
	 * @kohana Eugene Kudelia
	 */
	public function change_password($identity, $old = NULL, $new)
	{
		if (strlen($new) < $this->config->get('min_password_length') OR
			strlen($new) > $this->config->get('max_password_length'))
		{
			$this->set_error('password_change_unsuccessful');
			return FALSE;
		}

		$this->trigger_events('pre_change_password');

		$this->trigger_events('extra_where');

		$query = DB::select('id', 'salt', $this->identity_column, 'username', 'email', 'last_login')
		            ->from($this->tables['users'])
		            ->where($this->identity_column, '=', $identity)
		            ->limit(1)
					->as_object()
					->execute();

		if ($query->count() !== 1)
		{
			$this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
			$this->set_error('password_change_unsuccessful');
			return FALSE;
		}

		$user = $query->current();

		if ( ! ($self = $user->id == $this->session->get('user_id')))
		{
			$this->set_error('identity_mismatch');
			return NULL;
		}

		if (is_string($old))
		{
			$old_password_matches = $this->hash_password_db($user->id, $old);
		}

		$remember = $self ? $this->_remember_code_db($user->id, Cookie::get('remember_code', '0')) : FALSE;

		if ($old === NULL OR $old_password_matches === TRUE)
		{
			//store the new password and reset the remember code so all remembered instances have to re-login
			$hashed_new_password  = $this->hash_password($new, $user->salt);
			$data = array(
			    'password' => $hashed_new_password,
			    'remember_code' => NULL,
			);
			if ($self)
			{
				$data['forgotten_password_code'] = NULL;
				$data['forgotten_password_time'] = NULL;
				$data['last_login'] = time();
			}

			$this->trigger_events('extra_where');

			$affected_rows = DB::update($this->tables['users'])
								->set($data)
								->where($this->identity_column, '=', $identity)
								->execute();

			if ($successfully_changed_password_in_db = ($affected_rows === 1))
			{
				if ($self)
				{
					$this->session->restart();
				
					$this->set_session($user);
					$this->clear_login_attempts($identity);
				
					if ($remember AND $this->config->get('remember_users'))
					{
						$this->remember_user($user->id);
					}
					else
					{
						Cookie::delete('identity');
						Cookie::delete('remember_code');
					}
				}

				$this->trigger_events(array('post_change_password', 'post_change_password_successful'));
				$this->set_message('password_change_successful');
			}
			else
			{
				$this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
				$this->set_error('password_change_unsuccessful');
			}

			return $successfully_changed_password_in_db;
		}

		$this->set_error('password_change_unsuccessful');
		return FALSE;
	}

	/**
	 * Insert a forgotten password key.
	 *
	 * @return bool
	 * @author Mathew
	 * @updated Ryan
	 * @updated 52aa456eef8b60ad6754b31fbdcc77bb
	 * @kohana Eugene Kudelia
	 */
	public function forgotten_password($identity)
	{
		if (empty($identity))
		{
			$this->trigger_events(array('post_forgotten_password', 'post_forgotten_password_unsuccessful'));
			return FALSE;
		}

		//All some more randomness
		$activation_code_part = "";
		if (function_exists("openssl_random_pseudo_bytes"))
		{
			$activation_code_part = openssl_random_pseudo_bytes(128);
		}
		
		for ($i = 0; $i < 1024; $i++)
		{
			$activation_code_part = sha1($activation_code_part.mt_rand().microtime());
		}
		
		$key = $this->hash_code($activation_code_part.$identity);

		$this->forgotten_password_code = $key;

		$this->trigger_events('extra_where');

		$update = array(
		    'forgotten_password_code' => $key,
		    'forgotten_password_time' => time()
		);

		$affected_rows = DB::update($this->tables['users'])
							->set($update)
							->where($this->identity_column, '=', $identity)
							->execute();

		if ($return = ($affected_rows === 1))
		{
			$this->trigger_events(array('post_forgotten_password', 'post_forgotten_password_successful'));
		}
		else
		{
			$this->trigger_events(array('post_forgotten_password', 'post_forgotten_password_unsuccessful'));
		}

		return $return;
	}

	/**
	 * Forgotten Password Complete
	 *
	 * @return string
	 * @author Mathew
	 * @kohana Eugene Kudelia
	 */
	public function forgotten_password_complete($code, $salt = FALSE)
	{
		$this->trigger_events('pre_forgotten_password_complete');

		if (empty($code))
		{
			$this->trigger_events(array('post_forgotten_password_complete', 'post_forgotten_password_complete_unsuccessful'));
			return FALSE;
		}

		$query = DB::select('forgotten_password_time')
		            ->from($this->tables['users'])
		            ->where('forgotten_password_code', '=', $code)
		            ->limit(1)
					->as_object()
					->execute();

		if ($query->count() !== 1)
		{
			$this->trigger_events(array('post_forgotten_password_complete', 'post_forgotten_password_complete_unsuccessful'));
			$this->set_error('forgotten_password_complete_unsuccessful');
			return FALSE;
		}

		$forgotten_password_time = $query->current()->forgotten_password_time; //pass the code to profile

		if (($expiration = $this->config->get('forgot_password_expiration')) > 0)
		{
			//Make sure it isn't expired
			if (time() - $forgotten_password_time > $expiration)
			{
				//it has expired
				$this->set_error('forgot_password_expired');
				$this->trigger_events(array('post_forgotten_password_complete', 'post_forgotten_password_complete_unsuccessful'));

				return FALSE;
			}
		}

		$password = $this->salt();

		$data = array(
		    'password'                => $this->hash_password($password, $salt),
		    'forgotten_password_code' => NULL,
		    'forgotten_password_time' => NULL,
		    'active'                  => 1,
		);

		$affected_rows = DB::update($this->tables['users'])
							->set($data)
							->where('forgotten_password_code', '=', $code)
							->execute();

		if ($affected_rows === 1)
		{
			$this->trigger_events(array('post_forgotten_password_complete', 'post_forgotten_password_complete_successful'));
			return $password;
		}
		else
		{
			$this->trigger_events(array('post_forgotten_password_complete', 'post_forgotten_password_complete_unsuccessful'));
			return FALSE;
		}
	}

	/**
	 * Get identity for given emai
	 *
	 * @return mixed boolean / string
	 * @author Eugene Kudelia
	 */
	public function forgotten_password_identity($email, $login = NULL)
	{
		$identity = $this->config->get('identity');

		$select = DB::select($identity)
					->from($this->tables['users'])
					->where('email', '=', $email);

		if (is_string($login) AND strlen($login) >= $this->config->get('min_username_length'))
		{
			$select = $select->where($identity, '=', $login);
		}

		$result = $select->limit(1)->execute();

		if ($result->count() !== 1)
		{
			$this->set_error($identity.'_not_found');
			return FALSE;
		}

		return $result->get($identity);
	}

	/**
	 * register
	 *
	 * @return bool
	 * @author Mathew
	 * @kohana Eugene Kudelia
	 */
	public function register($username, $password, $email, $additional_data = array(), $groups = array())
	{
		$this->trigger_events('pre_register');

		$not_unique = $this->row_exists($this->tables['users'], 'username', $username);
		if ($this->identity_column == 'email' AND $this->row_exists($this->tables['users'], 'email', $email))
		{
			$this->set_error('account_creation_duplicate_email');
			return FALSE;
		}
		elseif ($this->identity_column == 'username' AND $not_unique)
		{
			$this->set_error('account_creation_duplicate_username');
			return FALSE;
		}

		// If username is taken, use username1 or username2, etc.
		if ($this->identity_column != 'username')
		{
			$original_username = $username;
			for ($i = 0; $not_unique; $i++)
			{
				if ($i > 0)
				{
					$username = $original_username.$i;
				}
			}
		}

		$salt       = $this->store_salt ? $this->salt() : FALSE;
		$password   = $this->hash_password($password, $salt);

		$active = (int) ($this->config->get('manual_activation') === FALSE);

		$cms = 0;
		if (count(array_intersect($this->manager_groups(), $groups)) > 0)
		{
			$cms = 1;
		}
		if (in_array($this->group_id($this->config->get('default_admin')), $groups))
		{
			$cms = 2;
		}

		// Users table.
		$data = array(
		    'username'   => $username,
		    'password'   => $password,
		    'email'      => $email,
		    'ip_address' => $this->_ip_address(),
		    'created_on' => time(),
		    'active'     => $active,
			'cms'        => $cms
		);

		if ($this->store_salt)
		{
			$data['salt'] = $salt;
		}

		//filter out any data passed that doesnt have a matching column in the users table
		//and merge the set user data and the additional data
		$user_data = array_merge($this->_filter_data($this->tables['users'], $additional_data), $data);
		$columns = array_keys($user_data);
		$values = array_values($user_data);

		$this->trigger_events('extra_set');

		list($id, $rows) = DB::insert($this->tables['users'])
								->columns($columns)
								->values($values)
								->execute();

		if ($rows === 1)
		{
			$default_group = $this->group_id($this->config->get('default_group'));

			if ( ! in_array($default_group, $groups))
			{
				array_push($groups, $default_group);
			}

			//add to groups
			foreach ($groups as $group)
			{
				$this->add_to_group($group, $id);
			}

			$this->trigger_events('post_register');
			return $id;
		}

		return FALSE;
	}

	/**
	 * login
	 *
	 * @return bool
	 * @author Mathew
	 * @kohana Eugene Kudelia
	 */
	public function login($identity, $password, $remember = FALSE, $cms_access = FALSE)
	{
		$this->trigger_events('pre_login');

		if (empty($identity) OR empty($password))
		{
			$this->set_error('login_unsuccessful');
			return FALSE;
		}

		if ($this->is_time_locked_out($identity))
		{
			//Hash something anyway, just to take up time
			$this->hash_password($password);

			$this->trigger_events('post_login_unsuccessful');
			$this->set_error('login_timeout');

			return FALSE;
		}

		$this->trigger_events('extra_where');

		$query = DB::select($this->identity_column, 'id',
							'username', 'email', 'password',
							'last_login', 'active', 'cms', 'display_name')
		                  ->from($this->tables['users'])
		                  ->where($this->identity_column, '=', $identity)
		                  ->limit(1)
						  ->as_object()
						  ->execute();

		if ($query->count() === 1)
		{
			$user = $query->current();

			$password = $this->hash_password_db($user->id, $password);

			if ($password === TRUE)
			{
				if ($user->active == 0 OR ($cms_access AND $user->cms == 0))
				{
					$this->increase_login_attempts($identity);
					$this->trigger_events('post_login_unsuccessful');

					$why = $user->active == 0 ? 'not_active' : 'access_denied';
					$this->set_error('login_unsuccessful_'.$why);

					return FALSE;
				}

				$this->set_session($user);
				$this->update_last_login($user->id);
				$this->clear_login_attempts($identity);

				if ($remember AND $this->config->get('remember_users'))
				{
					$this->remember_user($user->id);
				}

				$this->trigger_events(array('post_login', 'post_login_successful'));
				$this->set_message(__('Hi,').' '.($user->display_name ?: $user->username).' !');

				return TRUE;
			}
		}

		// Hash something anyway, just to take up time
		$this->hash_password($password);
		$this->increase_login_attempts($identity);

		$this->trigger_events('post_login_unsuccessful');

		$this->set_error('login_unsuccessful');

		return FALSE;
	}

	/**
	 * is_max_login_attempts_exceeded
	 * Based on code from Tank Auth, by Ilya Konyukhov (https://github.com/ilkon/Tank-Auth)
	 *
	 * @param string $identity
	 * @return boolean
	 * @kohana Eugene Kudelia
	 */
	public function is_max_login_attempts_exceeded($identity)
	{
		if ($this->config->get('track_login_attempts'))
		{
			$max_attempts = $this->config->get('maximum_login_attempts');
			if ($max_attempts > 0)
			{
				$attempts = $this->get_attempts_num($identity);
				return $attempts >= $max_attempts;
			}
		}

		return FALSE;
	}

	/**
	 * Get number of attempts to login occured from given IP-address or identity
	 * Based on code from Tank Auth, by Ilya Konyukhov (https://github.com/ilkon/Tank-Auth)
	 *
	 * @param	string $identity
	 * @return	int
	 * @kohana Eugene Kudelia
	 */
	function get_attempts_num($identity)
	{
		if ($this->config->get('track_login_attempts'))
		{
			$query = DB::select()
						->from($this->tables['login_attempts'])
						->where('ip_address', '=', $this->_ip_address());

			if (strlen($identity) > 0)
			{
				$query->or_where('login', '=', $identity);
			}

			return $query->execute()->count();
		}

		return 0;
	}

	/**
	 * Get a boolean to determine if an account should be locked out due to
	 * exceeded login attempts within a given period
	 *
	 * @return	boolean
	 * @kohana Eugene Kudelia
	 */
	public function is_time_locked_out($identity)
	{
		return $this->is_max_login_attempts_exceeded($identity) AND $this->get_last_attempt_time($identity) > time() - $this->config->get('lockout_time');
	}

	/**
	 * Get the time of the last time a login attempt occured from given IP-address or identity
	 *
	 * @param	string $identity
	 * @return	int
	 * @kohana Eugene Kudelia
	 */
	public function get_last_attempt_time($identity)
	{
		if ($this->config->get('track_login_attempts'))
		{
			$query = DB::select(array(DB::expr('MAX(`time`)'), 'time'))
						->from($this->tables['login_attempts'])
						->where('ip_address', '=', $this->_ip_address());

			if (strlen($identity) > 0)
			{
				$query->or_where('login', '=', $identity);
			}

			$query->limit(1);

			return ($query->execute()->count() > 0) ? $query->execute()->get('time') : 0;
		}

		return 0;
	}

	/**
	 * increase_login_attempts
	 * Based on code from Tank Auth, by Ilya Konyukhov (https://github.com/ilkon/Tank-Auth)
	 *
	 * @param string $identity
	 * @kohana Eugene Kudelia
	 */
	public function increase_login_attempts($identity)
	{
		if ($this->config->get('track_login_attempts'))
		{
			list($id, $rows) = DB::insert($this->tables['login_attempts'])
									->columns(array('ip_address', 'login', 'time'))
									->values(array($this->_ip_address(), $identity, time()))
									->execute();

			return ($rows == 1) ? $id : FALSE;
		}

		return FALSE;
	}

	/**
	 * clear_login_attempts
	 * Based on code from Tank Auth, by Ilya Konyukhov (https://github.com/ilkon/Tank-Auth)
	 *
	 * @param string $identity
	 * @kohana Eugene Kudelia
	 */
	public function clear_login_attempts($identity, $expire_period = 86400)
	{
		if ($this->config->get('track_login_attempts'))
		{
			return DB::delete($this->tables['login_attempts'])
						->where('ip_address', '=', $this->_ip_address())
						->and_where('login', '=', $identity)
						->or_where('time', '<', time() - $expire_period) // Purge obsolete login attempts
						->execute();
		}

		return FALSE;
	}

	/**
	 * Ion Auth Query Builder methods
	 * moved to parent Model_Common
	 * for common use 
	 */

	/**
	 * users
	 *
	 * @return object Users
	 * @author Ben Edmunds
	 * @kohana Eugene Kudelia
	 */
	public function users($groups = NULL)
	{
		$this->trigger_events('users');

		if (isset($this->_select) AND ! empty($this->_select))
		{
			$select = array();
			foreach ($this->_select as $item)
			{
				
				if (is_array($item))
				{
					foreach($item as $col)
					{
						$select[] = $this->tables['users'].'.'.$col;
					}
				}
				elseif (is_string($item))
				{
					$select[] = $this->tables['users'].'.'.$item;
				}
			}

			$this->_query = DB::select_array($select)->from($this->tables['users']);

			$this->_select = array();
		}
		else
		{
			//default selects
			$this->_query = DB::select(
				$this->tables['users'].'.*',
			    array($this->tables['users'].'.id', 'id'),
			    array($this->tables['users'].'.id', 'user_id')
			)->from($this->tables['users']);
		}

		//filter by group id(s) if passed
		if (isset($groups))
		{
			//build an array if only one group was passed
			if (is_numeric($groups))
			{
				$groups = array($groups);
			}

			//join and then run a where_in against the group ids
			if (isset($groups) AND ! empty($groups))
			{
				$this->_query
					->distinct(TRUE)
					->join($this->tables['users_groups'], 'INNER')
					->on($this->tables['users_groups'].'.'.$this->join['users'], '=', $this->tables['users'].'.id')
					->where($this->tables['users_groups'].'.'.$this->join['groups'], 'IN', $groups);

				if (in_array($this->group_id($this->config->get('default_group')), $groups) AND count($groups) == 1)
				{
					//$this->_query->where($this->tables['users'].'.id', 'NOT IN', $this->manager_users());
					$this->_query->where($this->tables['users'].'.cms', '=', 0);
				}
			}
		}

		$this->trigger_events('extra_where');

		//run each where that was passed
		if (isset($this->_where) AND ! empty($this->_where))
		{
			foreach ($this->_where as $where)
			{
				$this->_query->where($where[0], $where[1], $where[2]);
			}

			$this->_where = array();
		}

		//run each or_where that was passed
		if (isset($this->_or_where) AND ! empty($this->_or_where))
		{
			foreach ($this->_or_where as $or_where)
			{
				$this->_query->or_where($or_where[0], $or_where[1], $or_where[2]);
			}

			$this->_or_where = array();
		}

		//if (isset($this->_like) AND ! empty($this->_like))
		//{
		//	foreach ($this->_like as $like)
		//	{
		//		$this->db->or_like($like);
		//	}
		//
		//	$this->_like = array();
		//}

		if (isset($this->_limit) AND isset($this->_offset))
		{
			$this->_query->limit($this->_limit)->offset($this->_offset);

			$this->_limit  = NULL;
			$this->_offset = NULL;
		}
		elseif (isset($this->_limit))
		{
			$this->_query->limit($this->_limit);

			$this->_limit  = NULL;
		}

		//set the order
		if (isset($this->_order_by) AND isset($this->_order))
		{
			$this->_query->order_by($this->_order_by, $this->_order);

			$this->_order_by = NULL;
			$this->_order    = NULL;
		}
		elseif (isset($this->_order_by))
		{
			$this->_query->order_by($this->_order_by);

			$this->_order_by = NULL;
		}

		return $this;
	}

	/**
	 * users_count
	 *
	 * @return integer Total number of rows in Users table
	 * @author Eugene Kudelia
	 */
	public function users_count()
	{
		return DB::select('id')
					->from($this->tables['users'])
					->execute()
					->count();
	}

	/**
	 * manager users
	 *
	 * @return array List of Manager User ID's
	 * @author Eugene Kudelia
	 */
	public function manager_users()
	{
		$query = DB::select($this->tables['users'].'.id')
					->from($this->tables['users'])
					->distinct(TRUE)
					->join($this->tables['users_groups'], 'INNER')
					->on($this->tables['users_groups'].'.'.$this->join['users'], '=', $this->tables['users'].'.id')
					->where($this->tables['users_groups'].'.'.$this->join['groups'], 'IN', $this->manager_groups())
					->execute();

		$users = array();
		foreach ($query as $user)
		{
			$users[] = $user['id'];
		}
		return $users;
	}

	/**
	 * manager groups
	 *
	 * @return array List of group IDs with cms access
	 * @author Eugene Kudelia
	 */
	public function manager_groups()
	{
		$query = DB::select('id', $this->config->get('cms_access'))
					->from($this->tables['groups'])
					->where($this->config->get('cms_access'), '=', 1)
					->execute();

		$groups = array();
		foreach ($query as $group)
		{
			$groups[] = $group['id'];
		}
		return $groups;
	}

	/**
	 * group id
	 *
	 * @return integer Group ID
	 * @author Eugene Kudelia
	 */
	public function group_id($name)
	{
		if (isset($this->_group_id[$name]))
		{
			return $this->_group_id[$name];
		}

		$this->_group_id[$name] = DB::select('id')
			->from($this->tables['groups'])
			->where('name', '=', $name)
			->limit(1)
			->execute()
			->get('id');

		return $this->_group_id[$name];
	}

	/**
	 * user
	 *
	 * @return object
	 * @author Ben Edmunds
	 * @kohana Eugene Kudelia
	 */
	public function user($id = NULL)
	{
		$this->trigger_events('user');

		//if no id was passed use the current users id
		$id OR $id = $this->session->get('user_id');

		$this->where($this->tables['users'].'.id', '=', $id)
			->limit(1)
			->users();

		return $this;
	}

	/**
	 * get_users_groups
	 *
	 * @return array
	 * @author Ben Edmunds
	 * @kohana Eugene Kudelia
	 */
	public function get_users_groups($id = FALSE, $as_object = FALSE, $order_by = NULL)
	{
		$this->trigger_events('get_users_group');

		//if no id was passed use the current users id
		$id OR $id = $this->session->get('user_id');

		$query =  DB::select(
			array($this->tables['users_groups'].'.'.$this->join['groups'], 'id'),
			$this->tables['groups'].'.name',
			$this->tables['groups'].'.title',
			$this->tables['groups'].'.cms'
		)
			->from($this->tables['users_groups'])
			->where($this->tables['users_groups'].'.'.$this->join['users'], '=', $id)
			->join($this->tables['groups'])
			->on($this->tables['users_groups'].'.'.$this->join['groups'], '=', $this->tables['groups'].'.id');

		if (is_array($order_by))
		{
			$order = $order_by[$column = key($order_by)] ;
			$query->order_by($this->tables['groups'].'.'.$column, $order);
		}

		if ($as_object)
		{
			$query->as_object();
		}

		return $query->execute();
	}

	/**
	 * get user permissions
	 *
	 * @return array
	 * @author Eugene Kudelia
	 */
	public function get_user_permissions($id = FALSE, $groups_update = FALSE)
	{
		$this->trigger_events('get_users_permissions');

		//if no id was passed use the current users id
		$id OR $id = $this->session->get('user_id');

		if (isset($this->_cache_user_permissions[$id]) AND ! $groups_update)
		{
			return $this->_cache_user_permissions[$id];
		}

		$query = DB::select($this->tables['groups'].'.permissions')
			->from($this->tables['groups'])
			->join($this->tables['users_groups'])
			->on($this->tables['groups'].'.id', '=', $this->tables['users_groups'].'.'.$this->join['groups'])
			->where($this->tables['users_groups'].'.'.$this->join['users'], '=', $id)
			->execute();

		if ($query->count() > 0)
		{
			$result = array();
			foreach ($query as $group)
			{
				//empty($group['permissions']) OR $r += unserialize($group['permissions']);
				$perms = ! empty($group['permissions']) ? unserialize($group['permissions']) : array();
				foreach ($perms as $p)
				{
					$result[] = $p;
				}
			}

			$this->_cache_user_permissions[$id] = array_unique($result);
			return $this->_cache_user_permissions[$id];
		}

		return array();
	}

	/**
	 * add_to_group
	 *
	 * @return bool
	 * @author Ben Edmunds
	 * @kohana Eugene Kudelia
	 */
	public function add_to_group($group_id, $user_id = FALSE)
	{
		$this->trigger_events('add_to_group');

		//if no id was passed use the current users id
		$user_id OR $user_id = $this->session->get('user_id');

		$rows = DB::select()
						->from($this->tables['users_groups'])
						->where($this->join['groups'], '=', (int) $group_id)
						->where($this->join['users'], '=', (int) $user_id)
						->execute()
						->count();

		//check if unique - count() > 0 means row found
		if ($rows > 0)
		{
			return FALSE;
		}

		unset($rows);

		list($id, $rows) = DB::insert($this->tables['users_groups'])
								->columns(array($this->join['groups'], $this->join['users']))
								->values(array((int) $group_id, (int) $user_id))
								->execute();

		if ($return = ($rows == 1))
		{
			if (isset($this->_cache_groups[$group_id]))
			{
				$group_name = $this->_cache_groups[$group_id];
			}
			else
			{
				$group_name = $this->group($group_id)->result()->get('group');

				$this->_cache_groups[$group_id] = $group_name;
			}

			$this->_cache_user_in_group[$user_id][$group_id] = $group_name;
		}

		return $return;
	}

	/**
	 * remove_from_group
	 *
	 * @return bool
	 * @author Ben Edmunds
	 * @kohana Eugene Kudelia
	 */
	public function remove_from_group($user_id = FALSE, $group_ids = NULL)
	{
		$this->trigger_events('remove_from_group');

		// user id is required
		if (empty($user_id))
		{
			return FALSE;
		}

		// if group id(s) are passed remove user from the group(s)
		if ( ! empty($group_ids))
		{
			if ( ! is_array($group_ids))
			{
				$group_ids = array($group_ids);
			}

			foreach($group_ids as $group_id)
			{
				$result = DB::delete($this->tables['users_groups'])
							->where($this->join['groups'], '=', (int) $group_id)
							->where($this->join['users'], '=', (int) $user_id)
							->execute();

				if ($result AND isset($this->_cache_user_in_group[$user_id]) AND isset($this->_cache_user_in_group[$user_id][$group_id]))
				{
					unset($this->_cache_user_in_group[$user_id][$group_id]);
				}

				$r[] = $result;
			}

			return ! in_array(0, $r);
		}
		// otherwise remove user from all groups
		else
		{
			$return = DB::delete($this->tables['users_groups'])
						->where($this->join['users'], '=', (int) $user_id)
						->execute();

			if ($return)
			{
				$this->_cache_user_in_group[$user_id] = array();
			}

			return (bool) $return;
		}
	}

	/**
	 * groups
	 *
	 * @return object
	 * @author Ben Edmunds
	 * @kohana Eugene Kudelia
	 */
	public function groups()
	{
		$this->trigger_events('groups');

		if (isset($this->_select) AND ! empty($this->_select))
		{
			$select = array();
			foreach ($this->_select as $item)
			{
				
				if (is_array($item))
				{
					foreach($item as $col)
					{
						$select[] = $this->tables['groups'].'.'.$col;
					}
				}
				elseif (is_string($item))
				{
					$select[] = $this->tables['groups'].'.'.$item;
				}
			}

			$this->_query = DB::select_array($select)->from($this->tables['groups']);

			$this->_select = array();
		}
		else
		{
			$this->_query = DB::select()->from($this->tables['groups']);
		}

		//run each where that was passed
		if (isset($this->_where) AND ! empty($this->_where))
		{
			foreach ($this->_where as $where)
			{
				$this->_query->where($where[0], $where[1], $where[2]);
			}

			$this->_where = array();
		}

		//run each or_where that was passed
		if (isset($this->_or_where) AND ! empty($this->_or_where))
		{
			foreach ($this->_or_where as $or_where)
			{
				$this->_query->or_where($or_where[0], $or_where[1], $or_where[2]);
			}

			$this->_or_where = array();
		}

		if (isset($this->_limit) AND isset($this->_offset))
		{
			$this->_query->limit($this->_limit, $this->_offset);

			$this->_limit  = NULL;
			$this->_offset = NULL;
		}
		elseif (isset($this->_limit))
		{
			$this->_query->limit($this->_limit);

			$this->_limit  = NULL;
		}

		//set the order
		if (isset($this->_order_by) AND isset($this->_order))
		{
			$this->_query->order_by($this->_order_by, $this->_order);

			$this->_order_by = NULL;
			$this->_order    = NULL;
		}
		elseif (isset($this->_order_by))
		{
			$this->_query->order_by($this->_order_by);

			$this->_order_by = NULL;
		}

		return $this;
	}

	/**
	 * group
	 *
	 * @return object
	 * @author Ben Edmunds
	 * @kohana Eugene Kudelia
	 */
	public function group($id = NULL)
	{
		$this->trigger_events('group');

		if (isset($id))
		{
			$this->where($this->tables['groups'].'.id', '=', $id);
		}

		$this->limit(1)->groups();

		return $this;
	}

	/**
	 * group by name
	 *
	 * @return mixed
	 * @author Eugene Kudelia
	 */
	public function group_by_name($name = '')
	{
		if (empty($name))
		{
			return FALSE;
		}

		$this->trigger_events('group_by_name');

		$this->where($this->tables['groups'].'.name', '=', $name);
		$this->limit(1)->groups();

		return $this;
	}

	/**
	 * update
	 *
	 * @return bool
	 * @author Phil Sturgeon
	 * @kohana Eugene Kudelia
	 */
	public function update($id, array $data)
	{
		$this->trigger_events('pre_update_user');

		$user = $this->user($id)->result();

		if (array_key_exists($this->identity_column, $data)
			AND $this->row_exists($this->tables['users'], $this->identity_column, $data[$this->identity_column])
			AND $user->get($this->identity_column) !== $data[$this->identity_column])
		{
			$this->set_error('account_creation_duplicate_'.$this->identity_column);

			$this->trigger_events(array('post_update_user', 'post_update_user_unsuccessful'));
			$this->set_error('update_unsuccessful');
			return FALSE;
		}

		$db = Database::instance();
		$db->begin();

		// Filter the data passed
		$data = $this->_filter_data($this->tables['users'], $data, $db);

		if (array_key_exists('username', $data) OR array_key_exists('password', $data) OR array_key_exists('email', $data))
		{
			if (array_key_exists('password', $data))
			{
				if ( ! empty($data['password']))
				{
					$data['password'] = $this->hash_password($data['password'], $user->get('salt'));
				}
				else
				{
					// unset password so it doesn't effect database entry if no password passed
					unset($data['password']);
				}
			}
		}

		$data['edited_on'] = time();
		$data['edited_by'] = $this->session->get('user_id');

		try
		{
			$this->trigger_events('extra_where');

			$return = DB::update($this->tables['users'])
						->set($data)
						->where('id', '=', $user->get('id'))
						->execute();

			$db->commit();

			$return = ($return === 1);

			$this->trigger_events(array('post_update_user', 'post_update_user_successful'));
			$this->set_message('update_successful');
		}
		catch (Database_Exception $e)
		{
			$db->rollback();

			$return = FALSE;

			$this->trigger_events(array('post_update_user', 'post_update_user_unsuccessful'));
			$this->set_error('update_unsuccessful');
		}

		return $return;
	}

	/**
	 * delete_user
	 *
	 * @return bool
	 * @author Phil Sturgeon
	 * @kohana Eugene Kudelia
	 */
	public function delete_user($id)
	{
		$this->trigger_events('pre_delete_user');

		$db = Database::instance();
		$db->begin();

		try
		{
			// remove user from groups
			$this->remove_from_group($id);

			// delete user from users table
			// should be placed after remove from group
			$return = DB::delete($this->tables['users'])
						->where('id', '=', $id)
						->execute();

			$db->commit();

			// if user does not exist in database then it returns FALSE
			// else removes the user from groups
			$return = ($return > 0);

			$this->trigger_events(array('post_delete_user', 'post_delete_user_successful'));
			$this->set_message('delete_successful');
		}
		catch (Database_Exception $e)
		{
			$db->rollback();

			$return = FALSE;

			$this->trigger_events(array('post_delete_user', 'post_delete_user_unsuccessful'));
			$this->set_error('delete_unsuccessful');
			$this->set_error($db::text($e));
		}

		return $return;
	}

	/**
	 * update_last_login
	 *
	 * @return bool
	 * @author Ben Edmunds
	 * @kohana Eugene Kudelia
	 */
	public function update_last_login($id)
	{
		$this->trigger_events('update_last_login');

		$this->trigger_events('extra_where');

		$data = array(
			'last_login' => time(),
			'last_login_ip' => $this->_ip_address(),
			'login_count' => DB::expr('login_count + 1')
		);

		$affected_rows = DB::update($this->tables['users'])
							->set($data)
							->where('id', '=', $id)
							->execute();

		return $affected_rows === 1;
	}

	/**
	 * set_session
	 *
	 * @return bool
	 * @author jrmadsen67
	 * @kohana Eugene Kudelia
	 */
	public function set_session($user)
	{
		$this->trigger_events('pre_set_session');

		$session_data = array(
		    'identity'			=> $user->{$this->identity_column},
		    'username'			=> $user->username,
		    'email'				=> $user->email,
		    'user_id'			=> $user->id, //everyone likes to overwrite id so we'll use user_id
		    'old_last_login'	=> $user->last_login
		);

		foreach ($session_data as $key => $value)
		{
			$this->session->set($key, $value);
		}

		$this->trigger_events('post_set_session');

		return TRUE;
	}

	/**
	 * session reset
	 *
	 * @return bool
	 * @author Eugene Kudelia
	 */
	public function session_reset()
	{
		if ($this->session->restart())
		{
			// delete the remember and identity cookies if they exist
			if (Cookie::get('identity'))
			{
				Cookie::delete('identity');
			}
			if (Cookie::get('remember_code'))
			{
				Cookie::delete('remember_code');
			}
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * remember_user
	 *
	 * @return bool
	 * @author Ben Edmunds
	 * @kohana Eugene Kudelia
	 */
	public function remember_user($id = FALSE)
	{
		$this->trigger_events('pre_remember_user');

		if ( ! $id)
		{
			return FALSE;
		}

		$user = $this->user($id)->row();
		$salt = sha1($user->password);

		$affected_rows = DB::update($this->tables['users'])
							->set(array('remember_code' => $salt))
							->where('id', '=', $id)
							->execute();

		if ($affected_rows > -1)
		{
			// if the user_expire is set to zero we'll set the expiration 6 months from now.
			if ($this->config->get('user_expire') === 0)
			{
				$expire = (int) 16E6; // ~= 185 days
			}
			// otherwise use what is set
			else
			{
				$expire = $this->config->get('user_expire');
			}

			Cookie::set('identity', $user->{$this->identity_column}, $expire);
			Cookie::set('remember_code', $salt, $expire);

			$this->trigger_events(array('post_remember_user', 'remember_user_successful'));

			return TRUE;
		}

		$this->trigger_events(array('post_remember_user', 'remember_user_unsuccessful'));

		return FALSE;
	}

	/**
	 * login_remembered_user
	 *
	 * @return bool
	 * @author Ben Edmunds
	 * @kohana Eugene Kudelia
	 */
	public function login_remembered_user()
	{
		$this->trigger_events('pre_login_remembered_user');

		//check for valid data
		if ( ! Cookie::get('identity', '')
			OR ! Cookie::get('remember_code', '')
			OR ! $this->row_exists($this->tables['users'], $this->identity_column, Cookie::get('identity', '')))
		{
			$this->trigger_events(array('post_login_remembered_user', 'post_login_remembered_user_unsuccessful'));

			return FALSE;
		}

		//get the user
		$this->trigger_events('extra_where');

		$query = DB::select($this->identity_column, 'id', 'username', 'email', 'last_login')
					->from($this->tables['users'])
					->where($this->identity_column, '=', Cookie::get('identity'))
					->where('remember_code', '=', Cookie::get('remember_code'))
					->limit(1)
					->as_object()
					->execute();

		//if the user was found, sign them in
		if ($query->count() === 1)
		{
			$user = $query->current();
			$this->update_last_login($user->id);
			$this->set_session($user);

			//extend the users cookies if the option is enabled
			if ($this->config->get('user_extend_on_login'))
			{
				$this->remember_user($user->id);
			}

			$this->trigger_events(array('post_login_remembered_user', 'post_login_remembered_user_successful'));

			return TRUE;
		}

		$this->trigger_events(array('post_login_remembered_user', 'post_login_remembered_user_unsuccessful'));

		return FALSE;
	}

	/**
	 * Creates a new group
	 *
	 * @return bool
	 * @author Eugene Kudelia
	 */
	public function create_group($name, $title = NULL, $cms = 0)
	{
		if ( ! $name)
		{
			$this->set_error('new_group_name_required');

			return FALSE;
		}

		$this->trigger_events('pre_create_group');

		! is_string($title) OR $title = trim($title);
		! empty($title) OR $title = ucfirst($name);

		if ($this->row_exists($this->tables['groups'], 'name', $name))
		{
			$this->set_error('group_already_exists');

			return FALSE;
		}

		list($id, $rows) = DB::insert($this->tables['groups'])
							->columns(array('name', 'title', 'cms'))
							->values(array($name, $title, $cms))
							->execute();

		if (is_numeric($id) AND $rows === 1)
		{
			$this->trigger_events(array('post_create_group', 'post_create_group_successful'));
			$this->set_message('create_group_successful');

			return $id;
		}

		$this->trigger_events(array('post_create_group', 'post_create_group_unsuccessful'));
		$this->set_error('create_group_unsuccessful');

		return FALSE;
	}
	
	/**
	 * Renames an existing group
	 *
	 * @return bool
	 * @author Eugene Kudelia
	 */
	public function rename_group($id, $title)
	{
		if ( ! $id OR ! $title)
		{
			$this->set_error('Each of arguments ( $id, $name ) must be a non-empty string');

			return FALSE;
		}

		if ($this->row_exists($this->tables['groups'], 'title', $title))
		{
			$this->set_error('group_title_already_exists');

			return FALSE;
		}

		$affected_rows = DB::update($this->tables['groups'])
							->set(array('title' => $title))
							->where('id', '=', $id)
							->execute();

		if ($affected_rows === 1)
		{
			$this->set_message('set_new_group_title_successful');
		}
		else
		{
			$this->set_error('set_new_group_title_unsuccessful');
		}

		return $affected_rows === 1;
	}
	
	/**
	 * Set group permissions
	 *
	 * @return bool
	 * @author Eugene Kudelia
	 */
	public function group_permissions($id, $permissions)
	{
		if ( ! $id OR ! $permissions)
		{
			$this->set_error('Each of arguments ( $id, $permissions ) must be a non-empty string');

			return FALSE;
		}

		$affected_rows = DB::update($this->tables['groups'])
							->set(array('permissions' => $permissions))
							->where('id', '=', $id)
							->execute();

		if ($affected_rows === 1)
		{
			$this->set_message('set_permissions_successful');
		}
		else
		{
			$this->set_error('set_permissions_unsuccessful');
		}

		return $affected_rows === 1;
	}
	
	/**
	 * Deletes an existing group
	 *
	 * @return bool
	 * @author Eugene Kudelia
	 */
	public function delete_group($id)
	{
		if ( ! is_numeric($id) OR ! $id)
		{
			$this->set_error('Argument ($id) must be a positive number');

			return FALSE;
		}

		$this->trigger_events('pre_delete_group');

		$db = Database::instance();
		$db->begin();

	    try
		{
			$this->trigger_events('extra_where');

			DB::delete($this->tables['users_groups'])
				->where($this->join['groups'], '=', $id)
				->execute();

			$return = DB::delete($this->tables['groups'])
						->where('id', '=', $id)
						->execute();

			$db->commit();

			$return = ($return > 0);

			$this->trigger_events(array('post_delete_group', 'post_delete_group_successful'));
			$this->set_message('group_delete_successful');
		}
		catch (Database_Exception $e)
		{
			$db->rollback();

			$return = FALSE;

			$this->trigger_events(array('post_delete_group', 'post_delete_group_unsuccessful'));
			$this->set_error('group_delete_unsuccessful');
			$this->set_error($db::text($e));
		}

	    return $return;
	}

	public function set_hook($event, $name, $class, $method, $arguments)
	{
		$this->_ion_hooks->{$event}[$name] = new stdClass;
		$this->_ion_hooks->{$event}[$name]->class     = $class;
		$this->_ion_hooks->{$event}[$name]->method    = $method;
		$this->_ion_hooks->{$event}[$name]->arguments = $arguments;
	}

	public function remove_hook($event, $name)
	{
		if (isset($this->_ion_hooks->{$event}[$name]))
		{
			unset($this->_ion_hooks->{$event}[$name]);
		}
	}

	public function remove_hooks($event)
	{
		if (isset($this->_ion_hooks->$event))
		{
			unset($this->_ion_hooks->$event);
		}
	}

	protected function _call_hook($event, $name)
	{
		if (isset($this->_ion_hooks->{$event}[$name]) AND method_exists($this->_ion_hooks->{$event}[$name]->class, $this->_ion_hooks->{$event}[$name]->method))
		{
			$hook = $this->_ion_hooks->{$event}[$name];

			return call_user_func_array(array($hook->class, $hook->method), $hook->arguments);
		}

		return FALSE;
	}

	public function trigger_events($events)
	{
		if (is_array($events) AND ! empty($events))
		{
			foreach ($events as $event)
			{
				$this->trigger_events($event);
			}
		}
		else
		{
			if (isset($this->_ion_hooks->$events) AND ! empty($this->_ion_hooks->$events))
			{
				foreach ($this->_ion_hooks->$events as $name => $hook)
				{
					$this->_call_hook($events, $name);
				}
			}
		}
	}

	/**
	 * set_message_delimiters
	 *
	 * Set the message delimiters
	 *
	 * @return void
	 * @author Ben Edmunds
	 */
	public function set_message_delimiters($start_delimiter, $end_delimiter)
	{
		$this->message_start_delimiter = $start_delimiter;
		$this->message_end_delimiter   = $end_delimiter;

		return TRUE;
	}

	/**
	 * set_error_delimiters
	 *
	 * Set the error delimiters
	 *
	 * @return void
	 * @author Ben Edmunds
	 */
	public function set_error_delimiters($start_delimiter, $end_delimiter)
	{
		$this->error_start_delimiter = $start_delimiter;
		$this->error_end_delimiter   = $end_delimiter;

		return TRUE;
	}

	/**
	 * set_message
	 *
	 * Set a message
	 *
	 * @return void
	 * @author Ben Edmunds
	 * @kohana Eugene Kudelia
	 */
	public function set_message($message)
	{
		$this->messages[] = ion__($message);

		return $message;
	}

	/**
	 * messages
	 *
	 * Get the messages
	 *
	 * @return void
	 * @author Ben Edmunds
	 * @kohana Eugene Kudelia
	 */
	public function messages()
	{
		$_output = '';
		foreach ($this->messages as $message)
		{
			$_output .= $this->message_start_delimiter.$message.$this->message_end_delimiter;
		}

		return $_output;
	}

	/**
	 * messages as array
	 *
	 * Get the messages as an array
	 *
	 * @return array
	 * @author Raul Baldner Junior
	 * @kohana Eugene Kudelia
	 */
	public function messages_array()
	{
		$_output = array();
		foreach ($this->messages as $message)
		{
			$_output[] = $this->message_start_delimiter.$message.$this->message_end_delimiter;
		}

		return $_output;
	}

	/**
	 * set_error
	 *
	 * Set an error message
	 *
	 * @return void
	 * @author Ben Edmunds
	 * @kohana Eugene Kudelia
	 */
	public function set_error($error)
	{
		$this->errors[] = ion__($error);

		return $error;
	}

	/**
	 * errors
	 *
	 * Get the error message
	 *
	 * @return void
	 * @author Ben Edmunds
	 * @kohana Eugene Kudelia
	 */
	public function errors()
	{
		$_output = '';
		foreach ($this->errors as $error)
		{
			$_output .= $this->error_start_delimiter.$error.$this->error_end_delimiter;
		}

		return $_output;
	}

	/**
	 * errors as array
	 *
	 * Get the error messages as an array
	 *
	 * @return array
	 * @author Raul Baldner Junior
	 * @kohana Eugene Kudelia
	 */
	public function errors_array()
	{
		$_output = array();
		foreach ($this->errors as $error)
		{
			$_output[] = $this->error_start_delimiter.$error.$this->error_end_delimiter;
		}

		return $_output;
	}

	/**
	 * @kohana Eugene Kudelia
	 */
	protected function _prepare_ip($ip_address)
	{
		$db_type = $this->db_type();

		// Fake. To be update for Kohana
		if ($db_type === 'postgre' OR $db_type === 'sqlsrv' OR $db_type === 'mssql')
		{
			return $ip_address;
		}

		return inet_pton($ip_address);
	}

	/**
	 *
	 */
	protected function _ip_address()
	{
		if ( ! isset($this->_ip_address))
		{
			$this->_ip_address = $this->_prepare_ip(Request::$client_ip);
		}

		return $this->_ip_address;
	}

	/**
	 *
	 */
	private function _remember_code_db($id, $remember_code)
	{
		$query = DB::select('remember_code')
					->from($this->tables['users'])
					->where('id', '=', $id)
					->where('remember_code', '=', $remember_code)
					->limit(1)
					->execute();

		return $query->count() === 1;
	}

} // End Model_Ion_Auth
