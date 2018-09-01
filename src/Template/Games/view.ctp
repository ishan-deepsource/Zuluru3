<?php
use Cake\Core\Configure;
use Cake\Utility\Inflector;
use App\Controller\AppController;

/**
 * @type $game \App\Model\Entity\Game
 * @type $my_team \App\Model\Entity\Team
 * @type $ratings_obj \App\Module\Ratings
 * @type $spirit_obj \App\Module\Spirit
 * @type $league_obj \App\Module\LeagueType
 * @type $is_coordinator boolean
 * @type $is_captain boolean
 */

$this->Html->addCrumb(__('Games'));
$this->Html->addCrumb(__('Game') . ' ' . $game->id);
$this->Html->addCrumb(__('View'));

$preliminary = ($game->home_team_id === null || ($game->division->schedule_type != 'competition' && $game->away_team_id === null));
$carbon_flip_options = [
	2 => __('{0} won', $game->home_team_id !== null ? $game->home_team->name : $game->home_dependency),
	0 => __('{0} won', $game->away_team_id !== null ? $game->away_team->name : $game->away_dependency),
	1 => __('tie'),
];
?>

<div class="games view">
	<h2><?= __('View Game') ?></h2>
	<dl class="dl-horizontal">
		<dt><?= __('League') . '/' . __('Division') ?></dt>
		<dd><?= $this->element('Divisions/block', ['division' => $game->division, 'field' => 'full_league_name']) ?></dd>
		<dt><?= $game->division->schedule_type == 'competition' ? __('Team') : __('Home Team') ?></dt>
		<dd><?php
			if ($game->home_team_id === null) {
				if ($game->has('home_dependency')) {
					echo $game->home_dependency;
				} else {
					echo __('Unassigned');
				}
			} else {
				echo $this->element('Teams/block', ['team' => $game->home_team]);
				if ($game->has('home_dependency')) {
					echo " ({$game->home_dependency})";
				}
				if ($game->division->schedule_type != 'tournament') {
					echo ' (' . __('currently rated') . ": {$game->home_team->rating})";
					if (!$preliminary && !$game->isFinalized() && $game->division->schedule_type != 'competition') {
						printf(' (%0.1f%% %s)', $ratings_obj->calculateExpectedWin($game->home_team->rating, $game->away_team->rating) * 100, __('chance to win'));
					}
				}
			}
		?></dd>
<?php
if ($game->division->schedule_type != 'competition'):
?>
		<dt><?= __('Away Team') ?></dt>
		<dd><?php
			if ($game->away_team_id === null) {
				if ($game->has('away_dependency')) {
					echo $game->away_dependency;
				} else {
					echo __('Unassigned');
				}
			} else {
				echo $this->element('Teams/block', ['team' => $game->away_team]);
				if ($game->has('away_dependency')) {
					echo " ({$game->away_dependency})";
				}
				if ($game->division->schedule_type != 'tournament') {
					echo ' (' . __('currently rated') . ": {$game->away_team->rating})";
					if (!$preliminary && !$game->isFinalized()) {
						printf(' (%0.1f%% %s)', $ratings_obj->calculateExpectedWin($game->away_team->rating, $game->home_team->rating) * 100, __('chance to win'));
					}
				}
			}
		?></dd>
<?php
endif;

if ($game->home_dependency_type != 'copy'):
?>
		<dt><?= __('Date and Time') ?></dt>
		<dd><?= $this->Time->dateTimeRange($game->game_slot) ?></dd>
		<dt><?= __('Location') ?></dt>
		<dd><?= $this->element('Fields/block', ['field' => $game->game_slot->field, 'display_field' => 'long_name']) ?></dd>
<?php
endif;
?>
		<dt><?= __('Game Status') ?></dt>
		<dd><?= __(Inflector::humanize ($game->status)) ?></dd>
<?php
if ($game->division->schedule_type == 'roundrobin' && $game->round):
?>
		<dt><?= __('Round') ?></dt>
		<dd><?= $game->round ?></dd>
<?php
endif;

if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator || $is_captain):
	if ($game->home_team) {
		$captains = $game->home_team->people;
	} else {
		$captains = [];
	}
	if ($game->away_team) {
		$captains = array_merge($captains, $game->away_team->people);
	}
	if (!empty($captains)):
?>
		<dt><?= __('Captain Emails') ?></dt>
		<dd><?= $this->Html->link(__('Email all coaches/captains'), 'mailto:' . implode(',', AppController::_extractEmails($captains, false, false, true))) ?></dd>
