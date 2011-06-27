#!/bin/sh
#

# PROVIDE: xbtt
# REQUIRE: NETWORKING mysql

# Add the following line to /etc/rc.conf to enable XBTT:
# xbtt_enable (bool):  Set to "NO" by default.
#                      Set it to "YES" to enable XBTT.
# xbtt_path (str):     Path to dir with xbt_tracker.conf

. /etc/rc.subr

name="xbtt"
rcvar=`set_rcvar`
start_precmd="${name}_prestart"

command="/db/www/xbtt/Tracker/xbt_tracker"

: ${xbtt_path="/db/www/xbtt/Tracker"}

xbtt_prestart()
{
	cd ${xbtt_path}
}

load_rc_config $name
run_rc_command "$1"


