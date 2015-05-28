# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import models, migrations


class Migration(migrations.Migration):

    dependencies = [
        ('sanalysis', '0004_sample_exif'),
    ]

    operations = [
        migrations.AddField(
            model_name='sample',
            name='strings',
            field=models.TextField(default=b''),
        ),
    ]
