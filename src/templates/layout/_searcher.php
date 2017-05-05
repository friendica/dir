<form action="/search" method="get" class="search-form">
    <div class="search-wrapper">
        <input class="search-field" type="text" name="query" placeholder="Search your friends" tabindex="1" value="<?php echo isset($query)? $query : ''; ?>" />
        <a href="/directory" class="reset" tabindex="3">&#xf00d;</a>
        <input class="search" type="submit" value="Search" tabindex="2" />
    </div>
</form>
