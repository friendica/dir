# Decentralized Friendica Directory

## Installing

### 1. Initialize the database

Create a database with a username and a password.
Then import ````dfrndir.sql```` to it.

### 2. Create an autoloader with composer

Make sure you have composer installed globally, or rewrite the command to use a `.phar`.

```sh
composer dump-autoload
```

### 3. Set up the cronjobs.

Example cronjob using `www-data` user.

```
*/30 * * * * www-data cd /var/www/friendica-directory; php include/cron_maintain.php
*/5  * * * * www-data cd /var/www/friendica-directory; php include/cron_sync.php
```

## How syncing works

The new syncing features include: pushing and pulling.

### Pushing

Submissions you receive can be submitted to other directories using a push target.

You do this by creating an entry in the sync-targets table with the push bit set to `1`.
Also, you must enable pushing in your `.htconfig` settings.

The next time `include/cron_sync.php` is run from your cronjob, the queued items will be submitted to your push targets.

### Pulling

For pulling to work, the target server must enable pulling.
This makes the `/sync/pull/all` and `/sync/pull/since/[when]` methods work on that server.

Next you can add an entry in the sync-targets table with the pull bit set to `1`.
Also, you must enable pulling in your `.htconfig` settings.

The next time `include/cron_sync.php` is run from your cronjob, the pulling sources will be checked.
New items will be queued in your pull queue.
The queue will be gradually cleared based on your `syncing.max_pull_items` settings.
You can check the backlog of this queue at the `/admin` page.

## How submissions are processed

1.  The /submit endpoint takes a `?url=` parameter.
    This parameter is an encoded URL, the original ASCII is treated as binary and base16 encoded.
    This URL should be a profile location, such as `https://fc.oscp.info/profile/admin`.
    This URL will be checked in the database for existing accounts.
    This check includes a normalization, http vs https is ignored as well as www. prefixes.

2.  If noscrape is supported by the site, this will be used instead of a scrape request.
    In this case `https://fc.oscp.info/noscrape/admin`.
    If noscrape fails or is not supported, the url provided (as is) will be scraped for meta information.
    * `<meta name="dfrn-global-visibility" content="true" />`
    * `<meta name="friendica.community" content="true" />`
      or `<meta name="friendika.community" content="true" />`
    * `<meta name="keywords" content="these,are,your,public,tags" />`
    * `<link rel="dfrn-*" href="https://fc.oscp.info/*" />`
      any dfrn-* prefixed link and it's href attribute.
    * `.vcard .fn` as `fn`
    * `.vcard .title` as `pdesc`
    * `.vcard .photo` as `photo`
    * `.vcard .key` as `key`
    * `.vcard .locality` as `locality`
    * `.vcard .region` as `region`
    * `.vcard .postal-code` as `postal-code`
    * `.vcard .country-name` as `country-name`

3.  If the `dfrn-global-visibility` value is set to false. Any existing records will be deleted.
    And the process exits here.

4.  A submission is IGNORED when at least the following data could not be scraped.
    * `key` the public key from the hCard.
    * `dfrn-request` required for the DFRN protocol.
    * `dfrn-confirm` required for the DFRN protocol.
    * `dfrn-notify` required for the DFRN protocol.
    * `dfrn-poll` required for the DFRN protocol.

5.  If the profile existed in the database and the profile is not explicitly set to
    public using the `dfrn-global-visibility` meta tag. It will be deleted.

6.  If the profile existed in the database and the profile lacks either an `fn` or `photo`
    attribute, it will be deleted.

7.  The profile is now inserted/updated based on the found information.
    Notable database fields are:
    * `homepage` the originally (decoded) `?url=` parameter.
    * `nurl` the normalized URL created to remove http vs https and www vs non-www urls.
    * `created` the creation date and time in UTC (now if the entry did not exist yet).
    * `updated` the current date and time in UTC.

8.  If an insert has occurred, the URL will now be used to check for duplicates.
    The highest insert ID will be kept, anything else deleted.

9.  If provided, your public tags are now split by ` ` (space character) and stored in the tags table.
    This uses your normalized URL as unique key for your profile.

10. The `photo` provided will be downloaded and resized to 80x80, regardless of source size.

11. Should there somehow have been an error at this point such as that there is no profile ID known.
    Everything will get deleted based on the original `?url=` parameter.