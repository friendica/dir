# Friendica Global Directory

Example cronjob.

```
*/30 * * * * www-data cd /var/www/friendica-directory; php include/cron_maintain.php
*/5  * * * * www-data cd /var/www/friendica-directory; php include/cron_sync.php
```