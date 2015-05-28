# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import models, migrations


class Migration(migrations.Migration):

    dependencies = [
        ('sanalysis', '0005_sample_strings'),
    ]

    operations = [
        migrations.AddField(
            model_name='sample',
            name='balbuzard',
            field=models.TextField(default=b''),
        ),
    ]
