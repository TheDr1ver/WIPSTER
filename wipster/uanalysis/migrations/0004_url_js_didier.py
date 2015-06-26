# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import models, migrations


class Migration(migrations.Migration):

    dependencies = [
        ('uanalysis', '0003_url_thug'),
    ]

    operations = [
        migrations.AddField(
            model_name='url',
            name='js_didier',
            field=models.TextField(default=b''),
        ),
    ]
