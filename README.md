# WHM cPanel PHP Hacker Wall 
It's really annoying how often the Wordpress site is hacked. Then I created this little script to help you prevent prevent your WHM / cPanel server PHP get hacker

# Installation

* Create a new database in your mysql.
* Execute this SQL on your new DB
```SQL
CREATE TABLE `hack_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `url` varchar(8000) DEFAULT NULL,
  `method` varchar(20) DEFAULT NULL,
  `post` text,
  `files` text,
  `server` text,
  `flag` tinyint(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


CREATE TABLE `hack_login` (
  `ip` int(10) unsigned NOT NULL,
  `lastUpdate` datetime DEFAULT NULL,
  `lastUrl` varchar(255) DEFAULT NULL,
  `count` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
```

* Set configuration variables in configs/db.php

* Open /usr/local/lib/php.ini in your favorite editor.
* Define this property : 
```
auto_prepend_file = /path/to/wall.php
```

# Notes
As you can see this script does not follow the general criterion of clean coding. But it allowed me to sleep and significantly reduce attacks. I used it on a server with more than 100 Wordpress webs sites.

Use this script at your own risk. 