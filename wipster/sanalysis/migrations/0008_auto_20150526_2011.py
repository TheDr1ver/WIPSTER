# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import models, migrations


class Migration(migrations.Migration):

    dependencies = [
        ('sanalysis', '0007_sample_trid'),
    ]

    operations = [
        migrations.AddField(
            model_name='sample',
            name='oleid',
            field=models.TextField(default=b''),
        ),
        migrations.AddField(
            model_name='sample',
            name='olemeta',
            field=models.TextField(default=b''),
        ),
        migrations.AddField(
            model_name='sample',
            name='olevba',
            field=models.TextField(default=b''),
        ),
        migrations.AddField(
            model_name='sample',
            name='pdfid',
            field=models.TextField(default=b''),
        ),
        migrations.AddField(
            model_name='sample',
            name='peepdf',
            field=models.TextField(default=b''),
        ),
        migrations.AddField(
            model_name='sample',
            name='peframe',
            field=models.TextField(default=b''),
        ),
        migrations.AddField(
            model_name='sample',
            name='pescanner',
            field=models.TextField(default=b''),
        ),
        migrations.AddField(
            model_name='sample',
            name='rtfobj',
            field=models.TextField(default=b''),
        ),
    ]
