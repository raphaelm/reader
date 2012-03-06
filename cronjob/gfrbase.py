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
import threading
import Queue

class GFR:
	guids = None
	feedlist = None
	hashs = None
	conn = None
	cursor = None
	threads = None
	sqlqueue = None
	running = True
	todo = None
	
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
		if config.checkforupdates:
			self.checkForUpdates()
				
	def checkForUpdates(self):
		updateurl = "http://reader.geeksfactory.de/latest.txt"
		h = urllib.urlopen(updateurl)
		c = h.read(22).strip()
		latest = False
		if c.startswith('LATESTSTABLE:'):
			v = c[13:]
			if len(v) == 8:
				latest = int(v)
		own = int(open(config.maindir+'/version.txt').read().strip())
		
		if latest > own:
			print h.read()
		h.close()
	
	def getFeedList(self):
		self.cursor.execute("SELECT `id`, `name`, `url` FROM `feeds`")
		self.feedlist = self.cursor.fetchall()
	
	def getEntryGuids(self):
		self.cursor.execute("SELECT `guid`, `contenthash` FROM `feeds_entries`")
		res = self.cursor.fetchall()
		self.guids = set()
		self.hashs = set()
		for r in res:
			self.guids.add(r[0])
			self.hashs.add(r[1])
		
	def _threaded_sql_exec(self, q, params = None):
		if self.threads is None:
			self.cursor.execute(q, params)
		else:
			self.sqlqueue.put((q, params))
		
	def sqlworker(self):
		conn = MySQLdb.connect(host = config.dbhostname,
						   user = config.dbusername,
						   passwd = config.dbpassword,
						   db = config.database)
		cursor = conn.cursor()
		while self.running:
			try:
				item = self.sqlqueue.get(True, 1)
			except:
				continue
			self.cursor.execute(item[0], item[1])
			conn.commit()
			self.sqlqueue.task_done()
			
		
	def getFeeds(self, threads=1):	
		self.getFeedList()
		
		if self.guids is None or type(self.guids) is not type(set()):
			self.getEntryGuids()
			
		if threads == 1:
			for feed in self.feedlist:
				self.doAFeed(feed)
		else:
			self.todo = self.feedlist
			self.sqlqueue = Queue.Queue()
			self.threads = []
			self.todo_lock = threading.Lock()
			for i in xrange(threads):
				t = threading.Thread(target=self.worker)
				t.start()
				self.threads.append(t)
				
			sqlt = threading.Thread(target=self.sqlworker)
			sqlt.start()
				
			for t in self.threads:
				t.join()
			
			self.running = False
			sqlt.join()
				
	def worker(self):		
		while True:
			self.todo_lock.acquire()
			if len(self.todo) == 0:
				self.todo_lock.release()
				return True
			feed = self.todo[0]
			self.todo = self.todo[1:]
			self.todo_lock.release()
			self.doAFeed(feed)
		
	def doAFeed(self, feed):
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
		except KeyboardInterrupt:
			sys.exit(0)
		except:
			print "Error parsing feed", feed_id, feed_url
			
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
			self._threaded_sql_exec("UPDATE `feeds` Set `lastupdate` = %s WHERE `id` = %s", (time.time(), feed_id))
			
		try:
			if f.feed.title.encode("utf-8") != feed_title and not f.feed.title.startswith("http://"): # feed_title ist anders als letztes Mal
				feed_title = f.feed.title
				if feed_title.strip() == '':
					feed_title = feed_url
				self._threaded_sql_exec("UPDATE `feeds` Set `name` = %s WHERE `id` = %s", (feed_title, feed_id))
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
					
				if entry_summary != '':
					entry_contenthash = hashlib.sha1(entry_summary).hexdigest()
				else:
					entry_contenthash = hashlib.sha1(entry_title).hexdigest()
				
				# Create a unique identifier for the post
				entry_guid = None
				origguid = "" # DEBUGGING
				try:
					# The feed provides one? Awesome!
					entry_guid = hashlib.sha1(str(feed_id)+entry['id'].encode("utf-8")).hexdigest()
					origguid = entry['id'].encode("utf-8") # DEBUGGING
				finally:
					# The feed doesn't? We choose.
					if entry_guid is None or len(entry_guid) < 10:
						entry_guid = hashlib.sha1(entry_link+entry_title+str(feed_id)).hexdigest()
						origguid = entry_link+entry_title.encode("utf-8")+str(feed_id) # DEBUGGING
						
				try:
					entry_updated = entry['updated_parsed']
					entry_date = datetime.datetime(entry_updated[0], entry_updated[1], 
												   entry_updated[2], entry_updated[3], 
												   entry_updated[4], entry_updated[5], 
												   entry_updated[6])
					entry_timestamp = time.mktime(entry_date.timetuple())
				except KeyError:
					entry_timestamp = time.time()
				except TypeError:
					entry_timestamp = time.time()
					
				if entry_timestamp < time.time() - 2592000:
					continue
				
				entry_summary_zipped = zlib.compress(entry_summary, 9)
				
				qp = (feed_id, entry_title, entry_link, entry_guid, entry_contenthash, int(entry_timestamp), entry_summary_zipped, origguid)
				if entry_guid not in self.guids: # no duplicates
					try:
						self._threaded_sql_exec("INSERT INTO `feeds_entries` (`feed_id`, `title`, `url`, `guid`, `contenthash`, `timestamp`, `summary`, original_guid) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)", qp)
						self.hashs.add(entry_contenthash)
						self.guids.add(entry_guid)
					finally:
						pass
				elif entry_contenthash not in self.hashs:
					try:
						self._threaded_sql_exec("UPDATE `feeds_entries` Set `title` = %s, `url` = %s, `contenthash` = %s, `timestamp` = %s, `summary` = %s, `updated` = `updated`+1 WHERE `guid` = %s", (qp[1], qp[2], qp[4], qp[5], qp[6], qp[3]))
						self._threaded_sql_exec("DELETE FROM `feeds_read` WHERE `article_id` = (SELECT `article_id` FROM `feeds_entries` WHERE `guid` = %s) AND (SELECT `updates` FROM `feeds_subscription` WHERE `feeds_read`.`user_id` = `feeds_subscription`.`userid` AND `feeds_subscription`.`feedid` = (SELECT `feed_id` FROM `feeds_entries` WHERE `guid` = %s)) = 1", (qp[3],qp[3]))
						self.hashs.add(entry_contenthash)
					finally:
						pass
						
		end = time.time()
		d = end-start
		if d > 5:
			self._threaded_sql_exec("UPDATE `feeds` Set `slower` = 1 WHERE `id` = %s", (feed_id))
		else:
			self._threaded_sql_exec("UPDATE `feeds` Set `slower` = 0 WHERE `id` = %s", (feed_id))
