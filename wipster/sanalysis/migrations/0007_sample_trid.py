# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import models, migrations


class Migration(migrations.Migration):

    dependencies = [
        ('sanalysis', '0006_sample_balbuzard'),
    ]

    operations = [
        migrations.AddField(
            model_name='sample',
            name='trid',
            field=models.TextField(default=b''),
        ),
    ]
