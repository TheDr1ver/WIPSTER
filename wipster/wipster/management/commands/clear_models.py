from django.core.management.base import BaseCommand
from sanalysis.models import Sample

class Command(BaseCommand):
	def handle(self, *args, **options):
		Sample.objects.all().delete()

