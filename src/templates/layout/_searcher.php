<form action="/search" method="get" class="search-form">
    <div class="search-wrapper">
        <input class="search-field" type="text" name="query" placeholder="Search your friends" tabindex="1" value="<?php echo $query; ?>" />
        <input class="reset" type="reset" value="&#xf00d;" tabindex="3" />
        <input class="search" type="submit" value="Search" tabindex="2" />
    </div>
</form>
