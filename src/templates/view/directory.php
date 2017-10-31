<div class="sub-menu-outer">
	<div class="sub-menu-inner search-options">
		<a class="option <?php echo empty($filter)      ? 'active' : '' ?>" href="<?php echo $this->filterAllUrl(''); ?>">All</a>
		<a class="option <?php echo $filter == 'people' ? 'active' : '' ?>" href="<?php echo $this->filterPeopleUrl(''); ?>">People</a>
		<a class="option <?php echo $filter == 'forums' ? 'active' : '' ?>" href="<?php echo $this->filterForumsUrl(''); ?>">Forums</a>
	</div>
</div>

<div class="directory-results">
	<aside>
		<?php echo tags_widget() ?>
		<?php echo country_widget() ?>
	</aside>
	<section>
		<div class="profiles">
			<?php if (count($results)): ?>

				<?php
					foreach ($results as $profile) {
						echo $this->view('_profile', array('profile' => $profile));
					}
				?>

			<?php else: ?>

				<h3>There were no results</h3>

			<?php endif ?>

		</div>
		<?php echo $this->paginate();?>
	</section>
</div>
