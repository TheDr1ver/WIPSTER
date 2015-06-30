from django.conf import settings

#Thug settings
#thug_loc = settings(settings, "thug_loc", "/opt/remnux-thug/src/thug.py")
fuzzy_threshold = getattr(settings, "fuzzy_threshold", 0)