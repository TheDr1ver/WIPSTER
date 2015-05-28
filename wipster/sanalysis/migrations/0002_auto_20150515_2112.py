# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import models, migrations


class Migration(migrations.Migration):

    dependencies = [
        ('sanalysis', '0001_initial'),
    ]

    operations = [
        migrations.AddField(
            model_name='sample',
            name='filename',
            field=models.TextField(default=b'none'),
        ),
        migrations.AddField(
            model_name='sample',
            name='size',
            field=models.IntegerField(default=0),
        ),
        migrations.AddField(
            model_name='sample',
            name='type',
            field=models.TextField(default=b'none'),
        ),
    ]
