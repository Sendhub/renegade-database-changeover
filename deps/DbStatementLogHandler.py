# encoding: utf-8

"""DB Statement Log Handler"""

import logging

_currentSite = None
_gettingCurrentSite = False

class DbStatementLogHandler(logging.StreamHandler):
    """Simple db statement log handler."""

    def emit(self, record):
        """Overridden method to hijack the sql query logging."""
        print 'GOT AN EMIT, RECORD={0}'.format(record)
        lowerSql = record.sql.replace('\n', ' ').replace('\r', ' ').strip().lower()

        # Don't care about preserving SELECT's or data manipulation involving the celery tables.
        if lowerSql.startswith('select ') or lowerSql.startswith('insert into "celery_taskmeta" (') or lowerSql.startswith('savepoint '): 
            return

        # Import here to avoid circular dependency issues.
        from main import tasks as _tasks

        if _gettingCurrentSite is True:
            # Safe to ignore, because this is the site lookup statement.
            return

        if _currentSite is None:
            self._initCurrentSite()

        sql = record.sql
        # Ensure that the SQL will be ready for execution.
        if not sql.strip().endswith(';'):
            sql = '{0};'.format(sql)

        _tasks.asyncSendDbLog.delay(record.created, sql, _currentSite)

    def _initCurrentSite(self):
        """Initialize the _currentSite variable with the value from the db."""
        global _currentSite, _gettingCurrentSite
        from django.contrib.sites.models import Site
        _gettingCurrentSite = True
        _currentSite = Site.objects.get_current()
        _gettingCurrentSite = False

