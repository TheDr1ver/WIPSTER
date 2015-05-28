# WIPSTER Settings & Variables for Sample Analysis

from django.conf import settings

#Balbuzard Settings
balbuzard_loc = getattr(settings, "balbuzard_loc", "/opt/remnux-balbuzard/balbuzard.py")

#TRiD Settings
trid_loc = getattr(settings, "trid_loc", "/opt/remnux-trid/trid")

#VirusTotal Settings
vt_loc = getattr(settings, "vt_loc", "/opt/remnux-didier/virustotal-search.py")
vt_key = getattr(settings, "vt_key", "<YOUR_API_KEY>")
# Future - list of AVs of interest (i.e. SEP and MSE)
    #vt_primary = getattr(settings, "vt_primary", "<LIST OF AVs OF INTEREST>")

#OLETools Settings
oleid_loc = getattr(settings, "oleid_loc", "/usr/local/lib/python2.7/dist-packages/oletools/oleid.py")
olemeta_loc = getattr(settings, "olemeta_loc", "/usr/local/lib/python2.7/dist-packages/oletools/olemeta.py")
olevba_loc = getattr(settings, "olevba_loc", "/usr/local/lib/python2.7/dist-packages/oletools/olevba.py")
rtfobj_loc = getattr(settings, "rtfobj_loc", "/usr/local/lib/python2.7/dist-packages/oletools/rtfobj.py")

#PDFiD Settings
pdfid_loc = getattr(settings, "pdfid_loc", "/opt/remnux-didier/pdfid.py")

#PEEPDF Settings
peepdf_loc = getattr(settings, "peepdf_loc", "/opt/remnux-peepdf/peepdf.py")

#PEFrame Settings
peframe_loc = getattr(settings, "peframe_loc", "/usr/bin/peframe")

#PEScanner Settings
pescanner_loc = getattr(settings, "pescanner_loc", "/opt/remnux-scripts/pescanner.py")

#CRITs Settings

#ThreatAnalyzer Settings
