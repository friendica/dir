<?php echo $this->layout('_navigation'); ?>

<div class="homepage-wrapper">
    
    <h1 class="header">
        Friendica &nbsp; &nbsp;<br>&nbsp; &nbsp; Directory
    </h1>
    
    <?php echo $this->layout('_searcher'); ?>
    
    <p class="about">
        Friendica is a decentralized social network.
        And this is a directory to find people on this network.
        If you want to create your own account on a public server, have a look 
        on our <a href="servers">Public servers listing</a>.
    </p>
    
    <div class="profiles">
        <h3>Random groups</h3>
        <?php foreach ($profiles as $profile)
            echo $this->view('_profile', array('profile'=>$profile));
        ?>
    </div>
    
</div>
