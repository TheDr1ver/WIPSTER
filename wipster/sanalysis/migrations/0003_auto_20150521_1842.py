# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import models, migrations
import sanalysis.models


class Migration(migrations.Migration):

    dependencies = [
        ('sanalysis', '0002_auto_20150515_2112'),
    ]

    operations = [
        migrations.AddField(
            model_name='sample',
            name='fuzzy',
            field=models.TextField(default=b''),
        ),
        migrations.AlterField(
            model_name='sample',
            name='sample',
            field=models.FileField(storage=sanalysis.models.MediaFileSystemStorage(), upload_to=sanalysis.models.media_file_name),
        ),
    ]
