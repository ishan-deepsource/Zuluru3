<?php
$classes[] = 'center';
$cols = 11 + $league->hasSpirit() + $league->hasCarbonFlip() * 4;
?>
<tr>
	<td colspan="<?= $cols ?>" class="<?= implode(' ', $classes) ?>"><?= $this->Html->link('... ... ...', ['action' => 'standings', 'division' => $division->id, 'team' => $team_id, 'full' => 1]) ?></td>
</tr>
