# WIPSTER on Django (Alpha)

A rebuild of WIPSTER from the ground-up in a modular, cleaner, faster, and leaner manner, utilizing the Django framework.

This is completely in its alpha stages, but the raw base functionality currently works.

## Usage
1. Install Django
2. Customize ./sanalysis/settings.py with your personal settings
3. Run this from the /wipster/ base directory:
```sh
python manage.py runserver 0.0.0.0:8000 
```
4. from the /wipster/ base directory, point your browser to :8000/sanalysis, and you're off and running.

## To-Do List:

- Main landing page
- Yara integration
- Post-rtfobj.py strings automation
- Twitter marquee
- Pastebin-checker
- URL-Checking Tools app
- Auto-web-search for indicators

## Possibly later on:

- Anubis/Malwr integration?
- Shodan integration?
- ThreatExpert integration?

## Known bugs:

- Uploading more than one sample at a time can cause a database lock error
- Uploading filenames with UTF-8 characters causes an error

(C) Nick Driver - @TheDr1ver - 2016
