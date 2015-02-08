<?php echo $this->layout('_navigation'); ?>

<div class="homepage-wrapper">
    
    <h1 class="header">
        Friendica &nbsp; &nbsp;<br>&nbsp; &nbsp; Directory
    </h1>
    
    <?php echo $this->layout('_searcher'); ?>
    
    <p class="about">
        Friendica is a decentralized social network.
        And this is a directory to find people on this network.
        Vivamus condimentum tempor pellentesque. Phasellus turpis nulla, lacinia vitae quam in,
        cursus semper est. Ut lobortis ex quis sodales porta. Nam rhoncus tortor lobortis auctor
        efficitur. Ut ac ullamcorper lorem.
    </p>
    
    <div class="profiles">
        <h3>Random groups</h3>
        <?php foreach ($profiles as $profile)
            echo $this->view('_profile', array('profile'=>$profile));
        ?>
    </div>
    
</div>
