<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * LeaguesStatTypesFixture
 *
 */
class LeaguesStatTypesFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'leagues_stat_types'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'league_id' => LEAGUE_ID_MONDAY,
				'stat_type_id' => 1,
			],
		];

		parent::init();
	}

}