<?php
	endif;
endif;

if (!$preliminary && $game->division->schedule_type != 'roundrobin' && $ratings_obj->perGameRatings() && !$game->isFinalized() && Configure::read('Perm.is_logged_in')):
?>
		<dt><?= __('Ratings Table') ?></dt>
		<dd><?= $this->Html->link(__('Click to view'), ['action' => 'ratings_table', 'game' => $game->id]) ?></dd>
<?php
endif;
?>
	</dl>

<?php
$my_teams = $this->UserCache->read('AllTeamIDs');
if (in_array($game->home_team_id, $my_teams)) {
	$my_team = $game->home_team;
} else if (in_array($game->away_team_id, $my_teams)) {
	$my_team = $game->away_team;
}
$display_attendance = isset($my_team) && $my_team->track_attendance;
$can_annotate = Configure::read('feature.annotations') && isset($my_team);
$stats_link = $game->isFinalized() && $game->division->league->hasStats() && (Configure::read('Perm.is_logged_in') || Configure::read('feature.public'));

if ($display_attendance || $can_annotate || Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator || $stats_link):
?>
	<div class="actions columns">
		<ul class="nav nav-pills">
<?php
	if ($display_attendance) {
		echo $this->Html->tag('li', $this->Html->iconLink('attendance_24.png',
			['controller' => 'Games', 'action' => 'attendance', 'team' => $my_team->id, 'game' => $game->id],
			['alt' => __('Attendance'), 'title' => __('View Game Attendance Report')]));
	}
	if ($can_annotate) {
		echo $this->Html->tag('li', $this->Html->link(__('Add Note'), ['action' => 'note', 'game' => $game->id]));
	}
	if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator) {
		echo $this->Html->tag('li', $this->Html->iconLink('edit_24.png',
			['action' => 'edit', 'game' => $game->id],
			['alt' => __('Edit Game'), 'title' => __('Edit Game')]));
		echo $this->Html->tag('li', $this->Form->iconPostLink('delete_24.png',
			['action' => 'delete', 'game' => $game->id],
			['alt' => __('Delete Game'), 'title' => __('Delete Game')],
			['confirm' => __('Are you sure you want to delete this game?')]));
	}
	if ($stats_link) {
		echo $this->Html->tag('li', $this->Html->iconLink('stats_24.png',
			['action' => 'stats', 'game' => $game->id],
			['alt' => __('Game Stats'), 'title' => __('Game Stats')]));
	}
?>
		</ul>
	</div>
<?php
endif;

if (array_key_exists($game->home_team_id, $game->score_entries)) {
	$homeScoreEntry = $game->score_entries[$game->home_team_id];
}
if (array_key_exists($game->away_team_id, $game->score_entries)) {
	$awayScoreEntry = $game->score_entries[$game->away_team_id];
}

if (!empty($game->spirit_entries) || Configure::read('scoring.spirit_default')) {
	$homeSpiritEntry = $game->getSpiritEntry($game->home_team_id, $spirit_obj, false, true);
	$awaySpiritEntry = $game->getSpiritEntry($game->away_team_id, $spirit_obj, false, true);
} else {
	$homeSpiritEntry = $awaySpiritEntry = false;
}
$team_names = [];
if ($game->home_team_id) {
	$team_names[$game->home_team_id] = $game->home_team->name;
}
if ($game->away_team_id) {
	$team_names[$game->away_team_id] = $game->away_team->name;
}

if (Configure::read('scoring.gender_ratio')) {
	$gender_ratio_options = Configure::read("sports.{$game->division->league->sport}.gender_ratio.{$game->division->ratio_rule}");
} else {
	$gender_ratio_options = false;
}
?>

	<fieldset class="clear-float wide-labels">
		<legend><?= __('Scoring') ?></legend>
<?php
if ($game->isFinalized()):
?>
		<dl class="dl-horizontal">
<?php
	if (!in_array($game->status, Configure::read('unplayed_status'))):
?>
			<dt><?= $this->Text->truncate($game->home_team->name, 28) ?></dt>
			<dd><?php
				echo $game->home_score;
				if ($gender_ratio_options && isset($awayScoreEntry) && $awayScoreEntry->gender_ratio) {
					echo __(' ({0})', $gender_ratio_options[$awayScoreEntry->gender_ratio]);
				}
			?></dd>
<?php
		if ($game->division->schedule_type != 'competition'):
