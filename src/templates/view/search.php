<div class="sub-menu-outer">
    <div class="sub-menu-inner search-options">
        <a class="option <?php echo $filter === null    ? 'active' : '' ?>" href="<?php echo $this->filterAllUrl($query); ?>">All</a>
        <a class="option <?php echo $filter == 'people' ? 'active' : '' ?>" href="<?php echo $this->filterPeopleUrl($query); ?>">People</a>
        <a class="option <?php echo $filter == 'forums' ? 'active' : '' ?>" href="<?php echo $this->filterForumsUrl($query); ?>">Forums</a>
    </div>
</div>

<div class="search-results">
	<h3>Results for "<?php echo $query; ?>" (<?php echo $total; ?>)</h3>
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
</div>
