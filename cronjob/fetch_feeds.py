#!/usr/bin/env python
# -*- coding: utf-8 -*-

import MySQLdb
import gfrbase
import time
		
def main():
	g = gfrbase.GFR()
	onemonthago = time.time() - 2592000 # älter als 30 Tage
	g.cursor.execute("DELETE FROM `feeds_entries` WHERE `timestamp` < %s", (onemonthago,)) # zu alt
	g.cursor.execute("DELETE FROM `feeds_read` WHERE 0 = (SELECT COUNT(`article_id`) FROM `feeds_entries` WHERE `feeds_entries`.`article_id` = `feeds_read`.`article_id`)") # verwaiste einträge in feeds_read
	g.cursor.execute("DELETE FROM `sticky` WHERE 0 = (SELECT COUNT(`article_id`) FROM `feeds_entries` WHERE `feeds_entries`.`article_id` = `sticky`.`article_id`)") # verwaiste einträge in sticky
	g.cursor.execute("DELETE FROM `feeds_subscription` WHERE 0 = (SELECT COUNT(`id`) FROM `feeds` WHERE `feeds`.`id` = `feeds_subscription`.`feedid`) OR 0 = (SELECT COUNT(`id`) FROM `user` WHERE `user`.`id` = `feeds_subscription`.`userid`)") # verwaiste einträge in subscriptions
	g.cursor.execute("DELETE FROM `sessions` WHERE `expire` < %d" % time.time()) # abgelaufene sessions
	g.getFeeds(4)
	return 0

if __name__ == '__main__':
	main()
