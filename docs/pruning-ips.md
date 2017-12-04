# How to prune IPs automatically

For the benefit of moderation, Postmill stores IP addresses for new submissions,
comments, votes, and private messages. With the `app:prune-ips` command, you can
purge these IP addresses routinely to protect the privacy of the site's
visitors.

Add the following line to your crontab:

    @daily /path/to/postmill/bin/console app:prune-ips -n -q -m '1 day ago'

This will purge IP addresses that have been kept for longer than a day.

Run `bin/console app:prune-ips --help` for more information.
