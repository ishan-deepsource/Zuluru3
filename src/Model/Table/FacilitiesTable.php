<?php
namespace App\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Model\Rule\InConfigRule;
use App\Model\Rule\OrRule;

/**
 * Facilities Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Regions
 * @property \Cake\ORM\Association\HasMany $Fields
 * @property \Cake\ORM\Association\BelongsToMany $Teams
 */
class FacilitiesTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('facilities');
		$this->displayField('name');
		$this->primaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Formatter', [
			'fields' => [
				'name' => 'proper_case_format',
				// We can't just use 'strtoupper' here, as it will barf when it gets passed 2 arguments
				'code' => function ($value, $country) { return strtoupper($value); },
				'location_street' => 'proper_case_format',
				'location_city' => 'proper_case_format',
			],
		]);

		$this->belongsTo('Regions', [
			'foreignKey' => 'region_id',
		]);

		$this->hasMany('Fields', [
			'foreignKey' => 'facility_id',
		]);

		$this->belongsToMany('Teams', [
			'foreignKey' => 'facility_id',
			'targetForeignKey' => 'team_id',
			'joinTable' => 'teams_facilities',
		]);
	}

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator) {
		$validator
			->numeric('id')
			->allowEmpty('id', 'create')

			->boolean('is_open')
			->requirePresence('is_open', 'create')
			->notEmpty('is_open')

			->notEmpty('name', __('The name cannot be blank.'))

			->notEmpty('code', __('The code cannot be blank.'))

			->notEmpty('location_street', __('You must supply a valid street address.'))

			->notEmpty('location_city', __('You must supply a city.'))

			->notEmpty('location_province', __('Select a province/state from the list.'))

			->allowEmpty('parking')

			->allowEmpty('driving_directions')

			->allowEmpty('parking_details')

			->allowEmpty('transit_directions')

			->allowEmpty('biking_directions')

			->allowEmpty('washrooms')

			->allowEmpty('public_instructions')

			->allowEmpty('site_instructions')

			->allowEmpty('sponsor')

			->allowEmpty('entrances')

			->allowEmpty('sport')

			;

		return $validator;
	}

	/**
	 * Returns a rules checker object that will be used for validating
	 * application integrity.
	 *
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules(RulesChecker $rules) {
		$rules->add($rules->existsIn(['region_id'], 'Regions', __('You must select a valid region.')));

		$rules->add(new InConfigRule('provinces'), 'validProvince', [
			'errorField' => 'location_province',
			'message' => __('Select a province/state from the list.'),
		]);

		// We are happy if the sport is either empty or a valid selection
		$rules->add(new OrRule([
			function (EntityInterface $entity, Array $options) { return empty($entity->sport); },
			new InConfigRule('options.sport'),
		]), 'validSport', [
			'errorField' => 'sport',
			'message' => __('Select a sport from the list.'),
		]);

		return $rules;
	}

	public function findOpen(Query $query, Array $options) {
		$query->where(['Facilities.is_open' => true]);
		if (!empty($options['affiliates'])) {
			$query->matching('Regions', function (Query $q) use ($options) {
				return $q->andWhere(['Regions.affiliate_id IN' => $options['affiliates']]);
			});
		}

		return $query;
	}

	public function affiliate($id) {
		try {
			return $this->Regions->affiliate($this->field('region_id', ['id' => $id]));
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}
}
