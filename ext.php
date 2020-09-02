<?php
/**
 *
 * Top Poster Of The Month. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2005, 2019, 3Di <https://www.phpbbstudio.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace threedi\tpotm;

/**
* Top Poster Of The Month Extension base
*/

class ext extends \phpbb\extension\base
{
	/* Define constants */
	const NO_CACHE	= 0;
	const ONE_DAY	= 86400;
	const ONE_WEEK	= 604800;
	const TWO_WEEKS	= 1209600;
	const ONE_MONTH	= 2419200;

	/**
	 * Check whether the extension can be enabled.
	 * Provides meaningful(s) error message(s) and the back-link on failure.
	 * CLI and 3.1/3.2 compatible
	 *
	 * @return bool
	 */
	public function is_enableable()
	{
		if ((phpbb_version_compare(PHPBB_VERSION, '3.1.11', '>=') && phpbb_version_compare(PHPBB_VERSION, '4.0.0@dev', '<')))
		{
			return true;
		}
	}
}
