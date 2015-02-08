<a class="profile" href="<?php echo $profile['homepage']; ?>" target="_blank">
    
    <img class="profile-photo" src="<?php echo $this->photoUrl($profile['id']); ?>" />
    <div class="profile-info">
        <strong class="name"><?php echo $profile['name']; ?></strong>
        <div class="url"><?php echo $profile['homepage']; ?></div>
        <div class="description"><?php echo $profile['pdesc']; ?></div>
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
    </div>
    
</a>