?>
			<dt><?= $this->Text->truncate($game->away_team->name, 28) ?></dt>
			<dd><?php
				echo $game->away_score;
				if ($gender_ratio_options && isset($homeScoreEntry) && $homeScoreEntry->gender_ratio) {
					echo __(' ({0})', $gender_ratio_options[$homeScoreEntry->gender_ratio]);
				}
			?></dd>
<?php
		endif;

		if ($game->division->league->hasCarbonFlip() && $game->status == 'normal'):
?>
			<dt><?= __('Carbon Flip') ?></dt>
			<dd><?= $carbon_flip_options[$game->home_carbon_flip] ?></dd>
<?php
		endif;

		if ((Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator || $game->division->league->display_sotg != 'coordinator_only') && $game->division->league->hasSpirit()):
?>
			<dt><?= __('Spirit for {0}', $this->Text->truncate($game->home_team->name, 18)) ?></dt>
			<dd><?= $this->element('Spirit/symbol', [
				'spirit_obj' => $spirit_obj,
				'league' => $game->division->league,
				'is_coordinator' => $is_coordinator,
				'entry' => $awaySpiritEntry,
			]) ?></dd>
			<dt><?= __('Spirit for {0}', $this->Text->truncate($game->away_team->name, 18)) ?></dt>
			<dd><?= $this->element('Spirit/symbol', [
				'spirit_obj' => $spirit_obj,
				'league' => $game->division->league,
				'is_coordinator' => $is_coordinator,
				'entry' => $homeSpiritEntry,
			]) ?></dd>
<?php
		endif;
	endif;

	if ($ratings_obj->perGameRatings() && $game->type == SEASON_GAME) {
		echo $this->element("Leagues/game/{$league_obj->render_element}/score", compact('game'));
	}
?>
			<dt><?= __('Score Approved By') ?></dt>
			<dd><?php
				if ($game->approved_by_id < 0) {
					$approved = Configure::read('approved_by');
					echo __($approved[$game->approved_by_id]);
				} else {
					echo $this->element('People/block', ['person' => $game->approved_by]);
				}
			?></dd>
		</dl>

<?php
else:
?>
		<p><?= __('The score of this game has not yet been finalized.') ?></p>
<?php
	if (!empty($game->score_entries) && (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator)):
?>
		<h3><?= __('Score as entered') ?></h3>
		<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th></th>
					<th><?= $this->Text->truncate($game->home_team->name, 23) . ' (' . __('home') . ')' ?></th>
					<th><?= $this->Text->truncate($game->away_team->name, 23) . ' (' . __('away') . ')' ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?= __('Home Score') ?></td>
					<td><?= isset($homeScoreEntry) ? $homeScoreEntry->score_for : __('not entered') ?></td>
					<td><?= isset($awayScoreEntry) ? $awayScoreEntry->score_against : __('not entered') ?></td>
				</tr>
				<tr>
					<td><?= __('Away Score') ?></td>
					<td><?= isset($homeScoreEntry) ? $homeScoreEntry->score_against : __('not entered') ?></td>
					<td><?= isset($awayScoreEntry) ? $awayScoreEntry->score_for : __('not entered') ?></td>
				</tr>
				<tr>
					<td><?= __('Defaulted?') ?></td>
					<td><?= isset($homeScoreEntry) ? ($homeScoreEntry->status == 'home_default' ? __('us') : ($homeScoreEntry->status == 'away_default' ? __('them') : __('no'))) : '' ?></td>
					<td><?= isset($awayScoreEntry) ? ($awayScoreEntry->status == 'away_default' ? __('us') : ($awayScoreEntry->status == 'home_default' ? __('them') : __('no'))) : '' ?></td>
				</tr>
<?php
		if ($game->division->league->hasCarbonFlip()):
?>
				<tr>
					<td><?= __('Carbon Flip') ?></td>
					<td><?php
					if (isset($homeScoreEntry)) {
						if ($homeScoreEntry->status == 'normal') {
							echo $carbon_flip_options[$homeScoreEntry->home_carbon_flip];
						} else {
							echo __('N/A');
						}
					}
					?></td>
					<td><?php
					if (isset($awayScoreEntry)) {
						if ($awayScoreEntry->status == 'normal') {
							echo $carbon_flip_options[$awayScoreEntry->home_carbon_flip];
						} else {
							echo __('N/A');
						}
					}
					?></td>
				</tr>
<?php
		endif;

		if ($gender_ratio_options):
