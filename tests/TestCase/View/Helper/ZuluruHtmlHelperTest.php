<?php
namespace App\Test\TestCase\View\Helper;

use Cake\TestSuite\TestCase;
use Cake\View\View;
use App\View\Helper\ZuluruHtmlHelper;

/**
 * App\Model\Helper\ZuluruHtmlHelper Test Case
 */
class ZuluruHtmlHelperTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \App\View\Helper\ZuluruHtmlHelper
	 */
	public $ZuluruHtmlHelper;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$view = new View();
		$this->ZuluruHtmlHelper = new ZuluruHtmlHelper($view);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->ZuluruHtmlHelper);

		parent::tearDown();
	}

	/**
	 * Test link method
	 *
	 * @return void
	 */
	public function testLink() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scriptBlock method
	 *
	 * @return void
	 */
	public function testScriptBlock() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test imageLink method
	 *
	 * @return void
	 */
	public function testImageLink() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test iconImg method
	 *
	 * @return void
	 */
	public function testIconImg() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test iconLink method
	 *
	 * @return void
	 */
	public function testIconLink() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test help method
	 *
	 * @return void
	 */
	public function testHelp() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test formatMessage method
	 *
	 * @return void
	 */
	public function testFormatMessage() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
