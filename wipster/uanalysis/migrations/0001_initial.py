# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import models, migrations
import django.utils.timezone


class Migration(migrations.Migration):

    dependencies = [
    ]

    operations = [
        migrations.CreateModel(
            name='URL',
            fields=[
                ('id', models.AutoField(verbose_name='ID', serialize=False, auto_created=True, primary_key=True)),
                ('url', models.TextField(default=b'')),
                ('ticket', models.CharField(max_length=32)),
                ('md5', models.CharField(max_length=32)),
                ('html', models.TextField(default=b'')),
                ('js', models.TextField(default=b'')),
                ('vt', models.TextField(default=b'')),
                ('created', models.DateTimeField(default=django.utils.timezone.now)),
            ],
        ),
    ]
