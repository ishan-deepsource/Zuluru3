<?php
use Cake\Core\Configure;

/**
 * @type \App\Model\Entity\Person $person
 * @type \App\Model\Entity\Team $team
 * @type string $captain
 */
?>

<?= __('Dear {0},', $person->first_name) ?>


<?= __('{0} has removed the invitation to join the roster of the {1} team {2}.',
	$captain,
	Configure::read('organization.name'),
	$team->name
) ?>


<?= $this->element('Email/text/footer');
