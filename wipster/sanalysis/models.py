from django.db import models
from django.utils import timezone
from django.core.files.storage import FileSystemStorage
import hashlib
import os
import string

#custom storage pulled from phoibos on stackoverflow.com
class MediaFileSystemStorage(FileSystemStorage):
    def get_available_name(self, name):
        return name

    def _save(self, name, content):
        if self.exists(name):
            # if the file exists, do not call teh superclasses _save method
            return name
        # if the file is new, DO call it
        return super(MediaFileSystemStorage, self)._save(name, content)

def media_file_name(instance, filename):
#    h = instance.md5sum
#    h = hashlib.md5(instance.sample.read()).hexdigest()
    h = instance.md5
    basename, ext = os.path.splitext(filename)
    basename = ''.join(e for e in basename if e.isalnum())
    return os.path.join('sanalysis', 'static', 'samples', h, basename.lower() + ext.lower() + '.MAL')

class Sample(models.Model):
    sample = models.FileField(
        upload_to=media_file_name, storage=MediaFileSystemStorage())
    ticket = models.CharField(max_length=32)
    filename = models.TextField(default='none')
    size = models.IntegerField(default=0)
    type = models.TextField(default='none')
    md5 = models.CharField(max_length=32)
    sha1 = models.CharField(max_length=40)
    sha256 = models.CharField(max_length=64)
    fuzzy = models.TextField(default='')
    created = models.DateTimeField(
        default=timezone.now)
#    yara = models.TextField()
    exif = models.TextField(default='')
    strings = models.TextField(default='')
    balbuzard = models.TextField(default='')
    trid = models.TextField(default='')

    peframe = models.TextField(default='')
    pescanner = models.TextField(default='')
    pdfid = models.TextField(default='')
    peepdf = models.TextField(default='')
    oleid = models.TextField(default='')
    olemeta = models.TextField(default='')
    olevba = models.TextField(default='')
    rtfobj = models.TextField(default='')
    rtfobj_str = models.TextField(default='')
    rtfobj_balbuz = models.TextField(default='')

    ssdeep_compare = models.TextField(default='')

    vt = models.TextField(default='')
    vt_short = models.TextField(default='')
    

    def __str__(self):
        return self.filename

    def save(self, *args, **kwargs):
        if not self.pk: # file is new
            md5 = hashlib.md5()
            for chunk in self.sample.chunks():
                 md5.update(chunk)
            self.md5sum = md5.hexdigest()
        super(Sample, self).save(*args, **kwargs)

