# Cachet IRC bridge

This project is a simple Cachet IRC bridge, which leverages the Cachet API, a local sqlite status database and an external irker service to post updates from cachet to a configured IRC channel.

All settings are done using the environment it is executed under. You can either create a `.env.local` file containing your overrides, or set your environment values before running the command.

The following environment vars must be set for the bridge to work:

* `FRONTEND_HOST`: The base domain of the cachet instance, including a trailing slash, for example `https://status.yourdomain.local/`
* `IRKER_SERVER`: The dns name of the irker server, for example `irker.yourdomain.local`
* `IRC_ENDPOINT`: The irker formatted IRC endpoint, for example `irc://irc.tweakers.net:6667/bobv,isnick` for a nick and `irc://irc.tweakers.net:6667/#tweak` for a channel

You can run the command using cron, for example every minute:

```
*/1 * * * * ircbot /usr/bin/php -f /opt/cachet-irc-bridge/command.php -- -q
```
