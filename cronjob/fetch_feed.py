#!/usr/bin/env python
# -*- coding: utf-8 -*-

import MySQLdb
import gfrbase
import time, sys

class GFR_Onefeedfetcher(gfrbase.GFR):
	def __init__(self):
		gfrbase.GFR.__init__(self) # datenbankverbindung aufbauen
		if len(sys.argv) > 1:
			arg = sys.argv[1]
		else:
			sys.exit('No parameters.')
		self.guids = self.getEntryGuids()
		self.cursor.execute("SELECT `id`, `name`, `url` FROM `feeds` WHERE id = %s OR url = %s", (arg,arg))
		feed = self.cursor.fetchone()
		self.doAFeed(feed)
		
def main():
	GFR_Onefeedfetcher()
	return 0

if __name__ == '__main__':
	main()
