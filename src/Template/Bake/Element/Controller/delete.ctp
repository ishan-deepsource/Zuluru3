<%
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
%>

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 */
	public function delete() {
		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->query('<%= $singularName %>');
		$dependencies = $this-><%= $currentModelName; %>->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this <%= strtolower($singularHumanName) %>, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		try {
			$<%= $singularName %> = $this-><%= $currentModelName; %>->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid <%= strtolower($singularHumanName) %>.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid <%= strtolower($singularHumanName) %>.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this-><%= $currentModelName; %>->delete($<%= $singularName %>)) {
			$this->Flash->success(__('The <%= strtolower($singularHumanName) %> has been deleted.'));
		} else if (<%= $singularName %>->errors('delete')) {
			$this->Flash->warning(current(<%= $singularName %>->errors('delete')));
		} else {
			$this->Flash->warning(__('The <%= strtolower($singularHumanName) %> could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}
