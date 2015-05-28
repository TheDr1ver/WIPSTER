# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import models, migrations


class Migration(migrations.Migration):

    dependencies = [
        ('sanalysis', '0003_auto_20150521_1842'),
    ]

    operations = [
        migrations.AddField(
            model_name='sample',
            name='exif',
            field=models.TextField(default=b''),
        ),
    ]
