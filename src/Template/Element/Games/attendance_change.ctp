<?php
/**
 * @type \App\Model\Entity\Team $team
 * @type \Cake\I18n\FrozenTime $game_time
 * @type string $status
 * @type string $role
 * @type string $comment
 * @type int $person_id
 * @type bool $is_captain
 */
use Cake\Core\Configure;
use Cake\Utility\Text;
use App\Model\Table\GamesTable;

if ($team->track_attendance || (isset($force) && $force)) {
	$long = Configure::read("attendance.$status");
	$icon = Text::slug(strtolower($long), '_');

	if (isset($dedicated) && $dedicated) {
		$icon .= '_dedicated';
	} else {
		$dedicated = false;
	}

	$title = __('Current attendance: {0}', __($long));
	if (!empty($comment)) {
		if ($dedicated) {
			$icon .= '_comment';
		}
		$title .= " ($comment)";
	}

	$short = $this->Html->iconImg("attendance_{$icon}_24.png", [
		'title' => $title,
		'alt' => Configure::read("attendance_alt.$status"),
	]);

	if (!isset($future_only)) {
		$future_only = false;
	}

	$recent = $game_time->wasWithinLast('20 week');
	$future = $game_time->isFuture();
	$is_me = (!isset($person_id) || $person_id == Configure::read('Perm.my_id'));
	$is_relative = (!$is_me && in_array($person_id, $this->UserCache->read('RelativeIDs')));
	if (($future || (!$future_only && $recent)) && ($is_me || $is_relative || $is_captain) && $team->track_attendance) {
		$url = ['controller' => 'Games', 'action' => 'attendance_change', 'team' => $team->id];
		if (isset($game_id) && $game_id) {
			$url['game'] = $game_id;
		} else {
			$url['date'] = $game_time->format('Y-m-d');
		}

		if (!$is_me) {
			$url['person'] = $person_id;
		}

		$valid_options = array_keys(GamesTable::attendanceOptions($role, $status, !$future, in_array($team->id, $this->UserCache->read('OwnedTeamIDs'))));
		if ($future) {
			$valid_options[] = 'comment';
		}

		echo $this->Jquery->inPlaceWidget($short, [
			'type' => 'attendance',
			'url' => $url,
			'valid-options' => $valid_options,
			'comment-value' => $comment,
		], [
			'class' => "attendance_status_$status " .  strtolower(Configure::read("attendance.$status")),
		], false, compact('dedicated'));
	} else if (!$future_only) {
		echo $this->Html->tag('span', $short, ['class' => "attendance_status_$status " .  strtolower(Configure::read("attendance.$status"))]);
	}
}
