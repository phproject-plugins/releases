<?php
/**
 * @package  Releases
 * @author   Alan Hardman <alan@phpizza.com>
 * @version  0.0.1
 */

namespace Plugin\Releases;

class Base extends \Plugin {

	/**
	 * Initialize the plugin
	 * @todo load configuration and initialize socket connection
	 */
	public function _load() {
		$f3 = \Base::instance();
		$this->_addNav("releases", "Releases", "/$\\/releases/i", "browse");
        $f3->route("GET /releases", "Plugin\Releases\Controller->index");
	}

}
