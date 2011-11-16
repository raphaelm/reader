#!/usr/bin/env python
# -*- coding: utf-8 -*-

import MySQLdb
import gfrbase
import time

class GFR_Feedfetcher(gfrbase.GFR):
	def __init__(self):
		gfrbase.GFR.__init__(self) # datenbankverbindung aufbauen
		
		onemonthago = time.time() - 2592000 # älter als 30 Tage
		self.cursor.execute("DELETE FROM `feeds_entries` WHERE `timestamp` < %s", (onemonthago,)) # zu alt
		self.cursor.execute("DELETE FROM `feeds_read` WHERE 0 = (SELECT COUNT(`article_id`) FROM `feeds_entries` WHERE `feeds_entries`.`article_id` = `feeds_read`.`article_id`)") # verwaiste einträge in feeds_read
		self.feedlist = self.getFeedList()
		self.getFeeds()
		
def main():
	GFR_Feedfetcher()
	return 0

if __name__ == '__main__':
	main()
