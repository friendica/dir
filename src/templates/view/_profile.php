<figure class="profile" data-href="<?php echo $profile['homepage']; ?>" id="profile-<?php echo $profile['id']; ?>">
    <img class="profile-photo" src="<?php echo $this->photoUrl($profile['id']); ?>" />
    <div class="profile-info">
        <strong class="name"><?php echo $profile['name']; ?></strong>
        <div class="url"><?php echo $profile['homepage']; ?></div>
		<?php if ($profile['pdesc']): ?>
        <p class="description"><?php echo $profile['pdesc']; ?></p>
		<?php endif; ?>
        <div class="location">
            <?php
                $parts = array();
                if(!empty($profile['locality'])) $parts[] = $profile['locality'];
                if(!empty($profile['country-name'])) $parts[] = $profile['country-name'];
            ?>

            <?php if (count($parts)): ?>
                <i class="fa fa-globe"></i>
                <?php echo implode(', ', $parts); ?>
            <?php endif ?>

        </div>
		<?php if ($profile['tags']): ?>
		<div class="tags">
			<i class="fa fa-tag"></i>
			<?php
				$tags = array_map('trim', explode(' ', $profile['tags']));

				foreach($tags as $tag):?>

			<a href="/search?query=<?php echo urlencode($tag) ?>">#<?php echo htmlspecialchars($tag) ?></a>

			<?php endforeach;?>
		</div>
		<?php endif; ?>
    </div>

</figure>