<?php
namespace App\Test\Fixture;

use Cake\I18n\FrozenDate;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * ScoreDetailsFixture
 *
 */
class ScoreDetailsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'score_details'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'team_id' => TEAM_ID_RED,
				'created_team_id' => TEAM_ID_BLUE,
				'score_from' => 1,
				'play' => 'Lorem ipsum dolor sit amet',
				'points' => 1,
				'created' => FrozenDate::now(),
			],
		];

		parent::init();
	}

}
