# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import models, migrations


class Migration(migrations.Migration):

    dependencies = [
        ('sanalysis', '0008_auto_20150526_2011'),
    ]

    operations = [
        migrations.AddField(
            model_name='sample',
            name='ssdeep_compare',
            field=models.TextField(default=b''),
        ),
    ]