?>
				<tr>
					<td><?= __('Opponent\'s Gender Ratio') ?></td>
					<td><?= isset($homeScoreEntry) && $homeScoreEntry->gender_ratio ? $gender_ratio_options[$homeScoreEntry->gender_ratio] : '' ?></td>
					<td><?= isset($awayScoreEntry) && $awayScoreEntry->gender_ratio ? $gender_ratio_options[$awayScoreEntry->gender_ratio] : '' ?></td>
				</tr>
<?php
		endif;
?>
				<tr>
					<td><?= __('Entered By') ?></td>
					<td><?= isset($homeScoreEntry) ? $this->element('People/block', ['person' => $homeScoreEntry->person]) : '' ?></td>
					<td><?= isset($awayScoreEntry) ? $this->element('People/block', ['person' => $awayScoreEntry->person]) : '' ?></td>
				</tr>
				<tr>
					<td><?= __('Entry Time') ?></td>
					<td><?= isset($homeScoreEntry) ? $this->Time->datetime($homeScoreEntry->modified) : '' ?></td>
					<td><?= isset($awayScoreEntry) ? $this->Time->datetime($awayScoreEntry->modified) : '' ?></td>
				</tr>
<?php
		if ($game->division->league->hasSpirit()):
?>
				<tr>
					<td><?= __('Spirit Assigned') ?></td>
					<td><?= $this->element('Spirit/symbol', [
						'spirit_obj' => $spirit_obj,
						'league' => $game->division->league,
						'is_coordinator' => $is_coordinator,
						'entry' => $awaySpiritEntry,
					]) ?></td>
					<td><?= $this->element('Spirit/symbol', [
						'spirit_obj' => $spirit_obj,
						'league' => $game->division->league,
						'is_coordinator' => $is_coordinator,
						'entry' => $homeSpiritEntry,
					]) ?></td>
				</tr>
<?php
		endif;
?>
			</tbody>
		</table>
		</div>
<?php
	else:
		$entry = $game->getBestScoreEntry();
		if ($entry === null) {
			echo $this->Html->para(null, __('The final scores entered by the teams do not match, and the discrepancy has not been resolved.'));
		}
	endif;

	if (!empty($entry)):
?>
		<p><?php
			if ($entry->team_id === null) {
				$name = __('A scorekeeper');
			} else {
				$name = $team_names[$entry->team_id];
			}
			if ($entry->status == 'in_progress') {
					echo __('{0} reported the following in-progress score as of {1}:',
					$name, $this->Time->time($entry->modified)
				);
			} else {
				echo __('{0} reported the final score as:', $name);
			}
		?></p>
		<dl class="dl-horizontal">
			<dt><?= $this->Text->truncate($game->home_team->name, 28) ?></dt>
			<dd><?= ($entry->team_id != $game->away_team_id ? $entry->score_for : $entry->score_against) ?></dd>
			<dt><?= $this->Text->truncate($game->away_team->name, 28) ?></dt>
			<dd><?= ($entry->team_id == $game->away_team_id ? $entry->score_for : $entry->score_against) ?></dd>
		</dl>
<?php
	endif;
endif;

if (!empty($game->score_details)):
?>
		<fieldset>
			<legend><?= __('Box Score') ?></legend>
			<div id="BoxScore">
				<ul><?php
					$start = $game->game_slot->start_time;
					$scores = [$game->home_team_id => 0, $game->away_team_id => 0];

					foreach ($game->score_details as $detail) {
						$time = $detail->created->diffInMinutes($start);
						if ($detail->play == 'Start') {
							$start = $detail->created;
							$line = $this->Time->dateTime($detail->created) . ' ' . __('Game started');
							$start_text = Configure::read("sports.{$game->division->league->sport}.start.box_score");
							if ($start_text) {
								$line .= ', ' . __($start_text, $team_names[$detail->team_id]);
							}
						} else if (Configure::read("sports.{$game->division->league->sport}.other_options.{$detail->play}")) {
							$line = sprintf("%d:%02d", $time / HOUR, ($time % HOUR) / MINUTE) . ' ' .
								$team_names[$detail->team_id] . ' ' . strtolower(Configure::read("sports.{$game->division->league->sport}.other_options.{$detail->play}"));
						} else {
							$line = sprintf("%d:%02d", $time / HOUR, ($time % HOUR) / MINUTE) . ' ' .
								$team_names[$detail->team_id] . ' ' .
								strtolower($detail->play);
							if ($detail->points) {
								$scores[$detail->team_id] += $detail->points;
								$line .= ' (' . implode(' - ', $scores) . ')';
							}
							$stats = [];
							foreach ($detail->score_detail_stats as $stat) {
								$stats[] = Inflector::singularize(strtolower($stat->stat_type->name)) . ' ' . $stat->person->full_name;
							}
							if (!empty($stats)) {
								$line .= ' (' . implode(', ', $stats) . ')';
							}
						}
						echo $this->Html->tag('li', $line);
					}
				?></ul>
