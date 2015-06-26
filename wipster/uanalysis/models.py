from django.db import models
from django.utils import timezone


class URL(models.Model):
    uri = models.TextField(default='')
    ticket = models.CharField(max_length=32)
    md5 = models.CharField(max_length=32)
    html = models.TextField(default='')
    thug = models.TextField(default='')
    js = models.TextField(default='')
    js_didier = models.TextField(default='')
    vt = models.TextField(default='')
    #ua = models.ChoiceField(choices=['Internet Explorer 6.0 (Windows XP)',
    #                                 'Internet Explorer 8.0 (Windows XP)',
    #                                 'Firefox 3.6.14 (Windows 7)',
    #                                 'Chrome 20.0.1132.47 (Windows 7)'])
    created = models.DateTimeField(
        default=timezone.now)
    
    def __str__(self):
        return self.uri

    def __unicode__(self):
        if isinstance(self.uri, unicode):
            return self.uri
        else:
            return unicode(self.uri,'utf-8')