
Fetches stats from [CDN77](http://www.cdn77.com/) via their API and stores and displays them.
Useful for showing basic stats to clients without giving them your CDN77 account,
as well as maintaining a more complete history than CDN77's admin panel shows you.

## Setup ##

- Requires PHP5.2+ and the sqlite and curl PHP modules.
- Copy `settings.example.php` to `settings.php` and edit it.
- Be sure to keep your key file safe i.e. out of the webroot and chmodded to minimal permissions on shared hosts.
- Note that the directory containing the sqlite file must be writable by the web server.
- Set up cron to call the script regularly in order to maintain a complete history.
- Secure it behind HTTP authentication if you wish.
