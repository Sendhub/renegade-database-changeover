# Heroku legacy database upgrade system #

This is very much not an automatic system, but by using this method you can minimize your applications downtime whilst switching over from a legacy 32-bit Heroku Postgres instance to a 64-bit one.


Request parameters required by receiveStatement.php:
@param host The hostname or identifier under which the statement will be stored
@param statement The SQL statement to store
@param timestamp The timestamp this statement was executed, used to determine statement order

Example request:

curl -v -vv 'http://x/receive.php?host=sendhub.com&statement=SELECT%321&timestamp=20110814213318


### Step 1: Install the php scripts on an apache webserver ###
Your Heroku application must be able to reach this webserver

### Step 2: Activate database statement logging ###

Note: This is only an example of how I got db statement logging working. All that matters is that the queries are somehow sent to receiveStatement.php.

Outline of a Django 1.3 integration:

#### Copy dependencies to your main Django module ####
The dependencies are:

-   deps/DbStatementLogHandler.py
-   deps/wget.py

#### Add the async task to dispatch the logged statements ####
Create the file "tasks.py" in your main Django module with the following content (__don't forget to put in your own hostname!__):

    # encoding: utf-8
    from celery.task import task

    @task()
    def asyncSendDbLog(timestamp, statement, host):
        """
        Send a logged statement to the logging server.

        Really, don't forget to put in your own logging server's hostname in here!
        """
        loggingServerHostname = 'dbupgrade.sendhub.com'
        import urllib
        from main..wget import wget
        print 'Doing the wget for statemnt={0}'.format(statement)
        wget(
            'http://{0}/receiveStatement.php?timestamp=' \
            '{1}&statement={2}&host={3}'.format(
                loggingServerHostname,
                timestamp,
                urllib.quote_plus(statement),
                host
            ),  
            '', 
            10 # Up to 10 retries for the HTTP request.
        )

#### In settings.py, configure the django logging facilities to use the new handler ####
    LOGGING = {
        'disable_existing_loggers': False,
        'version': 1,
        'handlers': {
            'console': {
                # logging handler that outputs log messages to terminal
                'class': 'logging.StreamHandler',
                'level': 'DEBUG', # message level to be written to console
            },  
            'dbStatementLogHandler': {
                'class': 'main.DbStatementLogHandler',
                'level': 'DEBUG',
            },  
        },  
        'loggers': {
            '': {
                # this sets root level logger to log debug and higher level
                # logs to console. All other loggers inherit settings from
                # root level logger.
                'handlers': ['console'],
                'level': 'DEBUG',
                'propagate': True, # this tells logger to send logging message
                                   # to its parent (will send if set to True)
            },  
            'django.db': {
                # django also has database level logging
                'handlers': ['dbStatementLogHandler'],
                'level': 'DEBUG',
            },  
        },  
    }

Note: If your primary Django module is not named "main" then naturally you'll need to adjust the `DbStatementLogHandler` class path to reflect the appropriate import path.

### 

