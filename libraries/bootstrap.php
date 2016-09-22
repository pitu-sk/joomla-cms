<?php
/**
 * Bootstrap file for the Joomla! CMS [with legacy libraries].
 * Including this file into your application will make Joomla libraries available for use.
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Set the platform root path as a constant if necessary.
defined('JPATH_PLATFORM') or define('JPATH_PLATFORM', __DIR__);

// Detect the native operating system type.
$os = strtoupper(substr(PHP_OS, 0, 3));

defined('IS_WIN') or define('IS_WIN', ($os === 'WIN') ? true : false);
defined('IS_UNIX') or define('IS_UNIX', (($os !== 'MAC') && ($os !== 'WIN')) ? true : false);

/**
 * @deprecated 13.3  Use IS_UNIX instead
 */
defined('IS_MAC') or define('IS_MAC', (IS_UNIX === true && ($os === 'DAR' || $os === 'MAC')) ? true : false);

// Import the library loader if necessary.
if (!class_exists('JLoader'))
{
	require_once JPATH_PLATFORM . '/loader.php';

	// If JLoader still does not exist panic.
	if (!class_exists('JLoader'))
	{
		throw new RuntimeException('Joomla Platform not loaded.');
	}
}

// Setup the autoloaders.
JLoader::setup();

// Register the library base path for the legacy libraries.
JLoader::registerPrefix('J', JPATH_PLATFORM . '/legacy');

// Register the library base path for CMS libraries.
JLoader::registerPrefix('J', JPATH_PLATFORM . '/cms', false, true);

// Create the Composer autoloader
/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require JPATH_LIBRARIES . '/vendor/autoload.php';
$loader->unregister();

// Decorate Composer autoloader
spl_autoload_register([new JClassLoader($loader), 'loadClass'], true, true);

// Register the class aliases for Framework classes that have replaced their Platform equivilents
require_once JPATH_LIBRARIES . '/classmap.php';

// Ensure FOF autoloader included - needed for things like content versioning where we need to get an FOFTable Instance
if (!class_exists('FOFAutoloaderFof'))
{
	include_once JPATH_LIBRARIES . '/fof/include.php';
}

// Register the global exception handler.
set_exception_handler(['JErrorPage', 'render']);

// Define the Joomla version if not already defined.
defined('JVERSION') or define('JVERSION', (new JVersion)->getShortVersion());

// Set up the message queue logger for web requests
if (array_key_exists('REQUEST_METHOD', $_SERVER))
{
	JLog::addLogger(['logger' => 'messagequeue'], JLog::ALL, ['jerror']);
}

// Register classes that don't follow the autoloader convention.
JLoader::register('JText', JPATH_PLATFORM . '/joomla/language/text.php');
JLoader::register('JRoute', JPATH_PLATFORM . '/joomla/application/route.php');
JLoader::register('JArrayHelper', JPATH_PLATFORM . '/joomla/utilities/arrayhelper.php');

// Register the Crypto lib
JLoader::register('Crypto', JPATH_PLATFORM . '/php-encryption/Crypto.php');

// Check if the JsonSerializable interface exists already
if (!interface_exists('JsonSerializable'))
{
	JLoader::register('JsonSerializable', JPATH_PLATFORM . '/vendor/joomla/compat/src/JsonSerializable.php');
}

// Add deprecated constants
// @deprecated 4.0
define('JPATH_ISWIN', IS_WIN);
define('JPATH_ISMAC', IS_MAC);

// Register the PasswordHash library.
JLoader::register('PasswordHash', JPATH_PLATFORM . '/phpass/PasswordHash.php');

// Register classes where the names have been changed to fit the autoloader rules.
// @deprecated  4.0
JLoader::register('JSimpleCrypt', JPATH_PLATFORM . '/legacy/simplecrypt/simplecrypt.php');
JLoader::register('JTree', JPATH_PLATFORM . '/legacy/base/tree.php');
JLoader::register('JNode', JPATH_PLATFORM . '/legacy/base/node.php');

// Create class name aliases for the legacy application classes.
// @deprecated  4.0
JLoader::registerAlias('JAdministrator', 'JApplicationAdministrator');
JLoader::registerAlias('JSite', 'JApplicationSite');