<?php
	if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator) {
		echo $this->Html->iconLink('edit_24.png',
			['action' => 'edit_boxscore', 'game' => $game->id],
			['alt' => __('Edit Box Score'), 'title' => __('Edit Box Score')]);
	}
?>
			</div>
		</fieldset>
<?php
endif;
?>
	</fieldset>

<?php
if (!in_array($game->status, Configure::read('unplayed_status'))):

	if ($game->division->league->hasSpirit() &&
		(Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator || ($homeSpiritEntry !== null && $awaySpiritEntry !== null))
	) {
		echo $this->element('Spirit/view',
				['team' => $game->home_team, 'league' => $game->division->league, 'division' => $game->division, 'spirit' => $awaySpiritEntry, 'spirit_obj' => $spirit_obj]);
		echo $this->element('Spirit/view',
				['team' => $game->away_team, 'league' => $game->division->league, 'division' => $game->division, 'spirit' => $homeSpiritEntry, 'spirit_obj' => $spirit_obj]);
	}

	if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator):
		$allstars = collection($game->score_entries)->extract('allstars.{*}')->toArray();
		if (Configure::read('scoring.allstars') && $game->division->allstars && !empty($allstars)):
?>
	<fieldset>
		<legend><?= __('Allstars') ?></legend>
		<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Player') ?></th>
					<th><?= __('Team') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
			foreach ($allstars as $allstar):
?>
				<tr>
					<td><?= $this->element('People/block', ['person' => $allstar]) ?></td>
					<td><?= $allstar->_joinData->team_id == $game->home_team_id ? $game->home_team->name : $game->away_team->name ?></td>
					<td class="actions"><?= $this->Html->link(__('Delete'), ['controller' => 'Allstars', 'action' => 'delete', 'allstar' => $allstar->_joinData->id], ['confirm' => __('Are you sure you want to delete this allstar?')]) ?></td>
				</tr>

<?php
			endforeach;
?>
			</tbody>
		</table>
		</div>
	</fieldset>
<?php
		endif;

		if (Configure::read('scoring.incident_reports') && !empty($game->incidents)):
?>
	<fieldset>
		<legend><?= __('Incident Reports') ?></legend>
		<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('Reporting Team') ?></th>
					<th><?= __('Type') ?></th>
					<th><?= __('Details') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
			foreach ($game->incidents as $incident):
?>
				<tr>
					<td><?php
					if ($game->home_team_id == $incident->team_id) {
						echo $game->home_team->name;
					} else {
						echo $game->away_team->name;
					}
					?></td>
					<td><?= $incident->type ?></td>
					<td class="spirit-incident"><?= $incident->details ?></td>
				</tr>

<?php
			endforeach;
?>
			</tbody>
		</table>
		</div>
	</fieldset>
<?php
		endif;
	endif;
endif;

if (!empty($game->notes)):
?>
	<fieldset>
		<legend><?= __('Notes') ?></legend>
		<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?= __('From') ?></th>
					<th><?= __('Note') ?></th>
					<th><?= __('Visibility') ?></th>
					<th class="actions"><?= __('Actions') ?></th>
				</tr>
			</thead>
			<tbody>
<?php
	foreach ($game->notes as $note):
?>
				<tr>
					<td><?php
					echo $this->element('People/block', ['person' => $note->created_person]) .
						$this->Html->tag('br') .
						$this->Time->datetime($note->created) ?></td>
					<td><?= $note->note ?></td>
					<td><?= __(Configure::read("visibility.{$note->visibility}")) ?></td>
					<td class="actions"><?php
					if ($note->created_person_id == Configure::read('Perm.my_id')) {
						echo $this->Html->link(__('Edit'), ['action' => 'note', 'game' => $note->game_id, 'note' => $note->id]);
						echo $this->Html->link(__('Delete'), ['action' => 'delete_note', 'note' => $note->id], ['confirm' => __('Are you sure you want to delete this note?')]);
					}
					?></td>
				</tr>

<?php
	endforeach;
?>
			</tbody>
		</table>
		</div>
	</fieldset>
<?php
endif;
?>

</div>
