#!/usr/bin/env python
# -*- coding: utf-8 -*-

import MySQLdb
import feedparser

import datetime
import hashlib
import time
import zlib
import config
import os, sys
import urlparse 
import urllib
import socket

class GFR:
	def __init__(self):
		socket.setdefaulttimeout(15)
		try:
			self.conn = MySQLdb.connect(host = config.dbhostname,
							   user = config.dbusername,
							   passwd = config.dbpassword,
							   db = config.database)
			self.cursor = self.conn.cursor()
		except MySQLdb.Error, e:
			print "Error %d: %s" % (e.args[0], e.args[1])
			sys.exit(1)
	
	def getFeedList(self):
		self.cursor.execute("SELECT `id`, `name`, `url` FROM `feeds`")
		return self.cursor.fetchall()
	
	def getEntryGuids(self):
		self.cursor.execute("SELECT `guid`, `contenthash` FROM `feeds_entries`")
		res = self.cursor.fetchall()
		ret = []
		ret2 = []
		for r in res:
			ret.append(r[0])
			ret2.append(r[1])
		return (tuple(ret), tuple(ret2))
		
	def getFeeds(self):
		(self.guids, self.hashs) = self.getEntryGuids()
		for feed in self.feedlist:
			feed_id = int(feed[0])
			try:
				feed_title = feed[1].encode("utf-8", "ignore")
			except UnicodeDecodeError:
				feed_title = feed[1]
			except UnicodeError:
				feed_title = feed[1]
			feed_url = feed[2]
			try:
				self.getFeed(feed_id, feed_title, feed_url)
			except:
				print "error on parsing feed %s" % feed_id
			
	def getFeed(self, feed_id, feed_title, feed_url):
		start = time.time()
	
		try:
			iconname = config.icondir+'/'+str(feed_id)+'.png';
			if not os.path.exists(iconname) or os.path.getmtime(iconname) < time.time()-(3600*24):
				webFile = urllib.urlopen('http://g.etfv.co/%s?defaulticon=1pxgif' % feed_url)
				localFile = open(iconname, 'w')
				localFile.write(webFile.read())
				webFile.close()
				localFile.close()
		except:
			#print "Error fetching icon for feed %s" % feed_url
			pass
		
		f = feedparser.parse(feed_url)
		
		if f.version == '':
			return False
		else:
			self.cursor.execute("UPDATE `feeds` Set `lastupdate` = %s WHERE `id` = %s", (time.time(), feed_id))
			
		try:
			if f.feed.title.encode("utf-8") != feed_title and not f.feed.title.startswith("http://"): # feed_title ist anders als letztes Mal
				feed_title = f.feed.title
				if feed_title.strip() == '':
					feed_title = feed_url
				self.cursor.execute("UPDATE `feeds` Set `name` = %s WHERE `id` = %s", (feed_title, feed_id))
		except:
			#print "Error parsing title for feed %s" % feed_url
			pass
			
		if len(f.entries) > 0:
			for entry in f.entries:
				try:
					entry_title = entry['title'].encode("utf-8")
					if len(entry_title) > 255:
						entry_title = entry_title[0:250]+'…'
				except KeyError:
					entry_title = 'Unknown title'
					
				try:
					entry_summary = entry['content'][0]['value'].encode("utf-8")
				except KeyError:
					try:
						entry_summary = entry['summary'].encode("utf-8")
						
					except KeyError:
						entry_summary = ''
					
				try:
					entry_link = entry['link'].encode("utf-8")
				except KeyError:
					entry_link = 'http://example.org/?invalid'
					
				entry_contenthash = hashlib.sha1(entry_summary).hexdigest()
				
				# Create a unique identifier for the post
				entry_guid = None
				try:
					# The feed provides one? Awesome!
					entry_guid = entry['id'].encode("utf-8")
				finally:
					# The feed doesn't? We choose.
					if entry_guid is None or len(entry_guid) < 10:
						entry_guid = hashlib.sha1(entry_link+entry_title+str(feed_id)).hexdigest()
						
				try:
					entry_updated = entry['updated_parsed']
					entry_date = datetime.datetime(entry_updated[0], entry_updated[1], 
												   entry_updated[2], entry_updated[3], 
												   entry_updated[4], entry_updated[5], 
												   entry_updated[6])
					entry_timestamp = time.mktime(entry_date.timetuple())
				except KeyError:
					entry_timestamp = time.time()
					
				if entry_timestamp < time.time() - 2592000:
					continue
				
				entry_summary = zlib.compress(entry_summary, 9)
				
				qp = (feed_id, entry_title, entry_link, entry_guid, entry_contenthash, int(entry_timestamp), entry_summary)
				
				if entry_guid not in self.guids: # no duplicates
					try:
						self.cursor.execute("INSERT INTO `feeds_entries` (`feed_id`, `title`, `url`, `guid`, `contenthash`, `timestamp`, `summary`) VALUES (%s, %s, %s, %s, %s, %s, %s)", qp)
					finally:
						pass
				elif entry_contenthash not in self.hashs:
					try:
						self.cursor.execute("REPLACE INTO `feeds_entries` (`feed_id`, `title`, `url`, `guid`, `contenthash`, `timestamp`, `summary`) VALUES (%s, %s, %s, %s, %s, %s, %s)", qp)
					finally:
						pass
						
		end = time.time()
		d = end-start
		if d > 5:
			self.cursor.execute("UPDATE `feeds` Set `slower` = 1 WHERE `id` = %s", (feed_id))
		else:
			self.cursor.execute("UPDATE `feeds` Set `slower` = 0 WHERE `id` = %s", (feed_id))
