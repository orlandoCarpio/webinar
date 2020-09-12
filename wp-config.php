<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'webinar' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '~BOH,ywK&i{js-;^J0wbsm)qmOn<:i1j!`p8_er/.WW >!W0@k7PKHD[^4]}3Ck-' );
define( 'SECURE_AUTH_KEY',  '>#zgk:J9DYf,j@U#,RU`Z+q/%8a5FoQ_Ll8t]HtA_1Z80]Inh>zg{f6%RA([F#R=' );
define( 'LOGGED_IN_KEY',    ']mih<e#oua!o9Ak9vV|}Hv}s=^<Wo?3nDxxMe%$HI#ehN Is#X:}B/13QcaCW^N(' );
define( 'NONCE_KEY',        ';}[.x.4gV+jji~av4P<HLNp*~~R6bx_y^hi7PuhL&Gk!(A^7,:]Lh:aY_L>&HsJn' );
define( 'AUTH_SALT',        'SWy:&MZh:KiX7@#d;V(9Sqn:A[wJY4t *d`XUlwNk5t8eyL~;RaderTb=A-h+}D~' );
define( 'SECURE_AUTH_SALT', '>7:pY7SuY,Y!RO;[UXo+)H6QN[l%5-I#x/MVL)GNf32u8AF~&%u269I:OxJ}hxAI' );
define( 'LOGGED_IN_SALT',   '{n=2*ME)dN0&ZP11H=)qW{CL-?gRq=bz5Cd:]w_FS$osJ{Pf]X0p6,-c(_P=-8J~' );
define( 'NONCE_SALT',       'pcTW0fEku.]9m{ {D~jAGcR^DoAbsw0*WrFBe_JaA]i/?/H7D=iF`-an(31Zmi|=' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
