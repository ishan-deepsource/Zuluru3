<p><?= __('For all divisions except those with the "tournament" schedule type, you have the option to create a playoff schedule. Start from the {0} "Add Games" page, look for the "create a playoff schedule" link. This will take you through the normal "add games" process, but with the applicable tournament scheduling options (based on the number of teams in the division).',
	$this->Html->iconImg('schedule_add_24.png')
) ?></p>
<p><?= __('Note that you can do this <strong>at any time</strong>. Suggested practice is to schedule <strong>and publish</strong> the playoffs at the <strong>beginning</strong> of the season, preferably as soon as all of the teams are placed in the division. This way, players know exactly what form the playoffs will be taking (e.g. for a 8 team league, it\'s immediately clear whether it\'s a two-week playoff where only the top 4 teams compete for the championship, or a three-week playoff where everyone has a shot).') ?></p>
<?php
echo $this->element('Help/topics', [
	'section' => 'schedules/playoffs',
	'topics' => [
		'initialize' => [
			'title' => 'Initialize Schedule Dependencies',
			'image' => 'initialize_32.png',
		],
		'reset' => [
			'title' => 'Reset Schedule Dependencies',
			'image' => 'reset_32.png',
		],
	],
	'compact' => true,
]);
