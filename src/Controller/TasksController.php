<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\ORM\Query;

/**
 * Tasks Controller
 *
 * @property \App\Model\Table\TasksTable $Tasks
 */
class TasksController extends AppController {

	/**
	 * Index method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function index() {
		$this->Authorization->authorize($this);
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		if ($this->Authentication->getIdentity()->isManager() && $this->request->is('csv')) {
			$tasks = $this->Tasks->Categories->find()
				->contain([
					'Tasks' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['Tasks.name']);
						},
						'People' => [Configure::read('Security.authModel')],
						'TaskSlots' => [
							'queryBuilder' => function (Query $q) {
								return $q->order(['TaskSlots.task_date', 'TaskSlots.task_start']);
							},
							'People',
							'ApprovedBy',
						],
					],
				])
				->where(['Categories.affiliate_id IN' => $affiliates])
				->order(['Categories.name'])
				->toArray();
			$this->response->download('Tasks.csv');
		} else {
			$conditions = ['Categories.affiliate_id IN' => $affiliates];
			if (!$this->Authentication->getIdentity()->isManager()) {
				$conditions['Tasks.allow_signup'] = true;
			}
			$tasks = $this->Tasks->find()
				->contain([
					'Categories',
					'People',
				])
				->where($conditions)
				->order(['Categories.name', 'Tasks.name'])
				->toArray();
		}

		$this->set(compact('tasks'));
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function view() {
		$id = $this->request->getQuery('task');
		try {
			$task = $this->Tasks->get($id, [
				'contain' => [
					'Categories',
					'People',
					'TaskSlots' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['TaskSlots.task_date', 'TaskSlots.task_start', 'TaskSlots.task_end', 'TaskSlots.id']);
						},
						'People',
						'ApprovedBy',
					],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid task.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid task.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($task);
		$affiliates = $this->Authentication->applicableAffiliates(true);
		if ($this->Authorization->can($task, 'assign')) {
			$people = $this->Tasks->People->find()
				->matching('Groups', function (Query $q) {
					return $q->where(['Groups.id IN' => [GROUP_VOLUNTEER, GROUP_OFFICIAL, GROUP_MANAGER, GROUP_ADMIN]]);
				})
				->matching('Affiliates', function (Query $q) use ($affiliates) {
					return $q->where(['Affiliates.id IN' => array_keys($affiliates)]);
				})
				->order(['People.first_name', 'People.last_name'])
				->combine('id', 'full_name')
				->toArray();
		}
		$this->set(compact('task', 'affiliates', 'people'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$task = $this->Tasks->newEntity();
		$this->Authorization->authorize($this);

		if ($this->request->is('post')) {
			$task = $this->Tasks->patchEntity($task, $this->request->getData());
			if ($this->Tasks->save($task)) {
				$this->Flash->success(__('The task has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The task could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($this->Tasks->Categories->affiliate($task->category_id));
			}
		}

		$affiliates = $this->Authentication->applicableAffiliates(true);
		$categories = $this->Tasks->Categories->find('list', ['order' => 'Categories.name']);
		$people = $this->Tasks->People->find()
			->matching('Groups', function (Query $q) {
				return $q->where(['Groups.id IN' => [GROUP_VOLUNTEER, GROUP_OFFICIAL, GROUP_MANAGER, GROUP_ADMIN]]);
			})
			->matching('Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => array_keys($affiliates)]);
			})
			->order(['People.first_name', 'People.last_name'])
			->combine('id', 'full_name')
			->toArray();
		$this->set(compact('task', 'affiliates', 'categories', 'people'));
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->request->getQuery('task');
		try {
			$task = $this->Tasks->get($id, [
				'contain' => ['Categories']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid task.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid task.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($task);
		$this->Configuration->loadAffiliate($task->category->affiliate_id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$task = $this->Tasks->patchEntity($task, $this->request->getData());
			if ($this->Tasks->save($task)) {
				$this->Flash->success(__('The task has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The task could not be saved. Please correct the errors below and try again.'));
			}
		}
		$affiliates = $this->Authentication->applicableAffiliates(true);
		$categories = $this->Tasks->Categories->find('list', ['order' => 'Categories.name']);
		$people = $this->Tasks->People->find()
			->matching('Groups', function (Query $q) {
				return $q->where(['Groups.id IN' => [GROUP_VOLUNTEER, GROUP_OFFICIAL, GROUP_MANAGER, GROUP_ADMIN]]);
			})
			->matching('Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => array_keys($affiliates)]);
			})
			->order(['People.first_name', 'People.last_name'])
			->combine('id', 'full_name')
			->toArray();
		$this->set(compact('task', 'affiliates', 'categories', 'people'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 */
	public function delete() {
		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->getQuery('task');
		try {
			$task = $this->Tasks->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid task.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid task.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($task);

		$dependencies = $this->Tasks->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this task, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Tasks->delete($task)) {
			$this->Flash->success(__('The task has been deleted.'));
		} else if ($task->errors('delete')) {
			$this->Flash->warning(current($task->errors('delete')));
		} else {
			$this->Flash->warning(__('The task could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
