<div class="site">
    <div class="site-info">
        <strong class="name">
            <span class="health <?php echo $site['health_score_name']; ?>"
                title="Health: <?php echo $site['health_score_name']; ?>">&hearts;</span>
            <?php echo $site['name']; ?>
        </strong>
        <div class="url">
            <?php if ($site['supports']['HTTPS']): ?>
                <i class="fa fa-lock"></i>&nbsp;
            <?php endif ?>
            <a href="<?php echo $site['base_url']; ?>"><?php echo $site['base_url']; ?></a>
        </div>
        <div class="section">
            <span class="users"><?php echo $site['users']; ?> users</span>, 
            <span class="admin">admin: <a href="<?php echo $site['admin_profile']; ?>"><?php echo $site['admin_name']; ?></a></span>
        </div>
        <p class="description"><?php echo $site['info']; ?></p>
    </div>
    <div class="site-supports">
        <em>Features</em>
        <?php foreach ($site['popular_supports'] as $key => $value): if(!$value) continue; ?>
            
            <div class="supports <?php echo strtolower($key); ?>">
                <?php echo $key; ?><?php if($key == 'HTTPS' && $site['ssl_grade'] != null): ?>,&nbsp;Grade:&nbsp;<?php echo $site['ssl_grade']; ?><?php endif ?>&nbsp;&nbsp;&radic;
            </div>
        <?php endforeach ?>
        <?php if ($site['supports_more'] > 0): ?>
            
            <?php
            $more = '';
            foreach ($site['less_popular_supports'] as $key => $value){
                if(!$value) continue;
                $more .= $key.PHP_EOL;
            }
            ?>
            <abbr class="more" title="<?php echo $more ?>">+<?php echo $site['supports_more']; ?> more</abbr>
        <?php endif ?>
    </div>
</div>
