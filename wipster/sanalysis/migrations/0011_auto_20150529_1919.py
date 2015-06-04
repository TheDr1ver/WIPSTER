# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import models, migrations


class Migration(migrations.Migration):

    dependencies = [
        ('sanalysis', '0010_auto_20150529_1821'),
    ]

    operations = [
        migrations.AddField(
            model_name='sample',
            name='rtfobj_balbuz',
            field=models.TextField(default=b''),
        ),
        migrations.AddField(
            model_name='sample',
            name='rtfobj_str',
            field=models.TextField(default=b''),
        ),
    ]
