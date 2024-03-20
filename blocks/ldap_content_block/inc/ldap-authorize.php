
<?php

/**
	 * Validate this user's credentials against LDAP.
	 *
	 * @param  array  $auth_settings Plugin settings.
	 * @param  string $username      Attempted username from authenticate action.
	 * @param  string $password      Attempted password from authenticate action.
	 * @param  array  $debug         If provided, filled with an array of debug
	 *                               messages. Defaults to null.
	 *
	 * @return array|WP_Error        Array containing 'email' and 'authenticated_by' strings
	 *                               for the successfully authenticated user, or WP_Error()
	 *                               object on failure, or null if skipping LDAP auth and
	 *                               falling back to WP auth.
	 */
	function authenticate_ldap( $auth_settings, $username, $password, &$debug = null ) {
		// Make sure all LDAP settings are defined (user and password can be
		// overridden by constant or filter and may not exist in auth_settings).
		$defaults      = array(
			'ldap'                      => '',
			'ldap_host'                 => '',
			'ldap_port'                 => '389',
			'ldap_tls'                  => '1',
			'ldap_search_base'          => '',
			'ldap_search_filter'        => '',
			'ldap_uid'                  => 'uid',
			'ldap_attr_email'           => '',
			'ldap_user'                 => '',
			'ldap_password'             => '',
			'ldap_lostpassword_url'     => '',
			'ldap_attr_first_name'      => '',
			'ldap_attr_last_name'       => '',
			'ldap_attr_update_on_login' => '',
			'ldap_test_user'            => '',
		);
		$auth_settings = wp_parse_args( $auth_settings, $defaults );

		// Initialize debug array if a variable was passed in.
		if ( ! is_null( $debug ) ) {
			$debug = array(
				/* TRANSLATORS: Current time */
				sprintf( __( '[%s] Attempting to authenticate via LDAP.', 'authorizer' ), wp_date( get_option( 'time_format' ) ) ),
			);
		}

		// Get LDAP host(s), and attempt each until we have a valid connection.
		$ldap_hosts = explode( "\n", str_replace( "\r", '', trim( $auth_settings['ldap_host'] ) ) );

		// Fail silently (fall back to WordPress authentication) if no LDAP host specified.
		if ( count( $ldap_hosts ) < 1 ) {
			if ( is_array( $debug ) ) {
				$debug[] = __( 'Failed: no LDAP Host(s) specified.', 'authorizer' );
			}
			return null;
		}

		// Get LDAP search base(s).
		$search_bases = explode( "\n", str_replace( "\r", '', trim( $auth_settings['ldap_search_base'] ) ) );

		// Fail silently (fall back to WordPress authentication) if no search base specified.
		if ( count( $search_bases ) < 1 ) {
			if ( is_array( $debug ) ) {
				$debug[] = __( 'Failed: no LDAP Search Base(s) specified.', 'authorizer' );
			}
			return null;
		}

		// Get the FQDN from the first LDAP search base domain components (dc). For
		// example, ou=people,dc=example,dc=edu,dc=uk would yield user@example.edu.uk.
		$search_base_components = explode( ',', trim( $search_bases[0] ) );
		$domain                 = array();
		foreach ( $search_base_components as $search_base_component ) {
			$component = explode( '=', $search_base_component );
			if ( 2 === count( $component ) && 'dc' === $component[0] ) {
				$domain[] = $component[1];
			}
		}
		$domain = implode( '.', $domain );

		// If we can't get the logging in user's email address from an LDAP attribute,
		// just use the domain from the LDAP host. This will only be used if we
		// can't discover the email address from an LDAP attribute.
		if ( empty( $domain ) ) {
			$domain = preg_match( '/[^.]*\.[^.]*$/', $ldap_hosts[0], $matches ) === 1 ? $matches[0] : '';
		}

		// remove @domain if it exists in the username (i.e., if user entered their email).
		$username = str_replace( '@' . $domain, '', $username );

		// Fail silently (fall back to WordPress authentication) if both username
		// and password are empty (this will be the case when visiting wp-login.php
		// for the first time, or when clicking the Log In button without filling
		// out either field.
		if ( empty( $username ) && empty( $password ) ) {
			if ( is_array( $debug ) ) {
				$debug[] = __( 'Failed: empty username and password.', 'authorizer' );
			}
			return null;
		}

		// Fail with error message if username or password is blank.
		if ( empty( $username ) ) {
			if ( is_array( $debug ) ) {
				$debug[] = __( 'Failed: empty username.', 'authorizer' );
			}
			return new \WP_Error( 'empty_username', __( 'You must provide a username or email.', 'authorizer' ) );
		}
		if ( empty( $password ) ) {
			if ( is_array( $debug ) ) {
				$debug[] = __( 'Failed: empty password.', 'authorizer' );
			}
			return new \WP_Error( 'empty_password', __( 'You must provide a password.', 'authorizer' ) );
		}

		// If php5-ldap extension isn't installed on server, fall back to WP auth.
		if ( ! function_exists( 'ldap_connect' ) ) {
			if ( is_array( $debug ) ) {
				$debug[] = __( 'Failed: php-ldap extension not installed.', 'authorizer' );
			}
			return null;
		}

		// Authenticate against LDAP using options provided in plugin settings.
		$result       = false;
		$ldap_user_dn = '';
		$first_name   = '';
		$last_name    = '';
		$email        = '';

		// Attempt each LDAP host until we have a valid connection.
		$ldap_valid = false;
		foreach ( $ldap_hosts as $ldap_host ) {
			// Construct LDAP connection parameters. ldap_connect() takes either a
			// hostname or a full LDAP URI as its first parameter (works with OpenLDAP
			// 2.x.x or later). If it's an LDAP URI, the second parameter, $port, is
			// ignored, and port must be specified in the full URI. An LDAP URI is of
			// the form ldap://hostname:port or ldaps://hostname:port.
			$ldap_port   = intval( $auth_settings['ldap_port'] );
			$parsed_host = wp_parse_url( $ldap_host );

			// Fail if invalid host is specified.
			if ( false === $parsed_host ) {
				if ( is_array( $debug ) ) {
					/* TRANSLATORS: LDAP Host */
					$debug[] = sprintf( __( 'Warning: could not parse host %s with wp_parse_url().', 'authorizer' ), $ldap_host );
				}
				continue;
			}

			// Create LDAP connection.
			$ldap = ldap_connect( $ldap_host, $ldap_port );
			ldap_set_option( $ldap, LDAP_OPT_PROTOCOL_VERSION, 3 );
			ldap_set_option( $ldap, LDAP_OPT_REFERRALS, 0 );

			// Fail if we don't have a plausible LDAP URI.
			if ( false === $ldap ) {
				if ( is_array( $debug ) ) {
					/* TRANSLATORS: LDAP Host */
					$debug[] = sprintf( __( 'Warning: syntax check failed on host %s in ldap_connect().', 'authorizer' ), $ldap_host );
				}
				continue;
			}

			// Attempt to start TLS if that setting is checked and we're not using ldaps protocol.
			if ( 1 === intval( $auth_settings['ldap_tls'] ) && false === strpos( $ldap_host, 'ldaps://' ) ) {
				// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				if ( ! @ldap_start_tls( $ldap ) ) {
					if ( is_array( $debug ) ) {
						/* TRANSLATORS: LDAP Host */
						$debug[] = sprintf( __( 'Warning: unable to start TLS on host %s:', 'authorizer' ), $ldap_host );
						$debug[] = ldap_error( $ldap );
					}
					continue;
				}
			}

			// Set bind credentials; attempt an anonymous bind if not provided.
			$bind_rdn      = null;
			$bind_password = null;
			if ( strlen( $auth_settings['ldap_user'] ) > 0 ) {
				$bind_rdn      = $auth_settings['ldap_user'];
				$bind_password = $auth_settings['ldap_password'];

				// Decrypt LDAP password if coming from wp_options database (not needed
				// if it was provided via constant or filter).
				/* if ( ! defined( 'AUTHORIZER_LDAP_PASSWORD' ) && ! has_filter( 'authorizer_ldap_password' ) ) {
					$bind_password = Helper::decrypt( $bind_password );
				} */

				// If the bind user contains the [username] wildcard, replace it with
				// the username and password of the user logging in.
				if ( false !== strpos( $bind_rdn, '[username]' ) ) {
					$bind_rdn      = str_replace( '[username]', $username, $bind_rdn );
					$bind_password = $password;

					if ( is_array( $debug ) ) {
						/* TRANSLATORS: LDAP User DN */
						$debug[] = sprintf( __( 'Performing bind as user logging in: %s.', 'authorizer' ), $bind_rdn );
					}
				}
			}

			// Attempt LDAP bind.
			$result = @ldap_bind( $ldap, $bind_rdn, stripslashes( $bind_password ) ); // phpcs:ignore
			if ( ! $result ) {
				if ( is_array( $debug ) ) {
					/* TRANSLATORS: LDAP Host */
					$debug[] = sprintf( __( 'Warning: unable to bind on host %1$s using directory user:', 'authorizer' ), $ldap_host );
					$debug[] = ldap_error( $ldap );
				}

				// We failed either an anonymous bind or a bind with a service account,
				// so try to bind with the logging in user's credentials before failing.
				// Note: multiple search bases can be provided, so iterate through them
				// trying to bind as the user logging in.
				foreach ( $search_bases as $search_base ) {
					$bind_user_dn = $auth_settings['ldap_uid'] . '=' . $username . ',' . $search_base;
					$result = @ldap_bind( $ldap, $bind_user_dn, stripslashes( $password ) ); // phpcs:ignore
					if ( $result ) {
						if ( is_array( $debug ) ) {
							/* TRANSLATORS: LDAP User DN */
							$debug[] = sprintf( __( 'Successful bind using LDAP user DN %s instead of directory user.', 'authorizer' ), $bind_user_dn );
						}

						break;
					}
				}

				if ( ! $result ) {
					if ( is_array( $debug ) ) {
						/* TRANSLATORS: LDAP User */
						$debug[] = sprintf( __( 'Failed: password incorrect for LDAP user %s.', 'authorizer' ), $username );
						$debug[] = ldap_error( $ldap );
					}

					// Can't connect to LDAP, so fall back to WordPress authentication.
					continue;
				}
			}

			// If we've reached this, we have a valid ldap connection and bind.
			$ldap_valid = true;
			if ( is_array( $debug ) ) {
				/* TRANSLATORS: LDAP Host */
				$debug[] = sprintf( __( 'Connected to LDAP host %s.', 'authorizer' ), $ldap_host );
			}
			break;
		}

		// Move to next authentication method if we don't have a valid LDAP connection.
		if ( ! $ldap_valid ) {
			if ( is_array( $debug ) ) {
				$debug[] = __( 'Failed: unable to connect to any LDAP host.', 'authorizer' );
			}
			return null;
		}

		// Look up the bind DN (and first/last name) of the user trying to
		// log in by performing an LDAP search for the login username in
		// the field specified in the LDAP settings. This setup is common.
		$ldap_attributes_to_retrieve = array( 'dn' );
		if ( array_key_exists( 'ldap_attr_first_name', $auth_settings ) && strlen( $auth_settings['ldap_attr_first_name'] ) > 0 ) {
			array_push( $ldap_attributes_to_retrieve, $auth_settings['ldap_attr_first_name'] );
		}
		if ( array_key_exists( 'ldap_attr_last_name', $auth_settings ) && strlen( $auth_settings['ldap_attr_last_name'] ) > 0 ) {
			array_push( $ldap_attributes_to_retrieve, $auth_settings['ldap_attr_last_name'] );
		}
		if ( array_key_exists( 'ldap_attr_email', $auth_settings ) && strlen( $auth_settings['ldap_attr_email'] ) > 0 && substr( $auth_settings['ldap_attr_email'], 0, 1 ) !== '@' ) {
			array_push( $ldap_attributes_to_retrieve, strtolower( $auth_settings['ldap_attr_email'] ) );
		}

	
		// Create default LDAP search filter. If LDAP email attribute is provided,
		// use (|(uid=$username)(mail=$username)) instead (so logins with either a
		// username or an email address will work). Otherwise use (uid=$username).
		if ( array_key_exists( 'ldap_attr_email', $auth_settings ) && strlen( $auth_settings['ldap_attr_email'] ) > 0 && substr( $auth_settings['ldap_attr_email'], 0, 1 ) !== '@' ) {
			$search_filter =
				'(|' .
					'(' . $auth_settings['ldap_uid'] . '=' . $username . ')' .
					'(' . $auth_settings['ldap_attr_email'] . '=' . $username . ')' .
				')';
		} else {
			$search_filter = '(' . $auth_settings['ldap_uid'] . '=' . $username . ')';
		}

		// Merge LDAP search filter from plugin settings if it exists.
		$ldap_search_filter = trim( $auth_settings['ldap_search_filter'] );
		if ( ! empty( $ldap_search_filter ) ) {
			$search_filter = '(&' . $search_filter . $ldap_search_filter . ')';
		}

		

		// Multiple search bases can be provided, so iterate through them until a match is found.
		foreach ( $search_bases as $search_base ) {
			$ldap_search  = @ldap_search( // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				$ldap,
				$search_base,
				$search_filter,
				$ldap_attributes_to_retrieve
			);
			$ldap_entries = empty( $ldap_search ) ? array( 'count' => 0 ) : ldap_get_entries( $ldap, $ldap_search );
			if ( $ldap_entries['count'] > 0 ) {
				if ( is_array( $debug ) ) {
					/* TRANSLATORS: 1: LDAP user 2: LDAP search base */
					$debug[] = sprintf( __( 'Found user %1$s in search base: %2$s', 'authorizer' ), $username, $search_base );
				}
				break;
			} elseif ( is_array( $debug ) ) {
				/* TRANSLATORS: 1: LDAP user 2: LDAP search base */
				$debug[] = sprintf( __( 'Failed to find user %1$s in %2$s. Trying next search base.', 'authorizer' ), $username, $search_base );
			}
		}

		// If we didn't find any users in ldap, fall back to WordPress authentication.
		if ( $ldap_entries['count'] < 1 ) {
			if ( is_array( $debug ) ) {
				/* TRANSLATORS: LDAP User */
				$debug[] = sprintf( __( 'Failed: no LDAP user %s found.', 'authorizer' ), $username );
			}
			return null;
		}

		// Get the bind dn and first/last names; if there are multiple results returned, just get the last one.
		for ( $i = 0; $i < $ldap_entries['count']; $i++ ) {
			$ldap_user_dn = $ldap_entries[ $i ]['dn'];

			// Get user first name and last name.
			$ldap_attr_first_name = array_key_exists( 'ldap_attr_first_name', $auth_settings ) ? strtolower( $auth_settings['ldap_attr_first_name'] ) : '';
			if ( strlen( $ldap_attr_first_name ) > 0 && array_key_exists( $ldap_attr_first_name, $ldap_entries[ $i ] ) && $ldap_entries[ $i ][ $ldap_attr_first_name ]['count'] > 0 && strlen( $ldap_entries[ $i ][ $ldap_attr_first_name ][0] ) > 0 ) {
				$first_name = $ldap_entries[ $i ][ $ldap_attr_first_name ][0];
			}
			$ldap_attr_last_name = array_key_exists( 'ldap_attr_last_name', $auth_settings ) ? strtolower( $auth_settings['ldap_attr_last_name'] ) : '';
			if ( strlen( $ldap_attr_last_name ) > 0 && array_key_exists( $ldap_attr_last_name, $ldap_entries[ $i ] ) && $ldap_entries[ $i ][ $ldap_attr_last_name ]['count'] > 0 && strlen( $ldap_entries[ $i ][ $ldap_attr_last_name ][0] ) > 0 ) {
				$last_name = $ldap_entries[ $i ][ $ldap_attr_last_name ][0];
			}
			// Get user email if it is specified in another field.
			$ldap_attr_email = array_key_exists( 'ldap_attr_email', $auth_settings ) ? strtolower( $auth_settings['ldap_attr_email'] ) : '';
			if ( strlen( $ldap_attr_email ) > 0 ) {
				// If the email attribute starts with an at symbol (@), assume that the
				// email domain is manually entered there (instead of a reference to an
				// LDAP attribute), and combine that with the username to create the email.
				// Otherwise, look up the LDAP attribute for email.
				if ( substr( $ldap_attr_email, 0, 1 ) === '@' ) {
					$email = strtolower( $username . $ldap_attr_email );
				} elseif ( array_key_exists( $ldap_attr_email, $ldap_entries[ $i ] ) && $ldap_entries[ $i ][ $ldap_attr_email ]['count'] > 0 && strlen( $ldap_entries[ $i ][ $ldap_attr_email ][0] ) > 0 ) {
					$email = strtolower( $ldap_entries[ $i ][ $ldap_attr_email ][0] );
				}
			}
		}

		$result = @ldap_bind( $ldap, $ldap_user_dn, stripslashes( $password ) ); // phpcs:ignore
		if ( ! $result ) {
			if ( is_array( $debug ) ) {
				/* TRANSLATORS: LDAP User */
				$debug[] = sprintf( __( 'Failed: password incorrect for LDAP user %s.', 'authorizer' ), $username );
			}
			// We have a real ldap user, but an invalid password. Pass
			// through to wp authentication after failing LDAP (since
			// this could be a local account that happens to be the
			// same name as an LDAP user).
			return null;
		}

		// User successfully authenticated against LDAP, so set the relevant variables.
		$externally_authenticated_email = strtolower( $username . '@' . $domain );

		// If an LDAP attribute has been specified as containing the email address, use that instead.
		if ( strlen( $email ) > 0 ) {
			$externally_authenticated_email = strtolower( $email );
		}

		if ( is_array( $debug ) ) {
			/* TRANSLATORS: 1: Current time 2: LDAP User 3: LDAP user email */
			$debug[] = sprintf( __( '[%1$s] Successfully authenticated user %2$s (%3$s) via LDAP.', 'authorizer' ), wp_date( get_option( 'time_format' ) ), $username, $externally_authenticated_email );
		}

		return array(
			'email'            => $externally_authenticated_email,
			'username'         => $username,
			'first_name'       => $first_name,
			'last_name'        => $last_name,
			'authenticated_by' => 'ldap',
			'ldap_attributes'  => $ldap_entries,
		);
	}

    ?>