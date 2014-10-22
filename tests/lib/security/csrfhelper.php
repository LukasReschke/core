<?php
/**
 * Copyright (c) 2014 Lukas Reschke <lukas@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

use OCP\ISession;
use OC\Security\SecureRandom;
use OC\Security\CSRFHelper;

/**
 * Class CSRFHelper
 */
class CSRFHelperTest extends \PHPUnit_Framework_TestCase {

	/** @var CSRFHelper */
	protected $csrfHelper;
	/** @var ISession */
	protected $session;
	/** @var SecureRandom */
	protected $secureRandom;

	protected function setUp() {
		$this->session = $this->getMockBuilder('\OCP\ISession')
			->disableOriginalConstructor()->getMock();
		$this->secureRandom = $this->getMockBuilder('\OC\Security\SecureRandom')
			->disableOriginalConstructor()->getMock();

		$this->csrfHelper = new CSRFHelper($this->session, $this->secureRandom);
	}

	function testRegister() {
		$randomToken = $this->secureRandom;
		$this->secureRandom
			->expects($this->once())
			->method('generate')
			->with('30')
			->will($this->returnValue('ThisIsMaybeANotSoSecretToken!'));
		$this->secureRandom
			->expects($this->once())
			->method('getMediumStrengthGenerator')
			->will($this->returnValue($randomToken));
		$this->session
			->expects($this->once())
			->method('set')
			->with('requesttoken', 'ThisIsMaybeANotSoSecretToken!');

		// Register it two times to ensure that the cached variable is used
		// within the second request
		$this->csrfHelper->register();
		$this->csrfHelper->register();
	}

	function testGetToken() {
		// Empty token should reply NULL
		$this->assertNull($this->csrfHelper->getToken());

		// Non-Empty Token
		$this->session
			->expects($this->once())
			->method('get')
			->with('requesttoken')
			->will($this->returnValue('ThisIsMaybeANotSoSecretToken!'));
		$this->assertSame('ThisIsMaybeANotSoSecretToken!', $this->csrfHelper->getToken());
	}

	function testVerifyWithoutCache() {
		$randomToken = $this->secureRandom;
		$this->secureRandom
			->expects($this->once())
			->method('generate')
			->with('30')
			->will($this->returnValue('CorrectToken'));
		$this->secureRandom
			->expects($this->once())
			->method('getMediumStrengthGenerator')
			->will($this->returnValue($randomToken));
		$this->session
			->expects($this->once())
			->method('set')
			->with('requesttoken', 'CorrectToken');
		$this->csrfHelper->register();
		$this->session
			->expects($this->exactly(0))
			->method('get')
			->with('requesttoken')
			->will($this->returnValue('ThisIsMaybeANotSoSecretToken!'));

		$this->assertFalse($this->csrfHelper->verify(null));
		$this->assertFalse($this->csrfHelper->verify('WrongToken'));
		$this->assertTrue($this->csrfHelper->verify('CorrectToken'));
	}

	function testVerifyWithCache() {
		$this->session
			->expects($this->once())
			->method('get')
			->with('requesttoken')
			->will($this->returnValue('CorrectToken'));

		$this->assertFalse($this->csrfHelper->verify(null));
		$this->assertFalse($this->csrfHelper->verify('WrongToken'));
		$this->assertTrue($this->csrfHelper->verify('CorrectToken'));
	}
}
