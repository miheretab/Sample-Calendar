<div id="events">
	<h2><?php echo date('l (M d, Y) ', strtotime($date)) . __('Events'); ?></h2>
	<table>
		<tbody>
			<?php foreach ($events as $event): ?>
				<tr>
					<td><?php echo h($event['Event']['name']); ?>&nbsp;</td>
					<td><?php echo h($event['Event']['description']); ?>&nbsp;</td>
					<td><?php echo h('location: ' . $event['Event']['location']); ?>&nbsp;</td>
				</tr>
				<tr class="secondrow">
					<td><?php echo h('From: ' . $event['Event']['event_from']); ?>&nbsp;</td>
					<td><?php echo h('To: ' . $event['Event']['event_to']); ?>&nbsp;</td>
					<td class="actions">
						<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $event['Event']['id'])); ?>
						<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $event['Event']['id']), null, __('Are you sure you want to delete # %s?', $event['Event']['id'])); ?>
					</td>	
				</tr>				
			<?php endforeach; ?>
		</tbody>
	</table>

</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Event'), array('action' => 'add', date('Y-m-d', strtotime($date)))); ?></li>
	</ul>
</div>
