<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'reddingtestproject' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '0%&_]7:{*MH}v#98Tl&j|_#|*c9pyX9ht3Ii_UW_Lw$W@zP+93/a 7#HA#,Vf@Lr' );
define( 'SECURE_AUTH_KEY',  '3BT6{7vC/W5fx.MmM]$eR=!FMM6.8eFQ_=7&F+9ryG0BKk19t4>5x_m r9l@~}E-' );
define( 'LOGGED_IN_KEY',    '-QN^(-.P)Y+Wc=*5C6/F_Lfy*L3{yW-F/guL[:oT5K6-N+In[)hy]8/cd}lwdi!4' );
define( 'NONCE_KEY',        'hb]*u5seTf;U?gwy&34Zq64E<IIqIpr-Y/mH1fu$(Auug-Tevxe -F:1&sxc@TEc' );
define( 'AUTH_SALT',        '0O`7E{F 3l+WI&=e`DL;Y@bGX#k*fI%_jouCL1$mPxN{{irecW.?*qh=8~<nl,3!' );
define( 'SECURE_AUTH_SALT', '**k18<.@/ @BUgZ.ghG.cjCz)r_+s3Vi{1^KR%;_#~N3EB#<rR)it-4gmy@C3%SW' );
define( 'LOGGED_IN_SALT',   '/~7N8Z[Sm g?vMf:v$;O46TbqjA~x[)Z7~@oMec;4PKHNj`I@ /RUx8xCgvY}M$N' );
define( 'NONCE_SALT',       '*!ZR($h1JjBp5_:.6(jKXMYSkDh?PM};Y.jtNvf)@0gHe91DR0/E:c_ Dm:<_W2!' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
