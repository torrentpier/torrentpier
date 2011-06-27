TBDev Yuna Scatari Edition pre6 to TorrentPier 1.0.x converter.

Quick guide:
1. Install and configure TorrentPier 1.0.0 or higher.
2. Import your TBDevYSE database into TorrentPier database (concretely 'comments', 'categories', 'torrents' and 'users' tables).
3. Put the contents of folder 'root' into your TorrentPier root.
4. Configure 'converter/settings.php' for optimal settings.
5. Run convert.php.
6. See 'converter/passwords.php' for new password to login under admin account.
7. Resychronize statistics using admin panel.
8. Make your new tracker popular!
----
9. If you need to notify users for new passwords (which are needed to login in TorrentPier) via TBDevYSE PM system,
   copy two files: automatically generated 'converter/passwords.php' and 'for_tbdev/pass.php' to your TBDevYSE root and run pass.php
   (Don't forget to remove these files after completion).
   You allow to change message text, see $msg in pass.php for this.
10. If you want to redirect peers from older announce to new announce everytime, replace original TBDev's announce.php with  
	'for_tbdev/announce.php'

Cheers, RoadTrain.
http://torrentpier.info/