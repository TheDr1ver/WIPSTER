# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import models, migrations


class Migration(migrations.Migration):

    dependencies = [
        ('sanalysis', '0009_sample_ssdeep_compare'),
    ]

    operations = [
        migrations.AddField(
            model_name='sample',
            name='vt',
            field=models.TextField(default=b''),
        ),
        migrations.AddField(
            model_name='sample',
            name='vt_short',
            field=models.TextField(default=b''),
        ),
    ]
