<?php
/**
 * @author Lukas Reschke
 * @copyright 2014 Lukas Reschke lukas@owncloud.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Files_Sharing\Middleware;

use OC\AppConfig;
use OCP\AppFramework\IApi;
use \OCP\AppFramework\Middleware;

/**
 * Checks whether the "sharing check" is enabled
 *
 * @package OCA\Files_Sharing\Middleware
 */
class SharingCheckMiddleware extends Middleware {

	protected $appName;
	protected $appConfig;
	protected $api;

	/***
	 * @param string $appName
	 * @param AppConfig $appConfig
	 * @param IApi $api
	 */
	public function __construct($appName,
								AppConfig $appConfig,
								IApi $api) {
		$this->appName = $appName;
		$this->appConfig = $appConfig;
		$this->api = $api;
	}

	/**
	 * Check if sharing is enabled before the controllers is executed
	 * FIXME: Show 404 or redirect instead of stopping the application's execution
	 */
	public function beforeController() {
		if(!$this->isSharingEnabled()) {
			exit();
		}
	}

	/**
	 * Check whether sharing is enabled
	 * @return bool
	 */
	private function isSharingEnabled() {
		// FIXME: This check is currently done here since the route is globally defined and not inside the files_sharing app
		// Check whether the sharing application is enabled
		if(!$this->api->isAppEnabled($this->appName)) {
			return false;
		}

		// Check whether public sharing is enabled
		if($this->appConfig->getValue('core', 'shareapi_allow_links', 'yes') !== 'yes') {
			return false;
		}

		return true;
	}

}
