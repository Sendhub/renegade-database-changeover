# encoding: utf-8

__author__ = 'Jay Taylor [@jtaylor]'

"""
This library makes it easy execute HTTP GET requests.

@date 2010-11-01

Copyright Jay Taylor 2010
"""

import urllib2

# For G-Zip decompression.
import gzip, StringIO


USER_AGENT = 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.15) Gecko/20110303 Firefox/3.6.15'

class WgetError(Exception):
    pass


def wget(url, referer='', num_tries=1):
    """
    @param referer Defaults to ''.  If you pass None, it will be the same as
        the target URL.
    """
    if referer == None:
        referer = url
    opener = urllib2.build_opener()
    opener.addheaders = [
        ('User-agent', USER_AGENT),
        ('Referer', referer),
        ('Accept-encoding', 'gzip'),
    ]
    try:
        data = opener.open(url).read()
        try:
            compressedstream = StringIO.StringIO(data)
            gzipper = gzip.GzipFile(fileobj=compressedstream)
            data = gzipper.read()
        except IOError:
            pass
        return data
    except urllib2.URLError, e:
        if num_tries > 1:
            return wget(url, referer, num_tries - 1)
        raise WgetError(url + ' failed, ' + str(e))

