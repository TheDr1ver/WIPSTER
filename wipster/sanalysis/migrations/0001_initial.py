# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import models, migrations
import django.utils.timezone
import sanalysis.models


class Migration(migrations.Migration):

    dependencies = [
    ]

    operations = [
        migrations.CreateModel(
            name='Sample',
            fields=[
                ('id', models.AutoField(verbose_name='ID', serialize=False, auto_created=True, primary_key=True)),
                ('sample', models.FileField(upload_to=sanalysis.models.media_file_name)),
                ('ticket', models.CharField(max_length=32)),
                ('md5', models.CharField(max_length=32)),
                ('sha1', models.CharField(max_length=40)),
                ('sha256', models.CharField(max_length=64)),
                ('created', models.DateTimeField(default=django.utils.timezone.now)),
            ],
        ),
    ]
