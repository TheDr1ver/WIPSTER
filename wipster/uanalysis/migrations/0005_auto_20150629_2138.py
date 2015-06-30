# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import models, migrations


class Migration(migrations.Migration):

    dependencies = [
        ('uanalysis', '0004_url_js_didier'),
    ]

    operations = [
        migrations.AddField(
            model_name='url',
            name='fuzzy',
            field=models.TextField(default=b''),
        ),
        migrations.AddField(
            model_name='url',
            name='ssdeep_compare',
            field=models.TextField(default=b''),
        ),
    ]
