# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import models, migrations


class Migration(migrations.Migration):

    dependencies = [
        ('uanalysis', '0002_auto_20150623_2330'),
    ]

    operations = [
        migrations.AddField(
            model_name='url',
            name='thug',
            field=models.TextField(default=b''),
        ),
    ]
