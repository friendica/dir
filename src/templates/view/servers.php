<div class="search-results">
    <div class="sites">
        <h1>Public servers</h1>
        <p class="intro">
            If you are not interested in <a href="http://friendi.ca/installation/">hosting your own server</a>, you can still use Friendica.
            Here are some public server run by enthousiasts that you can join.
            We recommend these based on their <abbr title="Decent speed, proper security, recent version, etc.">health</abbr>.
        </p>
        <p class="intro">
            Keep in mind that different servers may support different features like communicating with additional networks besides Friendica.
            It's best to pick the one that best suits your needs.
        </p>

        <div class="feeling-lucky">
            <a class="btn surprise" href="/servers/surprise">Surprise me &raquo;</a>
        </div>

        <h3>Recommending <?php echo $total; ?> public servers</h3>
        <?php
            foreach ($sites as $site)
                echo $this->view('_site', array('site'=>$site));
        ?>
    </div>
</div>