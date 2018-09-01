<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DivisionsPeopleFixture
 *
 */
class DivisionsPeopleFixture extends TestFixture {

	/**
	 * Table name
	 *
	 * @var string
	 */
	public $table = 'divisions_people';

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'divisions_people'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'division_id' => DIVISION_ID_MONDAY_LADDER_PAST,
				'person_id' => PERSON_ID_COORDINATOR,
				'position' => 'coordinator'
			],
			[
				'division_id' => DIVISION_ID_MONDAY_LADDER,
				'person_id' => PERSON_ID_COORDINATOR,
				'position' => 'coordinator'
			],
			[
				'division_id' => DIVISION_ID_THURSDAY_ROUND_ROBIN,
				'person_id' => PERSON_ID_COORDINATOR,
				'position' => 'coordinator'
			],
		];

		parent::init();
	}

}
